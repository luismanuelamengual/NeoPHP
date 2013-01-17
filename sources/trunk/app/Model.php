<?php

abstract class Model 
{
    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    public function __get($name)
    {
        if (isset($this->$name))
            return $this->$name;
    }
    
    public function __isset($name)
    {
        return isset($this->$name);
    }
   
    public function __unset($name)
    {
        unset($this->$name);
    }
}

?>
