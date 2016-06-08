<?php

namespace NeoPHP\mvc;

class ModelSorter
{
    const DIRECTION_ASCENDING = "ASC";
    const DIRECTION_DESCENDING = "DESC";
    
    private $property;
    private $direction;
    
    public function __construct($property, $direction=self::DIRECTION_ASCENDING)
    {
        $this->property = $property;
        $this->direction = $direction;
    }

    public function getProperty()
    {
        return $this->property;
    }

    public function getDirection()
    {
        return $this->direction;
    }
}