<?php

namespace NeoPHP\mvc\manager;

use Exception;
use MongoDB\Driver\Manager;
use MongoId;
use NeoPHP\core\Collection;
use NeoPHP\mvc\Model;
use NeoPHP\mvc\ModelFilter;
use NeoPHP\mvc\ModelFilterGroup;
use NeoPHP\mvc\ModelSorter;
use NeoPHP\mvc\PropertyModelFilter;

class MongoModelManager extends EntityModelManager
{
    private static $managers = [];
    
    /**
     * Obtiene una nueva conexión de base de datos de mongo
     * @param string $connectionName Nombre de la conexión que se desea obtener
     * @return Manager conexión de datos
     */
    protected function getMongoManager ($connectionName=null)
    {
        if (!isset($connectionName))
            $connectionName = isset($this->getProperties()->defaultMongoConnection)? $this->getProperties()->defaultMongoConnection : "main";
        
        if (!isset(self::$managers[$connectionName]))
        {
            if (!isset($this->getProperties()->mongoConnections))
                throw new Exception ("Property \"connections\" not found !!");
            
            $connectionConfig = null; 
            if (is_object($this->getProperties()->mongoConnections))
            {
                $connectionConfig = $this->getProperties()->mongoConnections->$connectionName;
            }
            else
            {
                foreach ($this->getProperties()->mongoConnections as $testConnectionProperty)
                {
                    if ($testConnectionProperty->name = $connectionName)
                    {
                        $connectionConfig = $testConnectionProperty;
                        break;
                    }
                }
            }
            if (!isset($connectionConfig))
                throw new Exception ("Mongo Connection \"$connectionName\" not found !!");

            $mongoHost = isset($connectionConfig->host)? $connectionConfig->host : "localhost";
            $mongoPort = isset($connectionConfig->port)? $connectionConfig->port : 27017;
            self::$managers[$connectionName] = new Manager("mongodb://$mongoHost:$mongoPort");
        }
        return self::$managers[$connectionName];
    }
    
    public function create(Model $model)
    {
        $createResult = false;
        $modelAttributes = $this->getModelAttributes($model);
        $modelIdAttribute = $this->getModelIdAttribute();
        unset($modelAttributes[$modelIdAttribute]);
        return $this->getMongoManager()->selectCollection($this->getModelEntityName())->insert($modelAttributes);
    }

    public function delete(Model $model)
    {
        $deleteResult = false;
        $modelAttributes = $this->getModelAttributes($model);
        $modelIdAttribute = $this->getModelIdAttribute();
        $modelId = $modelAttributes[$modelIdAttribute];
        return $this->getMongoManager()->selectCollection($this->getModelEntityName())->remove(['_id'=>new MongoId($modelId)]);
    }

    public function update(Model $model)
    {
        $updateResult = false;
        $modelAttributes = $this->getModelAttributes($model);
        $modelIdAttribute = $this->getModelIdAttribute();
        $modelId = $modelAttributes[$modelIdAttribute];
        $mongoCollection = $this->getMongoManager()->selectCollection($this->getModelEntityName());
        $mongoId = new MongoId($modelId);
        $document = $mongoCollection->findOne(['_id'=>$mongoId]);
        if (isset($document))
        {
            $savedModelAttributes = $this->getAttributesFromDocument($document);
            $updateModelAttributes = array_diff_assoc($modelAttributes, $savedModelAttributes);
            if (!empty($updateModelAttributes))
                $updateResult = $mongoCollection->update (['_id'=>$mongoId], ['$set'=>$updateModelAttributes]);
        }
        return $updateResult;
    }
    
