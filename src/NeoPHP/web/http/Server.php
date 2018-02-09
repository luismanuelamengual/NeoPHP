<?php

namespace NeoPHP\web\http;

class Server 
{
    private static $instance;

    public static function getInstance()
    {
        if (!isset(self::$instance))
            self::$instance = new self;
        return self::$instance;
    }
    
    public function get ($name=null)
    {
        return $name == null? $_SERVER : $_SERVER[$name];
    }
    
    public function has ($name=null)
    {
        return isset($_SERVER[$name]);
    }
    
    public function getSoftware ()
    {
        return $this->get("SERVER_SOFTWARE");
    }
    
    public function getName ()
    {
        return $this->get("SERVER_NAME");
    }
    
    public function getAddress ()
    {
        return $this->get("SERVER_ADDR");
    }
    
    public function getPort ()
    {
        return $this->get("SERVER_PORT");
    }
}