<?php

namespace NeoPHP\core;

final class Float extends Number
{
    public function __construct ($value) 
    {
        if (!is_float($value) && !is_int($value))
            throw new IllegalArgumentException ("Value \"$value\" is not a float");
        parent::__construct((float)$value);
    }
        
    public static function valueOf ($value)
    {
        return new Float($this->parseFloat($value));
    }
    
    public static function parseFloat ($value)
    {
        return floatval($value);
    }
    
    public function equals (Object $object)
    { 
        return ($object instanceof Float)? $this->value == $object->floatValue() : false;
    }
}

?>