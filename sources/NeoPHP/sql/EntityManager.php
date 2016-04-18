<?php

namespace NeoPHP\sql;

use NeoPHP\core\Object;
use NeoPHP\core\reflect\ReflectionAnnotatedClass;

class EntityManager
{
    const ANNOTATION_TABLE = "table";
    const ANNOTATION_COLUMN = "column";
    const ANNOTATION_EXTRA_COLUMN = "extraColumn";
    const PARAMETER_ID = "id";
    const PARAMETER_NAME = "name";
    const PARAMETER_TYPE = "type";
    const PARAMETER_ENTITY_CLASS_NAME = "entityClassName";
    
    private $connection;
    
    public function __construct (Connection $connection)
    {
        $this->connection = $connection;
    }
    
    public function insert (Object $entity)
    {
        return $this->connection->getTable($this->getTableName($entity->getClass()))->insert($this->getColumns($entity));
    }
    
    public function create (ReflectionAnnotatedClass $entityClass, array $columns)
    {
        $entityClassName = $entityClass->getName();
        $entity = new $entityClassName;
        $this->setColumns($entity, $columns);
        return $entity;
    }
    
    public function update (Object $entity)
    {
        $idColumns = $this->getColumns($entity, ["retrieveNonIdFields"=>false]);
        $valueColumns = array_filter($this->getColumns($entity, ["retrieveIdField"=>false]), function($val) { return isset($val); });
        $entityTable = $this->connection->getTable($this->getTableName($entity->getClass()));
        foreach ($idColumns as $idColumnName=>$idColumnValue)
            $entityTable->addWhere($idColumnName, SQL::OPERATOR_EQUAL, $idColumnValue);
        return $entityTable->update($valueColumns);
    }
    
    public function delete (Object $entity)
    {
        $idColumns = $this->getColumns($entity, false, true);
        $entityTable = $this->connection->getTable($this->getTableName($entity->getClass()));
        foreach ($idColumns as $idColumnName=>$idColumnValue)
            $entityTable->addWhere($idColumnName, SQL::OPERATOR_EQUAL, $idColumnValue);
        return $entityTable->delete(); 
    }
    
    public function persist (Object $entity)
    {
        $affectedRows = $this->update($entity);
        if ($affectedRows === 0)
            $affectedRows = $this->insert ($entity);
        return $affectedRows;
    }
    
    public function find (ReflectionAnnotatedClass $entityClass, $id)
    {
        $entity = null;
        $idProperty = $this->getIdProperty($entityClass);
        if ($idProperty != null)
        {
            $idPropertyColumnAnnotation = $idProperty->getAnnotation(self::ANNOTATION_COLUMN);
            $idPropertyColumn = $idPropertyColumnAnnotation->getParameter(self::PARAMETER_NAME);
            $idColumn = $idPropertyColumn != null? $idPropertyColumn : $idProperty->getName();
            $entity = $this->connection->getTable($this->getTableName($entityClass))->addWhere($idColumn, SQL::OPERATOR_EQUAL, $id)->getFirst($entityClass);
        }
        return $entity;
    }
    
    public function findBy (ReflectionAnnotatedClass $entityClass, EntityFilterGroup $filters)
    {
        $entityTable = $this->connection->getTable($this->getTableName($entityClass));
        $entityTable->addWhereGroup($this->getConditionGroup($entityClass, $filters));
        return $entityTable->get($entityClass);
    }
    
    public function findAll (ReflectionAnnotatedClass $entityClass)
    {
        return $this->connection->getTable($this->getTableName($entityClass))->get($entityClass);
    }
    
    public function complete (Object $entity)
    {
        $idColumns = $this->getColumns($entity, false, true);
        $entityTable = $this->connection->getTable($this->getTableName($entity->getClass()));
        foreach ($idColumns as $idColumnName=>$idColumnValue)
            $entityTable->addWhere($idColumnName, SQL::OPERATOR_EQUAL, $idColumnValue);
        $entityColumns = $entityTable->getFirst();
        $this->setColumns($entity, $entityColumns);
        return $entity;
    }
    
    private function getTableName (ReflectionAnnotatedClass $entityClass)
    {
        return $entityClass->getAnnotation(self::ANNOTATION_TABLE)->getParameter(self::PARAMETER_NAME);
    }
    
    private function getColumns (Object $entity, array $options = [])
    {
        $options = array_merge(["retrieveIdField"=>true, "retrieveNonIdFields"=>true, "retrieveEmptyValues"=>false], $options);
        $columns = [];
        $properties = $entity->getClass()->getProperties();
        foreach ($properties as $property)
        {
            $columnAnnotation = $property->getAnnotation(self::ANNOTATION_COLUMN);
            if ($columnAnnotation == null) 
                $columnAnnotation = $property->getAnnotation(self::ANNOTATION_EXTRA_COLUMN);
            
            if ($columnAnnotation != null)
            {
                $isIdField = $columnAnnotation->getParameter(self::PARAMETER_ID);
                if ($options["retrieveIdField"] == false && $isIdField)
                    continue;
                if ($options["retrieveNonIdFields"] == false && !$isIdField)
                    continue;
                
                $property->setAccessible(true);
                $columnNameParameter = $columnAnnotation->getParameter(self::PARAMETER_NAME);
                $columnName = $columnNameParameter != null? $columnNameParameter : $property->getName();
                $columnValue = $property->getValue($entity);
                if ($columnValue != null)
                {
                    $entityClassName = $columnAnnotation->getParameter(self::PARAMETER_ENTITY_CLASS_NAME);
                    if ($entityClassName != null)
                    {
                        $columnValue = $this->getEntityId($columnValue);
                    }
                }
                if ($columnValue != null || $options["retrieveEmptyValues"] == true)
                    $columns[$columnName] = $columnValue;
            }
        }
        return $columns;
    }
    
