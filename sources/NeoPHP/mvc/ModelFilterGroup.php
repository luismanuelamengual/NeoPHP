<?php

namespace NeoPHP\mvc;

class ModelFilterGroup extends ModelFilter
{
    const CONNECTOR_OR = "or";
    const CONNECTOR_AND = "and";
    
    private $connector = self::CONNECTOR_AND;
    private $filters = [];
    
    public function __construct($connector = self::CONNECTOR_AND)
    {
        $this->connector = $connector;
        $this->filters = [];
    }

    public function getConnector()
    {
        return $this->connector;
    }

    public function setConnector($connector)
    {
        $this->connector = $connector;
    }
    
    public function getFilters()
    {
        return $this->filters;
    }

    public function addFilter (ModelFilter $filter)
    {
        $this->filters[] = $filter;
    }
    
    public function addPropertyFilter ($property, $operator, $value=null)
    {
        $this->addFilter (new PropertyModelFilter($property, $operator, $value));
    }
}