<?php

namespace NeoPHP\mvc\manager;

use Exception;
use NeoPHP\core\Collection;
use NeoPHP\mvc\Model;
use NeoPHP\mvc\ModelFilter;
use NeoPHP\mvc\ModelFilterGroup;
use NeoPHP\mvc\ModelSorter;
use NeoPHP\mvc\PropertyModelFilter;
use NeoPHP\sql\Connection;
use NeoPHP\sql\ConnectionQueryColumnFilter;
use NeoPHP\sql\ConnectionQueryFilterGroup;
use PDO;

class DefaultModelManager extends EntityModelManager
{
    private static $connections = [];
    
    /**
     * Obtiene una nueva conexión de base de datos en funcion del nombre especificado
     * @param string $connectionName Nombre de la conexión que se desea obtener
     * @return Connection conexión de datos
     */
    protected function getConnection ($connectionName=null)
    {
        if (!isset($connectionName))
            $connectionName = isset($this->getProperties()->defaultConnection)? $this->getProperties()->defaultConnection : "main";
        
        if (!isset(self::$connections[$connectionName]))
        {
            if (!isset($this->getProperties()->connections))
                throw new Exception ("Property \"connections\" not found !!");
            
            $connectionConfig = null; 
            if (is_object($this->getProperties()->connections))
            {
                $connectionConfig = $this->getProperties()->connections->$connectionName;
            }
            else
            {
                foreach ($this->getProperties()->connections as $testConnectionProperty)
                {
                    if ($testConnectionProperty->name = $connectionName)
                    {
                        $connectionConfig = $testConnectionProperty;
                        break;
                    }
                }
            }
            if (!isset($connectionConfig))
                throw new Exception ("Connection \"$connectionName\" not found !!");

            $connection = new Connection();
            $connection->setLogger($this->getLogger());
            $connection->setDriver($connectionConfig->driver);
            $connection->setDatabase($connectionConfig->database);
            $connection->setHost(isset($connectionConfig->host)? $connectionConfig->host : "localhost");
            $connection->setPort(isset($connectionConfig->port)? $connectionConfig->port : "");
            $connection->setUsername(isset($connectionConfig->username)? $connectionConfig->username : "");
            $connection->setPassword(isset($connectionConfig->password)? $connectionConfig->password : "");
            self::$connections[$connectionName] = $connection;
        }
        return self::$connections[$connectionName];
    }
    
    public function create(Model $model)
    {
        $createResult = false;
        $modelAttributes = $this->getModelAttributes($model);
        $modelIdAttribute = $this->getModelIdAttribute();
        unset($modelAttributes[$modelIdAttribute]);
        return $this->getConnection()->createQuery($this->getModelEntityName())->insert($modelAttributes);
    }

    public function delete(Model $model)
    {
        $deleteResult = false;
        $modelAttributes = $this->getModelAttributes($model);
        $modelIdAttribute = $this->getModelIdAttribute();
        $modelId = $modelAttributes[$modelIdAttribute];
        return $this->getConnection()->createQuery($this->getModelEntityName())->addWhere($modelIdAttribute, "=", $modelId)->delete();
    }

    public function update(Model $model)
    {
        $updateResult = false;
        $modelAttributes = $this->getModelAttributes($model);
        $modelIdAttribute = $this->getModelIdAttribute();
        $modelId = $modelAttributes[$modelIdAttribute];
        $modelQuery = $this->getConnection()->createQuery($this->getModelEntityName())->addWhere($modelIdAttribute, "=", $modelId);
        $savedModelAttributes = $modelQuery->getFirst();
        if (isset($savedModelAttributes))
        {
            $updateModelAttributes = array_diff_assoc($modelAttributes, $savedModelAttributes);
            if (!empty($updateModelAttributes))
                $updateResult = $modelQuery->update($updateModelAttributes);
        }
        return $updateResult;
    }
    
    public function retrieve(ModelFilter $filters=null, ModelSorter $sorters=null, array $parameters=[])
    {
        $modelCollection = new Collection();
        $modelClass = $this->getModelClass();
        $modelQuery = $this->getConnection()->createQuery($this->getModelEntityName());
        if (isset($filters))
        {
            $modelQuery->setWhereClause($this->getConnectionQueryFilter($filters));            
        }
        if (isset($sorters))
        {
            foreach ($sorters->getSorters() as $sorter)
            {
                $modelQuery->addOrderBy($sorter->property, $sorter->direction);
            }
        }
        if (isset($parameters[self::PARAMETER_START]))
        {
            $modelQuery->setOffset($parameters[self::PARAMETER_START]);
        }
        if (isset($parameters[self::PARAMETER_LIMIT]))
        {
            $modelQuery->setLimit($parameters[self::PARAMETER_LIMIT]);
        }
        $modelQuery->get(PDO::FETCH_ASSOC, function ($modelAttributes) use ($modelCollection) 
        {
            $modelCollection->add($this->createModelFromAttributes($modelAttributes));
        });
        return $modelCollection;
    }
    
    public function getConnectionQueryFilter (ModelFilter $modelFilter)
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
                    $propertyOperator = "="; 
                    break;
                case PropertyModelFilter::OPERATOR_NOT_EQUALS: 
                    $propertyOperator = "!="; 
                    break;
                case PropertyModelFilter::OPERATOR_CONTAINS: 
                    $propertyOperator = "like"; 
                    $propertyValue = "%$propertyValue%";
                    break;
                case PropertyModelFilter::OPERATOR_IN: 
                    $propertyOperator = "in"; 
                    break;
                case PropertyModelFilter::OPERATOR_GREATER_THAN: 
                    $propertyOperator = ">"; 
                    break;
                case PropertyModelFilter::OPERATOR_GREATER_OR_EQUALS_THAN: 
                    $propertyOperator = ">="; 
                    break;
                case PropertyModelFilter::OPERATOR_LESS_THAN: 
                    $propertyOperator = "<"; 
                    break;
                case PropertyModelFilter::OPERATOR_LESS_OR_EQUALS_THAN: 
                    $propertyOperator = "<="; 
                    break;
            }
            
            $filter = new ConnectionQueryColumnFilter($propertyAttribute, $propertyOperator, $propertyValue);
        }
        else if ($modelFilter instanceof ModelFilterGroup)
        {
            $filter = new ConnectionQueryFilterGroup();
            switch ($modelFilter->getConnector())
            {
                case ModelFilterGroup::CONNECTOR_AND: 
                    $filter->setConnector("AND");
                    break;
                case ModelFilterGroup::CONNECTOR_OR:
                    $filter->setConnector("OR");
                    break;                
            }
            foreach ($modelFilter->getFilters() as $childFilter)
            {
                $filter->addFilter($this->getConnectionQueryFilter($childFilter));
            }
        }
        return $filter;
    }
}