<?php

namespace NeoPHP\mvc\manager;

use Exception;
use NeoPHP\core\reflect\ReflectionAnnotatedClass;
use NeoPHP\mvc\Model;
use NeoPHP\util\IntrospectionUtils;
use stdClass;

abstract class EntityModelManager extends ModelManager
{
    const ANNOTATION_ENTITY = "entity";
    const ANNOTATION_ATTRIBUTE = "attribute";
    const ANNOTATION_ID = "id";
    const ANNOTATION_PARAMETER_NAME = "name";
    
    private $modelMetadata;
    
    /**
     * Obtiene el nombre de la entidad asociada con el modelo
     * @return string Nombre de entidad
     */
    protected function getModelEntityName ()
    {
        return $this->getModelMetadata()->name;
    }
    
    /**
     * Obtiene el nombre del atributo marcado como ID en el modelo
     * @return string nombre del atributo id del modelo
     */
    protected function getModelIdAttribute ()
    {
        return $this->getModelMetadata()->idAttribute;
    }   
    
    /**
     * Obtiene todos los atributos con sus valores del modelo
     * @param Model $model Modelo a obtener sus atributos
     * @return array Lista de atributos del modelo
     */
    protected function getModelAttributes (Model $model)
    {
        $modelAttributes = [];
        $modelMetadata = $this->getModelMetadata();
        foreach ($modelMetadata->attributes as $attribute)
        {
            $modelAttributes[$attribute->name] = IntrospectionUtils::getPropertyValue($model, $attribute->propertyName);
        }
        return $modelAttributes;
    }
    
    /**
     * Establece al modelo los atributos especificados
     * @param Model $model Modelo a establecer atributos
     * @param array $attributes Atributos a establecer
     */
    protected function setModelAttributes (Model $model, array $attributes)
    {
        $modelMetadata = $this->getModelMetadata();
        foreach ($modelMetadata->attributes as $attribute)
        {
            IntrospectionUtils::setPropertyValue($model, $attribute->propertyName, $attributes[$attribute->name]);
        }
    }
    
    /**
     * Crea un modelo nuevo a traves de atributos
     * @param array $attributes atributos a establecer
     * @return Model modelo creado
     */
    protected function createModelFromAttributes (array $attributes)
    {
        $model = null;
        if (!empty($attributes))
        {
            $modelClass = $this->getModelClass();
            $model = new $modelClass;
            $this->setModelAttributes($model, $attributes);
        }
        return $model;
    }
    
    /**
     * Obtiene metadatos de un modelo dado
     * @return type Metadatos del modelo
     */
    protected final function getModelMetadata ()
    {
        if (!isset($this->modelMetadata))
        {
            $this->modelMetadata = $this->retrieveModelMetadata();
        }
        return $this->modelMetadata;
    }
    
    /**
     * Obtiene de la clase del modelo los metadatos de entidad y atributos
     * @throws Exception Error si no se puede obtener los valores
     */
    protected function retrieveModelMetadata ()
    {
        $modelClass = $this->getModelClass();
        $entityMetadata = new stdClass();
        $entityClass = new ReflectionAnnotatedClass($modelClass);
        $entityAnnotation = $entityClass->getAnnotation(self::ANNOTATION_ENTITY);
        if ($entityAnnotation == null)
            throw new Exception ("Entity class \"$modelClass\" must have the \"" . self::ANNOTATION_ENTITY . "\" annotation");
        $entityName = $entityAnnotation->getParameter(self::ANNOTATION_PARAMETER_NAME);
        if (empty($entityName))
            $entityName = strtolower($entityClass->getShortName());
        $entityMetadata->name = $entityName; 
        $entityMetadata->attributes = [];
        $properties = $entityClass->getProperties();
        foreach ($properties as $property)
        {
            $attributeAnnotation = $property->getAnnotation(self::ANNOTATION_ATTRIBUTE);
            if ($attributeAnnotation != null)
            {
                $attribute = new stdClass();
                $attributeName = $attributeAnnotation->getParameter(self::ANNOTATION_PARAMETER_NAME);
                if (empty($attributeName))
                    $attributeName = strtolower($property->getName());
                $attribute->name = $attributeName;
                $attribute->propertyName = $property->getName();
                $entityMetadata->attributes[] = $attribute;
                
                $idAnnotation = $property->getAnnotation(self::ANNOTATION_ID);
                if ($idAnnotation)
                {
                    $entityMetadata->idAttribute = $attributeName;
                }
            }
        }
        return $entityMetadata;
    }
}