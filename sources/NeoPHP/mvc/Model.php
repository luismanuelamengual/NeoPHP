<?php

namespace NeoPHP\mvc;

use JsonSerializable;
use NeoPHP\core\Object;
use NeoPHP\util\IntrospectionUtils;

abstract class Model extends Object implements JsonSerializable
{
    public function jsonSerialize() 
    {
        return array_filter(IntrospectionUtils::getPropertyValues($this), function($var) { return $var != null; } );
    }
}

?>