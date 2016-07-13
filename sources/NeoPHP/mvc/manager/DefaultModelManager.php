<?php

namespace NeoPHP\mvc\manager;

use Exception;
use NeoPHP\core\Collection;
use NeoPHP\mvc\Model;
use NeoPHP\sql\Connection;
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
    protected final function getConnection ($connectionName=null)
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
    
    public function insert(Model $model, array $options = [])
    {
        $modelAttributes = $this->getModelAttributes($model);
        $modelIdAttribute = $this->getModelIdAttribute();
        unset($modelAttributes[$modelIdAttribute]);
        return $this->getConnection()->createQuery($this->getModelEntityName())->insert($modelAttributes);
    }

    public function remove(Model $model, array $options = [])
    {
        $modelAttributes = $this->getModelAttributes($model);
        $modelIdAttribute = $this->getModelIdAttribute();
        $modelId = $modelAttributes[$modelIdAttribute];
        return $this->getConnection()->createQuery($this->getModelEntityName())->addWhere($modelIdAttribute, "=", $modelId)->delete();
    }

    public function update(Model $model, array $options = [])
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
    
    public function find(array $filters=[], array $sorters=[], array $options=[])
    {
        $modelCollection = new Collection();
        $modelClass = $this->getModelClassName();
        $modelQuery = $this->getConnection()->createQuery($this->getModelEntityName());
        if (!empty($filters))
        {
            $modelQuery->setWhereClause($this->getConnectionQueryFilter($filters));
        }
        if (isset($sorters))
        {
            foreach ($sorters as $sorter)
            {
                if (is_array($sorter))
                {
                    $modelQuery->addOrderBy($sorter[0], $sorter[1]);
                }
                else
                {
                    $modelQuery->addOrderBy($sorter);
                }
            }
        }
        if (isset($options[self::PARAMETER_START]))
        {
            $modelQuery->setOffset($options[self::PARAMETER_START]);
        }
        if (isset($options[self::PARAMETER_LIMIT]))
        {
            $modelQuery->setLimit($options[self::PARAMETER_LIMIT]);
        }
        $modelQuery->get(PDO::FETCH_ASSOC, function ($modelAttributes) use ($modelCollection) 
        {
            $modelCollection->add($this->createModelFromAttributes($modelAttributes));
        });
        return $modelCollection;
    }
    
    private function getConnectionQueryFilter (array $modelFilter = [])
    {
        $filter = new ConnectionQueryFilterGroup();
        
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
                    $filter->setConnector($value);
                }
                else
                {
                    $attribute = $this->getModelAttribute($property);
                    if ($attribute != null)
                    {
                        if (!is_array($value))
                        {
                            $filter->addColumnFilter($attribute, "=", $value);
                        }
                        else
                        {
                            foreach ($value as $command => $attributeValue)
                            {
                                switch ($command)
                                {
                                    case '$eq': $filter->addColumnFilter($attribute, "=", $value); break;
                                    case '$dt': $filter->addColumnFilter($attribute, "!=", $value); break;
                                    case '$ct': $filter->addColumnFilter($attribute, "like", "%$value%"); break;
                                    case '$gt': $filter->addColumnFilter($attribute, ">", $value); break;
                                    case '$gte': $filter->addColumnFilter($attribute, ">=", $value); break;
                                    case '$lt': $filter->addColumnFilter($attribute, "<", $value); break;
                                    case '$lte': $filter->addColumnFilter($attribute, "<=", $value); break;
                                }
                            }
                        }
                    }
                    else
                    {
                        throw new Exception ("Property \"" . $property . "\" not found in Model \"" . $this->getModelClassName() . "\" !!");
                    }
                }
            }
        }
        return $filter;
    }
}