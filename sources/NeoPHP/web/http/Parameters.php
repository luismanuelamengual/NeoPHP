<?php

namespace NeoPHP\web\http;

class Parameters 
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
        return $name == null? $_REQUEST : (isset($_REQUEST[$name]) ? $_REQUEST[$name] : null);
    }
    
    public function getQuery ($name=null)
    {
        return $name == null? $_GET : (isset($_GET[$name]) ? $_GET[$name] : null);
    }
    
    public function getPost ($name=null)
    {
        return $name == null? $_POST : (isset($_POST[$name]) ? $_POST[$name] : null);
    }
    
    public function has ($name)
    {
        return isset($_REQUEST[$name]);
    }
    
    public function hasQuery ($name)
    {
        return isset($_GET[$name]);
    }
    
    public function hasPost ($name=null)
    {
        return isset($_POST[$name]);
    }
}