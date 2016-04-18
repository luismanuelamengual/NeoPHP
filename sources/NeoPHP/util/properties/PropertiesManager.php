<?php

namespace NeoPHP\util\properties;

class PropertiesManager 
{
    private $properties;
    
    public function __construct($filename=null) 
    {
        $this->properties = [];
        if ($filename != null)
            $this->addPropertyFile ($filename);
    }
    
    public function __get($name)
    {
        return $this->get($name);
    }

    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    public function __unset($name)
    {
        $this->remove($name);
    }
    
    public function __isset($name)
    {
        return $this->has($name);
    }
    
    public function get($name)
    {
        return isset($this->properties[$name])? $this->properties[$name] : null;
    }
    
    public function has($name)
    {
        return isset($this->properties[$name]);
    }
    
    public function set($name, $value)
    {
        $this->properties[$name] = $value;
    }
    
    public function remove($name)
    {
        unset($this->properties[$name]);
    }
    
    public function getAll()
    {
        return $this->properties;
    }
    
    public function addPropertiesFile ($filename)
    {
        if (file_exists($filename))
        {
            $properties = @parse_ini_file($filename);
            if (!empty($properties))
            {
                $this->properties = array_merge($this->properties, $properties);
            }
        }
    }
}

?>