<?php

class Preferences
{
    private static $instance;
    private $preferences = array();
    private function __construct() {}

    public static function getInstance()
    {
        if ( !isset(self::$instance))
            self::$instance = new self;
        return self::$instance;
    }
    
    public function __set($name, $value)
    {
        $this->preferences[$name] = $value;
    }

    public function __get($name)
    {
        if ( isset($this->preferences[$name]))
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
