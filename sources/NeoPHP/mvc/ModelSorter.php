<?php

namespace NeoPHP\mvc;

use stdClass;

class ModelSorter
{
    const DIRECTION_ASCENDING = "ASC";
    const DIRECTION_DESCENDING = "DESC";
    
    private $sorters;
    
    public function __construct ()
    {
        $this->sorters = [];
    }

    public function addSort ($property, $direction = self::DIRECTION_ASCENDING)
    {
        $sort = new stdClass();
        $sort->property = $property;
        $sort->direction = $direction;
        $this->sorters[] = $sort;
    }
    
    public function getSorters ()
    {
        return $this->sorters;
    }
}