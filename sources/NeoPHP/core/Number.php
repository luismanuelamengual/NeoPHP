<?php

namespace NeoPHP\core;

use JsonSerializable;

abstract class Number extends Object implements JsonSerializable
{
    protected $value;
    
    public function __construct($value)
    {
        $this->value = $value;
    }
    
    public function intValue()
    {
        return (int)$this->value;
    }
    
    public function floatValue()
    {
        return (float)$this->value;
    }
    
    public function doubleValue()
    {
        return (double)$this->value;
    }
    
    public function jsonSerialize()
    {
        return $this->value;
    }
    
    public function toString()
    {
        return strval($this->value);
    }
    
    public function hashCode()
    {
        return $this->value;
    }
}