<?php

class Request
{
    private static $instance;

    public static function getInstance()
    {
        if (!isset(self::$instance))
            self::$instance = new self;
        return self::$instance;
    }
    
    public function __get($name)
    {
        return (isset($_REQUEST[$name]))? $_REQUEST[$name] : false;
    }
    
    public function __isset($name)
    {
        return isset($_REQUEST[$name]);
    }
    
    public function getRequestVars ()
    {
        return $_REQUEST;
    }
}

?>