    public function retrieve(ModelFilter $filters=null, ModelSorter $sorters=null, array $parameters=[])
    {
        $modelCollection = new Collection();
        $modelClass = $this->getModelClass();
        $mongoCollection = $this->getMongoManager()->selectCollection($this->getModelEntityName());
        $mongoQuery = [];
        if (isset($filters))
        {
            $mongoQuery = array_merge($modelQuery, $this->getMongoQueryFilter($filters));
        }
        $mongoCursor = $mongoCollection->find($mongoQuery);
        if (isset($sorters))
        {
            $sortFields = [];
            foreach ($sorters->getSorters() as $sorter)
            {
                $sortProperty = $sorter->property;
                $sortFields[$sortProperty] = $sorter->direction == "ASC"?1:-1;
            }
            $mongoCursor->sort($sortFields);
        }
        if (isset($parameters[self::PARAMETER_START]))
        {
            $mongoCursor->skip($parameters[self::PARAMETER_START]);
        }
        if (isset($parameters[self::PARAMETER_LIMIT]))
        {
            $mongoCursor->limit($parameters[self::PARAMETER_LIMIT]);
        }

        foreach ($mongoCursor as $document)
        {
            $modelCollection->add($this->createModelFromAttributes($this->getAttributesFromDocument($document)));
        }
        return $modelCollection;
    }
    
    protected function getAttributesFromDocument ($document)
    {
        $modelIdAttribute = $this->getModelIdAttribute();
        $mongoId = strval($document["_id"]);
        $modelAttributes = $document;
        unset($modelAttributes["_id"]);
        $modelAttributes[$modelIdAttribute] = $mongoId;
        return $modelAttributes;
    }

    public function getMongoQueryFilter (ModelFilter $modelFilter)
    {
        $filter = null;
        $modelMetadata = $this->getModelMetadata();
        
        if ($modelFilter instanceof PropertyModelFilter)
        {
            $propertyAttribute = null;
            foreach ($modelMetadata->attributes as $attribute) 
            {
                if ($attribute->propertyName == $modelFilter->getProperty())
                {
                    $propertyAttribute = $attribute->name;
                    break;
                }
            }
            if ($propertyAttribute == null)
            {
                throw new Exception ("Property \"" . $modelFilter->getProperty() . "\" not found in Model \"" . $this->getModelClass() . "\" !!");
            }
            
            $propertyOperator = PropertyModelFilter::OPERATOR_EQUALS;
            $propertyValue = $modelFilter->getValue();
            switch ($modelFilter->getOperator())
            {
                case PropertyModelFilter::OPERATOR_EQUALS: 
                    $filter = [$propertyAttribute=>$propertyValue];
                    break;
                case PropertyModelFilter::OPERATOR_NOT_EQUALS: 
                    $filter = [$propertyAttribute=>['$ne'=>$propertyValue]];
                    break;
                case PropertyModelFilter::OPERATOR_CONTAINS: 
                    $filter = [$propertyAttribute=>['$regex'=>$propertyValue, '$options'=>'i']];
                    break;
                case PropertyModelFilter::OPERATOR_IN: 
                    $filter = [$propertyAttribute=>['$in'=>$propertyValue]];
                    break;
                case PropertyModelFilter::OPERATOR_GREATER_THAN: 
                    $filter = [$propertyAttribute=>['$gt'=>$propertyValue]];
                    break;
                case PropertyModelFilter::OPERATOR_GREATER_OR_EQUALS_THAN: 
                    $filter = [$propertyAttribute=>['$gte'=>$propertyValue]];
                    break;
                case PropertyModelFilter::OPERATOR_LESS_THAN: 
                    $filter = [$propertyAttribute=>['$lt'=>$propertyValue]];
                    break;
                case PropertyModelFilter::OPERATOR_LESS_OR_EQUALS_THAN: 
                    $filter = [$propertyAttribute=>['$lte'=>$propertyValue]];
                    break;
            }
        }
        else if ($modelFilter instanceof ModelFilterGroup)
        {
            $filter = [];
            
            switch ($modelFilter->getConnector())
            {
                case ModelFilterGroup::CONNECTOR_AND: 
                    $mongoFiltersConnector = '$and';
                    break;
                case ModelFilterGroup::CONNECTOR_OR:
                    $mongoFiltersConnector = '$or';
                    break;
            }
            
            $mongoFilters = [];
            foreach ($modelFilter->getFilters() as $childFilter)
            {
                $mongoFilters[] = $this->getMongoQueryFilter($childFilter);
            }
            $filters[$mongoFiltersConnector] = $mongoFilters;
        }
        
        return $filter;
    }
}