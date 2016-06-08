<?php

namespace NeoPHP\mvc;

class PropertyModelFilter extends ModelFilter
{
    const OPERATOR_EQUALS = "eq";
    const OPERATOR_NOT_EQUALS = "ne";
    const OPERATOR_GREATER_THAN = "gt";
    const OPERATOR_GREATER_OR_EQUALS_THAN = "gte";
    const OPERATOR_LESS_THAN = "lt";
    const OPERATOR_LESS_OR_EQUALS_THAN = "lte";
    const OPERATOR_IN = "in";
    const OPERATOR_CONTAINS = "ct";
    
    private $property;
    private $operator;
    private $value;
    
    public function __construct($property, $operator, $value=null)
    {
        if ($value == null)
        {
            $value = $operator;
            $operator = self::OPERATOR_EQUALS;
        }
        
        $this->property = $property;
        $this->operator = $operator;
        $this->value = $value;
    }
    
    public function getProperty()
    {
        return $this->property;
    }

    public function getOperator()
    {
        return $this->operator;
    }

    public function getValue()
    {
        return $this->value;
    }
}