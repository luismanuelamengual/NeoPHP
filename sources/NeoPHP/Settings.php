<?php

final class Settings
{
    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    public function __get($name)
    {
        return isset($this->$name)? $this->$name : false;
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
