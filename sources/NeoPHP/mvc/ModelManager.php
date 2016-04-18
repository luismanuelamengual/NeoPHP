<?php

namespace NeoPHP\mvc;

use Exception;
use NeoPHP\app\ApplicationComponent;
use NeoPHP\core\Collection;
use NeoPHP\core\reflect\ReflectionAnnotatedClass;
use NeoPHP\sql\Connection;
use NeoPHP\util\IntrospectionUtils;
use NeoPHP\util\logging\Logger;
use NeoPHP\util\memory\MemCache;
use NeoPHP\util\properties\PropertiesManager;
use stdClass;

abstract class ModelManager extends ApplicationComponent
{
    private static $modelMetadata = [];
    
    public function __construct (MVCApplication $application)
    {
        parent::__construct($application);
    }
    
    /**
     * Obtiene el manager de propiedades de la aplicación
     * @return PropertiesManager Propiedades de la aplicación
     */
    protected final function getProperties ()
    {
        return $this->application->getProperties();
    }
    
    /**
     * Obtiene el logger de la aplicación
     * @return Logger Logger de la aplicación
     */
    protected final function getLogger ()
    {
        return $this->application->getLogger();
    }
    
    /**
     * Obtiene el manejador de modelos
     * @param type $managerClass
     * @return ModelManager Manejador de modelos
     */
    protected final function getManager ($managerClass)
    {
        return $this->application->getManager($managerClass);
    }
    
    /**
     * Obtiene el manejador de cache asociado a la aplicación
     * @return MemCache Manejador de cache
     */
    protected final function getCacheManager ()
    {
        return $this->application->getCacheManager();
    }

    /**
     * Obtiene la base de datos asociada
     * @param $connectionName nombre de la base de datos
     * @return Connection Base de datos
     */
    protected final function getConnection ($connectionName="main")
    {
        return $this->application->getConnection ($connectionName);
    }
    
    /**
     * Crea un modelo con las propiedades establecidas
     * @param type $modelClass
     * @param type $modelProperties
     * @return Model Modelo creado
     */
    protected final function createModel ($modelClass, $modelProperties)
    {
        $model = null;
        if ($modelProperties != null)
        {
            $model = new $modelClass;
            $model->setFrom($modelProperties);
        }
        return $model;
    }
    
    /**
     * Crea una colección de modelos
     * @param type $modelClass
     * @param array $modelsProperties
     * @return Collection Coleccion de modelos
     */
    protected final function createModelCollection ($modelClass, array $modelsProperties)
    {
        $models = new Collection();
        foreach ($modelsProperties as $modelProperties)
        {
            $models->add($this->createModel($modelClass, $modelProperties));
        }
        return $models;
    }
    
    protected function getModel ($modelClassName, $id, $connectionName="main")
    {
        $model = null;
        $modelFields = $this->getConnection($connectionName)->createQuery($this->getModelTable($modelClassName))->addWhere($this->getModelIdField($modelClassName), "=", $id)->getFirst();
        if (!empty($modelFields))
        {
            $model = new $modelClassName;
            $this->setModelFields($model, $modelFields);
        }
        return $model;
    }
    
    protected function getAllModels ($modelClassName, $connectionName="main")
    {
        $models = new Collection();
        $modelsData = $this->getConnection($connectionName)->createQuery($this->getModelTable($modelClassName))->addOrderBy($this->getModelIdField($modelClassName))->get();
        foreach ($modelsData as $modelFields)
        {
            $model = new $modelClassName;
            $this->setModelFields($model, $modelFields);
            $models->add($model);
        }
        return $models;
    }
    
    protected function insertModel (Model $model, $connectionName="main")
    {
        $modelClassName = get_class($model);
        $modelFields = $this->getModelFields($model);
        $modelIdField = $this->getModelIdField($modelClassName);
        unset($modelFields[$modelIdField]);
        $this->getConnection($connectionName)->createQuery($this->getModelTable($modelClassName))->insert($modelFields);
    }
    
    protected function updateModel (Model $model, $connectionName="main")
    {
        $modelClassName = get_class($model);
        $modelFields = $this->getModelFields($model);
        $modelIdField = $this->getModelIdField($modelClassName);
        $modelId = $modelFields[$modelIdField];
        $savedModelFields = $this->getConnection($connectionName)->createQuery($this->getModelTable($modelClassName))->addWhere($modelIdField, "=", $modelId)->getFirst();
        $updateModelFields = array_diff_assoc($modelFields, $savedModelFields);
        if (!empty($updateModelFields))
            $this->getConnection($connectionName)->createQuery($this->getModelTable($modelClassName))->addWhere($modelIdField, "=", $modelId)->update($updateModelFields);
    }
    
