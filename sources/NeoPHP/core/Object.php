<?php

namespace NeoPHP\core;

use NeoPHP\core\reflect\ReflectionAnnotatedClass;

abstract class Object
{
    private static $classes = array();
    
    /**
     * Retorna la clase del objeto
     * @return ReflectionAnnotatedClass
     */
    public static final function getClass ()
    {
        $className = isset($this)? get_class($this) : get_called_class();
        if (!isset(self::$classes[$className]))
            self::$classes[$className] = new ReflectionAnnotatedClass($className);
        return self::$classes[$className];
    }
    
    public function equals (Object $object)
    {
        return ($this->hashCode() == $object->hashCode());
    }
    
    public function hashCode ()
    {
        return implode("|", get_object_vars($this));
    }
    
    public function toString ()
    {
        return get_class($this) . "@" . Integer::toHexString($this->hashCode());
    }
    
    public function __toString()
    {
        return $this->toString();
    }
}