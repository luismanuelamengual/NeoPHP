<?php

namespace NeoPHP\core;

use JsonSerializable;

final class Boolean extends Object implements JsonSerializable
{
    private $value;
    
    public function __construct ($value) 
    {
        if (!is_bool($value))
            throw new IllegalArgumentException ("Value \"$value\" is not a boolean");
        $this->value = $value;
    }
    
    public function jsonSerialize()
    {
        return $this->value;
    }
    
    public static function valueOf ($value)
    {
        return new Boolean($this->parseBoolean($value));
    }
    
    public static function parseBoolean ($value)
    {
        return boolval($value);
    }
    
    public function equals (Object $object)
    { 
        return ($object instanceof Boolean)? $this->value == $object->booleanValue() : false;
    }
    
    public function booleanValue()
    {
        return $this->value;
    }
    
    public function toString ()
    {
        return $this->value? "true":"false";
    }
}

?>