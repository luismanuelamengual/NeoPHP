<?php

namespace NeoPHP\Http;

class RequestHeaders 
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
        if (!isset($this->headers))
            $this->headers = apache_request_headers();
        
        return $name == null? $this->headers : $this->headers[$name];
    }
}