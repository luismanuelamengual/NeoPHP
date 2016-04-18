<?php

namespace NeoPHP\core\annotation;

class Annotation 
{
    private $name;
    private $parameters;
    
    public function __construct($name, $parameters=array())
    {
        $this->name = $name;
        $this->parameters = $parameters;
    }
    
    public function setName ($name)
    {
        $this->name = $name;
    }
    
    public function getName ()
    {
        return $this->name;
    }
    
    public function setParameters ($parameters)
    {
        $this->parameters = $parameters;
    }
    
    public function getParameters ()
    {
        return $this->parameters;
    }
    
    public function getParameter ($name)
    {
        return isset($this->parameters[$name])? $this->parameters[$name] : null;
    }
}

?>
