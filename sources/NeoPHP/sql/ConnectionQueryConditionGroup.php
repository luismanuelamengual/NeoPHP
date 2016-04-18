<?php

namespace NeoPHP\sql;

class ConnectionQueryConditionGroup
{
    private $conditions;
    private $connector;
    
    public function __construct($connector = "AND")
    {
        $this->connector = $connector;
        $this->conditions = [];
    }
    
    public function clear ()
    {
        $this->conditions = [];
        return $this;
    }
    
    public function isEmpty()
    {
        return empty($this->conditions);
    }
    
    public function getConditions()
    {
        return $this->conditions;
    }
    
    public function setConditions (array $conditions = [])
    {
        $this->conditions = $conditions;
    }
    
    public function addConditions (array $conditions = [])
    {
        $this->conditions = array_merge($this->conditions, $conditions);
    }
    
    public function getConnector()
    {
        return $this->connector;
    }
    
    public function setConnector ($connector)
    {
        $this->connector = $connector;
    }
    
    public function addCondition ($operand1, $operator, $operand2)
    {
        $this->conditions[] = ["operand1"=>$operand1, "operator"=>$operator, "operand2"=>$operand2];
        return $this;
    }
    
    public function addRawCondition ($expression, array $bindings = [])
    {
        $this->conditions[] = ["expression"=>$expression, "bindings"=>$bindings];
        return $this;
    }
    
    public function addConditionGroup (DatabaseQueryConditionGroup $conditionGroup)
    {
        $this->conditions[] = $conditionGroup;
        return $this;
    }
}