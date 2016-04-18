<?php

namespace NeoPHP\util\properties;

use NeoPHP\util\StringUtils;
use stdClass;

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
        return isset($this->properties->$name)? $this->properties->$name : null;
    }
    
    public function has($name)
    {
        return isset($this->properties->$name);
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
        $properties = null;
        if (file_exists($filename))
        {
            if (StringUtils::endsWith($filename, "ini"))
            {
                $propertiesArray = @parse_ini_file($filename);
                $properties = new stdclass;
                foreach ($propertiesArray as $key=>$value) 
                {
                    $c = $properties;
                    foreach (explode(".", $key) as $key) 
                    {
                        if (!isset($c->$key))                        
                            $c->$key = new stdClass;
                        $prev = $c;
                        $c = $c->$key;
                    }
                    $prev->$key = $value;
                }
            }
            else if (StringUtils::endsWith($filename, "json"))
            {
                $propertiesJson = @file_get_contents($filename);
                $properties = json_decode($propertiesJson);
            }
        }
        
        if ($properties != null)
        {
            $this->properties = !empty($this->properties)? (object)array_merge_recursive((array)$this->properties, (array)$properties) : $properties;
        }
    }
}

?>