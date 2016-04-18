<?php

namespace NeoPHP\sql;

class EntityFilterGroup
{
    private $connector;
    private $filters;
    
    public function __construct($connector = SQL::OPERATOR_AND)
    {
        $this->connector = $connector;
        $this->filters = [];
    }

    public function clear ()
    {
        $this->filters = [];
        return $this;
    }
    
    public function isEmpty()
    {
        return empty($this->filters);
    }
    
    public function getFilters()
    {
        return $this->filters;
    }
    
    public function setFilters ($filters)
    {
        $this->filters = $filters;
    }
    
    public function getConnector()
    {
        return $this->connector;
    }
    
    public function setConnector ($connector)
    {
        $this->connector = $connector;
    }
    
    public function addFilter ($property, $operator, $value)
    {
        $this->filters[] = ["property"=>$property, "operator"=>$operator, "value"=>$value];
        return $this;
    }
    
    public function addFilterGroup (EntityFilterGroup $filterGroup)
    {
        $this->filters[] = $filterGroup;
        return $this;
    }
}