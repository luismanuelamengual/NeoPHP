<?php

namespace NeoPHP\sql;

class ConnectionQueryRawFilter extends ConnectionQueryFilter
{
    private $filter;
    private $bindings;
    
    public function __construct($filter, array $bindings = [])
    {
        $this->filter = $filter;
        $this->bindings = $bindings;
    }
    
    public function getFilter()
    {
        return $this->filter;
    }

    public function getBindings()
    {
        return $this->bindings;
    }
}