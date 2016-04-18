<?php

namespace NeoPHP\util;

use Exception;
use ReflectionObject;
use stdClass;

abstract class IntrospectionUtils 
{
    public static function getPropertyValue ($object, $propertyName, $objectData=null)
    {
        if ($objectData == null)
            $objectData = new ReflectionObject($object);
        
        $propertyValue = null;
        try
        {
            $methodName = 'get' . ucfirst($propertyName);
            if ($objectData->hasMethod($methodName))
            {
                $propertyValue = $objectData->getMethod($methodName)->invoke($object);
            }
            else
            {
                $methodName = 'is' . ucfirst($propertyName);
                if ($objectData->hasMethod($methodName))
                    $propertyValue = $objectData->getMethod($methodName)->invoke($object);
            }
        } 
        catch (Exception $ex) {}
        return $propertyValue;
    }
    
    public static function setPropertyValue ($object, $propertyName, $value, $objectData=null)
    {
        if ($objectData == null)
            $objectData = new ReflectionObject($object);
        
        try
        {
            $methodName = 'set' . ucfirst($propertyName);
            if ($objectData->hasMethod($methodName))
                $objectData->getMethod($methodName)->invoke($object, $value);
        }
        catch (Exception $ex) {}
    }
    
    public static function getRecursivePropertyValue ($object, $propertyName, $entitySeparator = "_", $objectData = null)
    {
        if ($objectData == null)
            $objectData = new ReflectionObject($object);
        
        $propertyValue = null;
        $recursiveEntityPosition = strpos($propertyName, $entitySeparator);
        if ($recursiveEntityPosition != false)
        {
            $subentityName = substr($propertyName, 0, $recursiveEntityPosition);
            $subentityPropertyName = substr($propertyName, $recursiveEntityPosition+1);
            $subentityInstance = IntrospectionUtils::getPropertyValue ($object, $subentityName, $objectData);
            if (!empty($subentityInstance))
                $propertyValue = IntrospectionUtils::getRecursivePropertyValue ($subentityInstance, $subentityPropertyName, $entitySeparator);
        }
        else
        {
            $propertyValue = IntrospectionUtils::getPropertyValue ($object, $propertyName, $objectData);
        }
        return $propertyValue;
    }
    
    public static function setRecursivePropertyValue ($object, $propertyName, $value, $entitySeparator = "_", $objectData = null)
    {
        if ($objectData == null)
            $objectData = new ReflectionObject($object);
        
        $recursiveEntityPosition = strpos($propertyName, $entitySeparator);
        if ($recursiveEntityPosition != false)
        {
            $subentityName = substr($propertyName, 0, $recursiveEntityPosition);
            $subentityPropertyName = substr($propertyName, $recursiveEntityPosition+1);
            $subentityInstance = IntrospectionUtils::getPropertyValue ($object, $subentityName, $objectData);
            if (empty($subentityInstance))
            {
                $setSubentityMethodName = 'set' . ucfirst($subentityName);
                if ($objectData->hasMethod($setSubentityMethodName))
                {
                    $setSubentityMethod = $objectData->getMethod ($setSubentityMethodName);
                    $setSubentityMethodParameters = $setSubentityMethod->getParameters();
                    $setSubentityMethodParameter = $setSubentityMethodParameters[0];
                    $subentityClass = $setSubentityMethodParameter->getClass();
                    if ($subentityClass != null)
                    {
                        $subentityClassName = $subentityClass->getName();
                        $subentityInstance = new $subentityClassName();
                    }
                    else
                    {
                        $subentityInstance = new stdClass();
                    }
                    $setSubentityMethod->invoke($object, $subentityInstance);
                }
            }
            
            if (!empty($subentityInstance))
                IntrospectionUtils::setRecursivePropertyValue($subentityInstance, $subentityPropertyName, $value, $entitySeparator);
        }
        else
        {
            IntrospectionUtils::setPropertyValue ($object, $propertyName, $value, $objectData);
        }
    }
    
    public static function getPropertyValues ($object)
    {
        $properties = array();
        $objectData = new ReflectionObject($object);
        $objectProperties = $objectData->getProperties();
        foreach ($objectProperties as $property)
        {
            $propertyName = $property->getName();
            $properties[$propertyName] = IntrospectionUtils::getPropertyValue($object, $propertyName, $objectData);
        }
        return $properties;
    }
    
    public static function setPropertyValues ($object, $properties)
    {
        $objectData = new ReflectionObject($object);
        foreach ($properties as $propertyName=>$propertyValue)
            IntrospectionUtils::setPropertyValue($object, $propertyName, $propertyValue, $objectData);
    }
    
    public static function setRecursivePropertyValues ($object, $properties, $entitySeparator = "_")
    {
        $objectData = new ReflectionObject($object);
        foreach ($properties as $propertyName=>$propertyValue)
            IntrospectionUtils::setRecursivePropertyValue($object, $propertyName, $propertyValue, $entitySeparator, $objectData);
    }
}

?>
