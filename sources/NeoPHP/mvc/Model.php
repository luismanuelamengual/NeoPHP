<?php

namespace NeoPHP\mvc;

use NeoPHP\core\Object;
use NeoPHP\util\Arrayable;
use NeoPHP\util\IntrospectionUtils;

abstract class Model extends Object implements Arrayable
{
    public function __construct(array $properties = []) 
    {
        $this->setFrom($properties);
    }
    
    public function setFrom (array $properties = []) 
    {
        IntrospectionUtils::setRecursivePropertyValues($this, $properties);
    }
    
    public function toArray ()
    {
        return IntrospectionUtils::getPropertyValues($this);
    }
}