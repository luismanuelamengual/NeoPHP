<?php

namespace NeoPHP\mvc\manager;

use Exception;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Manager;
use MongoDB\Driver\Query;
use MongoDB\BSON\ObjectID;
use NeoPHP\core\Collection;
use NeoPHP\mvc\Model;

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
            $mongoManager = new Manager("mongodb://$mongoHost:$mongoPort");
            $mongoManager->defaultDatabase = $connectionConfig->database;
            self::$managers[$connectionName] = $mongoManager;
        }
        return self::$managers[$connectionName];
    }
    
    public function insert(Model $model, array $options = [])
    {
        $mongoManager = $this->getMongoManager();
        $modelAttributes = $this->getModelAttributes($model);
        $modelIdAttribute = $this->getModelIdAttribute();
        unset($modelAttributes[$modelIdAttribute]);
        $bulk = new BulkWrite();
        $bulk->insert($modelAttributes);
        return $mongoManager->executeBulkWrite($mongoManager->defaultDatabase . "." . $this->getModelEntityName(), $bulk);
    }

    public function remove(Model $model, array $options = [])
    {
        $mongoManager = $this->getMongoManager();
        $bulk = new BulkWrite();
        $bulk->delete(["_id"=>new ObjectID($model->getId())]);
        return $mongoManager->executeBulkWrite($mongoManager->defaultDatabase . "." . $this->getModelEntityName(), $bulk);
    }

    public function update(Model $model, array $options = [])
    {
        $mongoManager = $this->getMongoManager();
        $modelAttributes = $this->getModelAttributes($model);
        $modelIdAttribute = $this->getModelIdAttribute();
        unset($modelAttributes[$modelIdAttribute]);
        $bulk = new BulkWrite();
        $bulk->update(["_id"=>new ObjectID($model->getId())], $modelAttributes);
        return $mongoManager->executeBulkWrite($mongoManager->defaultDatabase . "." . $this->getModelEntityName(), $bulk);
    }
    
    public function find(array $filters=[], array $sorters=[], array $options=[])
    {
        $mongoManager = $this->getMongoManager();
        $modelCollection = new Collection();
        $modelClass = $this->getModelClass();
        $mongoFilters = [];
        if (isset($filters))
        {
            $mongoFilters = $this->getMongoQueryFilter($filters);
        }
        $mongoQuery = new Query($mongoFilters);
        $mongoCursor = $mongoManager->executeQuery ($mongoManager->defaultDatabase . "." . $this->getModelEntityName(), $mongoQuery);
        foreach ($mongoCursor as $document)
        {
            $modelAttributes = $this->getAttributesFromDocument($document);
            $modelCollection->add($this->createModelFromAttributes($modelAttributes));
        }
        return $modelCollection;
    }
    
    protected function getAttributesFromDocument ($document)
    {
        $modelAttributes = (array)$document;   
        $mongoId = strval($modelAttributes["_id"]);
        $modelIdAttribute = $this->getModelIdAttribute();
        unset($modelAttributes["_id"]);
        $modelAttributes[$modelIdAttribute] = $mongoId;
        return $modelAttributes;
    }

    public function getMongoQueryFilter (array $modelFilter = [])
    {
        $filter = [];
        
        foreach ($modelFilter as $property => $value) 
        {
            if (is_numeric($property) && is_array($value))
            {
                $filter->addFilter($this->getConnectionQueryFilter($value));
            }
            else
            {
                if ($property == '$connector')
                {
                }
                else
                {
                    $attribute = $this->getModelAttribute($property);
                    if ($attribute != null)
                    {
                        $filter[$attribute] = $value;
                    }
                    else
                    {
                        throw new Exception ("Property \"" . $property . "\" not found in Model \"" . $this->getModelClass() . "\" !!");
                    }
                }
            }
        }
        return $filter;
    }
}
