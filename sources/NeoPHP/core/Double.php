<?php

namespace NeoPHP\core;

final class Double extends Number
{
    public function __construct ($value) 
    {
        if (!is_double($value) && !is_float($value) && !is_int($value))
            throw new IllegalArgumentException ("Value \"$value\" is not a double");
        parent::__construct((double)$value);
    }
    
    public static function valueOf ($value)
    {
        return new Double($this->parseDouble($value));
    }
    
    public static function parseDouble ($value)
    {
        return doubleval($value);
    }
    
    public function equals (Object $object)
    { 
        return ($object instanceof Double)? $this->value == $object->doubleValue() : false;
    }
}

?>