    private function getColumn (Object $entity, $column)
    {
        $value = null;
        $properties = $entity->getClass()->getProperties();
        foreach ($properties as $property)
        {
            $columnAnnotation = $property->getAnnotation(self::ANNOTATION_COLUMN);
            if ($columnAnnotation == null) 
                $columnAnnotation = $property->getAnnotation(self::ANNOTATION_EXTRA_COLUMN);
            
            if ($columnAnnotation != null)
            {
                $columnNameParameter = $columnAnnotation->getParameter(self::PARAMETER_NAME);
                $columnName = $columnNameParameter != null? $columnNameParameter : $property->getName();
                if ($column == $columnName)
                {
                    $property->setAccessible(true);
                    $value = $property->getValue($entity);
                    break;
                }
            }
        }
        return $value;
    }
    
    private function setColumns (Object $entity, array $columns = [])
    {
        foreach ($columns as $key => $value)
            $this->setColumn($entity, $key, $value);
    }
    
    private function setColumn (Object $entity, $columnName, $columnValue)
    {
        $entityDeliminterPos = strpos($columnName, "_");
        if ($entityDeliminterPos !== false) 
        {
            $subEntityName = substr($columnName, 0, $entityDeliminterPos);
            $subEntityColumn = substr($columnName, $entityDeliminterPos+1);
            $subEntity = $entity::getClass()->getProperty($subEntityName)->getValue($entity);
            $this->setColumn($subEntity, $subEntityColumn, $columnValue);
        }
        else
        {
            $properties = $entity->getClass()->getProperties();
            foreach ($properties as $property)
            {
                $columnAnnotation = $property->getAnnotation(self::ANNOTATION_COLUMN);
                if ($columnAnnotation == null) 
                    $columnAnnotation = $property->getAnnotation(self::ANNOTATION_EXTRA_COLUMN);

                if ($columnAnnotation != null)
                {
                    $propertyColumnNameParameter = $columnAnnotation->getParameter(self::PARAMETER_NAME);
                    $propertyColumnName = $propertyColumnNameParameter != null? $propertyColumnNameParameter : $property->getName();
                    if ($propertyColumnName == $columnName)
                    {
                        $property->setAccessible(true);
                        $entityClassName = $columnAnnotation->getParameter(self::PARAMETER_ENTITY_CLASS_NAME);
                        if ($entityClassName != null)
                        {
                            $relatedEntity = $property->getValue($entity);
                            if ($relatedEntity == null)
                            {
                                if (strpos($entityClassName, "\\") == false)
                                    $entityClassName = $entity->getClass()->getNamespaceName() . "\\" . $entityClassName;
                                $relatedEntity = new $entityClassName;
                                $property->setValue($entity, $relatedEntity);
                            }
                            
                            $this->setEntityId($relatedEntity, $columnValue);
                        }
                        else
                        {
                            $property->setValue($entity, $columnValue);
                        }
                        break;
                    }
                }
            }
        }
    }
    
    private function getIdProperty (ReflectionAnnotatedClass $entityClass)
    {
        $idProperty = null;
        $properties = $entityClass->getProperties();
        foreach ($properties as $property)
        {   
            $columnAnnotation = $property->getAnnotation(self::ANNOTATION_COLUMN);
            if ($columnAnnotation != null)
            {
                if ($columnAnnotation->getParameter(self::PARAMETER_ID) != null)
                {
                    $idProperty = $property;
                    break;
                }
            }
        }
        return $idProperty;
    }
    
    public function getEntityId (Object $entity)
    {
        $idProperty = $this->getIdProperty($entity->getClass());
        return ($idProperty != null)? $idProperty->getValue($entity) : null;
    }
    
    public function setEntityId (Object $entity, $id)
    {
        $idProperty = $this->getIdProperty($entity->getClass());
        if ($idProperty != null)
        {
            $idProperty->setAccessible(true);
            $idProperty->setValue($entity, $id);
        }
    }
    
    private function getConditionGroup (ReflectionAnnotatedClass $entityClass, EntityFilterGroup $filters)
    {
        $conditionGroup = new SQLConditionGroup($filters->getConnector());
        foreach ($filters->getFilters() as $filter)
        {
            if ($filter instanceof EntityFilterGroup)
            {
                $conditionGroup->addConditionGroup($this->getConditionGroup($entityClass, $filter));
            }
            else
            {
                $propertyName = $filter["property"];
                $property = $entityClass->getProperty($propertyName);
                $columnAnnotation = $property->getAnnotation(self::ANNOTATION_COLUMN);
                if ($columnAnnotation == null) 
                    $columnAnnotation = $property->getAnnotation(self::ANNOTATION_JOIN_COLUMN);
                $columnNameParameter = $columnAnnotation->getParameter(self::PARAMETER_NAME);
                $columnName = $columnNameParameter != null? $columnNameParameter : $property->getName();
                $conditionGroup->addCondition($columnName, $filter["operator"], $filter["value"]);
            }
        }
        return $conditionGroup;
    }
}