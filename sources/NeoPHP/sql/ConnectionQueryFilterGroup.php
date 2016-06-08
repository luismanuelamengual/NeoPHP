<?php

namespace NeoPHP\sql;

class ConnectionQueryFilterGroup extends ConnectionQueryFilter
{
    private $connector;
    private $filters;
    
    public function __construct($connector = "AND")
    {
        $this->connector = $connector;
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

    public function addFilter (ConnectionQueryFilter $filter)
    {
        $this->filters[] = $filter;
    }
}