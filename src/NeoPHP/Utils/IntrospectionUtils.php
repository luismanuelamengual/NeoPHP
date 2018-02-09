<?php

namespace NeoPHP\Utils;

use Exception;
use ReflectionClass;
use stdClass;

abstract class IntrospectionUtils {

    private static $classes = [];

    /**
     * Obtiene la clase del objeto
     * @return ReflectionClass clase del objeto
     */
    private static function getClass($object) {
        $className = get_class($object);
        if (!isset(self::$classes[$className]))
            self::$classes[$className] = new ReflectionClass($object);
        return self::$classes[$className];
    }

    public static function getPropertyValue($object, $propertyName) {
        $propertyValue = null;
        try {
            $objectClass = self::getClass($object);
            $methodName = 'get' . ucfirst($propertyName);
            if ($objectClass->hasMethod($methodName)) {
                $propertyValue = $objectClass->getMethod($methodName)->invoke($object);
            }
            else {
                $methodName = 'is' . ucfirst($propertyName);
                if ($objectClass->hasMethod($methodName)) {
                    $propertyValue = $objectClass->getMethod($methodName)->invoke($object);
                }
                else {
                    $property = $objectClass->getProperty($propertyName);
                    if ($property->isPublic())
                        $propertyValue = $property->getValue($object);
                }
            }
        }
        catch (Exception $ex) {
        }
        return $propertyValue;
    }

    public static function setPropertyValue($object, $propertyName, $value) {
        try {
            $objectClass = self::getClass($object);
            $methodName = 'set' . ucfirst($propertyName);
            if ($objectClass->hasMethod($methodName)) {
                $objectClass->getMethod($methodName)->invoke($object, $value);
            }
            else {
                $property = $objectClass->getProperty($propertyName);
                if ($property->isPublic())
                    $property->setValue($object, $value);
            }
        }
        catch (Exception $ex) {
        }
    }

    public static function getRecursivePropertyValue($object, $propertyName, $entitySeparator = "_") {
        $propertyValue = null;
        $recursiveEntityPosition = strpos($propertyName, $entitySeparator);
        if ($recursiveEntityPosition != false) {
            $subentityName = substr($propertyName, 0, $recursiveEntityPosition);
            $subentityPropertyName = substr($propertyName, $recursiveEntityPosition + 1);
            $subentityInstance = self::getPropertyValue($object, $subentityName);
            if (!empty($subentityInstance))
                $propertyValue = self::getRecursivePropertyValue($subentityInstance, $subentityPropertyName, $entitySeparator);
        }
        else {
            $propertyValue = self::getPropertyValue($object, $propertyName);
        }
        return $propertyValue;
    }

    public static function setRecursivePropertyValue($object, $propertyName, $value, $entitySeparator = "_") {
        $recursiveEntityPosition = strpos($propertyName, $entitySeparator);
        if ($recursiveEntityPosition != false) {
            $subentityName = substr($propertyName, 0, $recursiveEntityPosition);
            $subentityPropertyName = substr($propertyName, $recursiveEntityPosition + 1);
            $subentityInstance = self::getPropertyValue($object, $subentityName);
            if (empty($subentityInstance)) {
                $objectClass = self::getClass($object);
                $setSubentityMethodName = 'set' . ucfirst($subentityName);
                if ($objectClass->hasMethod($setSubentityMethodName)) {
                    $setSubentityMethod = $objectClass->getMethod($setSubentityMethodName);
                    $setSubentityMethodParameters = $setSubentityMethod->getParameters();
                    $setSubentityMethodParameter = $setSubentityMethodParameters[0];
                    $subentityClass = $setSubentityMethodParameter->getClass();
                    if ($subentityClass != null) {
                        $subentityClassName = $subentityClass->getName();
                        $subentityInstance = new $subentityClassName();
                    }
                    else {
                        $subentityInstance = new stdClass();
                    }
                    $setSubentityMethod->invoke($object, $subentityInstance);
                }
            }

            if (!empty($subentityInstance))
                self::setRecursivePropertyValue($subentityInstance, $subentityPropertyName, $value, $entitySeparator);
        }
        else {
            self::setPropertyValue($object, $propertyName, $value);
        }
    }

    public static function getPropertyValues($object) {
        $properties = array();
        $objectClass = self::getClass($object);
        $objectProperties = $objectClass->getProperties();
        foreach ($objectProperties as $property) {
            $propertyName = $property->getName();
            $properties[$propertyName] = self::getPropertyValue($object, $propertyName);
        }
        return $properties;
    }

    public static function setPropertyValues($object, $properties) {
        $objectClass = self::getClass($object);
        foreach ($properties as $propertyName => $propertyValue)
            self::setPropertyValue($object, $propertyName, $propertyValue);
    }

    public static function setRecursivePropertyValues($object, $properties, $entitySeparator = "_") {
        $objectClass = self::getClass($object);
        foreach ($properties as $propertyName => $propertyValue)
            self::setRecursivePropertyValue($object, $propertyName, $propertyValue, $entitySeparator);
    }
}