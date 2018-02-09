<?php

namespace NeoPHP\sql;

class ConnectionQueryColumnFilter extends ConnectionQueryFilter
{
    private $column;
    private $operator;
    private $value;
    
    public function __construct($column, $operator, $value=null)
    {
        if ($value == null) 
        {
            $value = $operator;
            $operator = "=";
        }
        
        $this->column = $column;
        $this->operator = $operator;
        $this->value = $value;
    }

    public function getProperty()
    {
        return $this->column;
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
