<?php

abstract class Model 
{
    public function __call($method, $params) 
    {
        if (substr($method,0,3) == 'get')
        {
            $property = lcfirst(substr($method,3));
            return property_exists($this, $property)? $this->$property : null;
        } 
        else if (substr($method,0,3) == 'set')
        {
            $property = lcfirst(substr($method,3));
            if (property_exists($this, $property))
                $this->$property = $params[0];
        } 
        else 
        {
            return null;
        }
    }
}

?>
