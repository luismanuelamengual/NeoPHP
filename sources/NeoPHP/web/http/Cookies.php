<?php

namespace NeoPHP\web\http;

class Cookies 
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
        return $name == null? $_COOKIE : $_COOKIE[$name];
    }
    
    public function has ($name=null)
    {
        return isset($_COOKIE[$name]);
    }
    
    public function set ($name, $value=null, $expire=null, $path=null, $domain=null, $secure=false, $httponly=false)
    {
        setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
    }
}