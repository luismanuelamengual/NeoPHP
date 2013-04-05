<?php

final class Preferences
{
    private $preferences = array();
    
    public function __set($name, $value)
    {
        $this->preferences[$name] = $value;
    }

    public function __get($name)
    {
        if (isset($this->preferences[$name]))
            return $this->preferences[$name];
    }
    
    public function __isset($name)
    {
        return isset($this->preferences[$name]);
    }
   
    public function __unset($name)
    {
        unset($this->preferences[$name]);
    }
}

?>
