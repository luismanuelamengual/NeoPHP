<?php

class Server
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
        return (isset($_SERVER[$name]))? $_SERVER[$name] : false;
    }
    
    public function __isset($name)
    {
        return isset($_SERVER[$name]);
    }
    
    public function getServerVars ()
    {
        return $_SERVER;
    }
}

?>
