<?php

namespace NeoPHP\core;

use NeoPHP\core\reflect\ReflectionAnnotatedClass;

abstract class Object
{
    private static $classData = array();
    
    public static final function getClassName ()
    {
        return isset($this)? get_class($this) : get_called_class();
    }
    
    /**
     * Retorna la clase del objeto
     * @return ReflectionAnnotatedClass
     */
    public static final function getClass ()
    {
        $className = self::getClassName();
        if (!isset(self::$classData[$className]))
            self::$classData[$className] = new ReflectionAnnotatedClass($className);
        return self::$classData[$className];
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
        return $this->getClassName() . "@" . Integer::toHexString($this->hashCode());
    }
    
    public function __toString()
    {
        return $this->toString();
    }
}

?>