    protected function persistModel (Model $model, $connectionName="main")
    {
        $modelFields = $this->getModelFields($model);
        $modelIdField = $this->getModelIdField(get_class($model));
        $modelId = $modelFields[$modelIdField];
        if (!empty($modelId))
        {
            $this->updateModel ($model, $connectionName);
        }
        else
        {
            $this->insertModel($model, $connectionName);
        }
    }
    
    protected function deleteModel (Model $model, $connectionName="main")
    {
        $modelFields = $this->getModelFields($model);
        $modelIdField = $this->getModelIdField(get_class($model));
        $modelId = $modelFields[$modelIdField];
        $this->getConnection($connectionName)->createQuery($this->getModelTable(get_class($model)))->addWhere($modelIdField, "=", $modelId)->delete();
    }
    
    protected function completeModel (Model $model, $connectionName="main")
    {
        $modelClassName = get_class($model);
        $modelFields = $this->getModelFields($model);
        $modelIdField = $this->getModelIdField($modelClassName);
        $modelId = $modelFields[$modelIdField];
        $newModelFields = $this->getConnection($connectionName)->createQuery($this->getModelTable($modelClassName))->addWhere($modelIdField, "=", $modelId)->getFirst();
        if (!empty($newModelFields))
            $this->setModelFields($model, $newModelFields);
    }
    
    protected function getModelTable ($modelClassName)
    {
        $modelMetadata = $this->getModelMetadata($modelClassName);
        return $modelMetadata->tableName;
    }
    
    protected function getModelIdField ($modelClassName)
    {
        $modelMetadata = $this->getModelMetadata($modelClassName);
        $idField = null;
        foreach ($modelMetadata->columns as $column)
        {
            if ($column->id)
            {
                $idField = $column->name;
                break;
            }
        }
        return $idField;
    }   
    
    protected function getModelFields (Model $model)
    {
        $modelFields = [];
        $modelMetadata = $this->getModelMetadata(get_class($model));
        foreach ($modelMetadata->columns as $column)
        {
            $modelFields[$column->name] = IntrospectionUtils::getPropertyValue($model, $column->propertyName);
        }
        return $modelFields;
    }
    
    protected function setModelFields (Model $model, array $fields)
    {
        $modelFields = [];
        $modelMetadata = $this->getModelMetadata(get_class($model));
        foreach ($modelMetadata->columns as $column)
        {
            IntrospectionUtils::setPropertyValue($model, $column->propertyName, $fields[$column->name]);
        }
        return $modelFields;
    }
    
    protected function createModelFromFields ($modelClassName, array $fields)
    {
        $model = null;
        if (!empty($fields))
        {
            $model = new $modelClassName;
            $this->setModelFields($model, $fields);
        }
        return $model;
    }
    
    protected function getModelMetadata ($modelClassName)
    {
        if (empty(self::$modelMetadata[$modelClassName]))
            self::$modelMetadata[$modelClassName] = $this->retrieveModelMetadata($modelClassName);
        return self::$modelMetadata[$modelClassName];
    }
    
    protected function retrieveModelMetadata ($modelClassName)
    {
        $modelMetadata = new stdClass();
        $modelClass = new ReflectionAnnotatedClass($modelClassName);
        $tableAnnotation = $modelClass->getAnnotation("table");
        if ($tableAnnotation == null)
            throw new Exception ("Model class \"$modelClassName\" must have the \"table\" annotation");
        $tableName = $tableAnnotation->getParameter("name");
        if (empty($tableName))
            $tableName = strtolower($modelClass->getShortName());
        $modelMetadata->tableName = $tableName;
        $modelMetadata->columns = [];
        $properties = $modelClass->getProperties();
        foreach ($properties as $property)
        {
            $columnAnnotation = $property->getAnnotation("column");
            if ($columnAnnotation != null)
            {
                $column = new stdClass();
                $columnName = $columnAnnotation->getParameter("name");
                if (empty($columnName))
                    $columnName = strtolower($property->getName());
                $column->name = $columnName;
                $column->propertyName = $property->getName();
                if ($columnAnnotation->getParameter("id") != null) 
                    $column->id = true;
                $modelMetadata->columns[] = $column;
            }
        }
        return $modelMetadata;
    }
}