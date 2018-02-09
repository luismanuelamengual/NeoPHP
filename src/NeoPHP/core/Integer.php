<?php

namespace NeoPHP\core;

final class Integer extends Number
{    
    public function __construct ($value) 
    {
        if (!is_int($value))
            throw new IllegalArgumentException ("Value \"$value\" is not a integer");
        parent::__construct($value);
    }
    
    public static function valueOf ($value, $radix=10)
    {
        return new Integer(self::parseInt($value, $radix));
    }
    
    public static function parseInt ($value, $radix=10)
    {
        if ($radix != 10)
            $value = base_convert ($value, $radix, 10);        
        return intval($value);
    }
    
    public static function toHexString ($value)
    {
        return dechex($value);
    }
    
    public static function toBinaryString ($value)
    {
        return decbin($value);
    }
    
    public static function toOctalString ($value)
    {
        return decoct($value);
    }
    
    public function equals (Object $object)
    { 
        return ($object instanceof Integer)? $this->value == $object->intValue() : false;
    }
}

?>