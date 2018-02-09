<?php

namespace NeoPHP\web\http;

final class Request
{
    const METHOD_GET = "GET";
    const METHOD_PUT = "PUT";
    const METHOD_POST = "POST";
    const METHOD_DELETE = "DELETE";
    const METHOD_HEAD = "HEAD";
    const METHOD_OPTIONS = "OPTIONS";
    const METHOD_PATCH = "PATCH";
    
    private static $instance;

    public static function getInstance()
    {
        if (!isset(self::$instance))
            self::$instance = new self;
        return self::$instance;
    }
    
    public function getParameters()
    {
        return Parameters::getInstance();
    }
    
    public function getHeaders()
    {
        return RequestHeaders::getInstance();
    }
    
    public function getCookies()
    {
        return Cookies::getInstance();
    }
    
    public function getSession()
    {
        return Session::getInstance();
    }
    
    public function getServer()
    {
        return Server::getInstance();
    }
    
    public function getFiles ()
    {
        return $_FILES;
    }
    
    public function getContent ()
    {
        return @file_get_contents("php://input");
    }
    
    public function getMethod ()
    {
        return $_SERVER["REQUEST_METHOD"];
    }
    
    public function isMethod ($method)
    {
        return is_array($method)? in_array($this->getMethod(), $method) : $this->getMethod() == $method;
    }
    
    public function isGet ()
    {
        return $this->isMethod(self::METHOD_GET);
    }
    
    public function isPost ()
    {
        return $this->isMethod(self::METHOD_POST);
    }
    
    public function isPut ()
    {
        return $this->isMethod(self::METHOD_PUT);
    }
    
    public function isDelete ()
    {
        return $this->isMethod(self::METHOD_DELETE);
    }
    
    public function getScheme ()
    {
        return $this->isSecureRequest() ? "https" : "http";
    }
    
    public function isAjax ()
    {
        return $_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest";
    }
    
    public function isSecureRequest ()
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
    }
    
    public function getServerAddress ()
    {
        return $_SERVER["SERVER_ADDR"];
    }
    
    public function getServerName ()
    {
        return $_SERVER["SERVER_NAME"];
    }
    
    public function getHttpHost ()
    {
        return $_SERVER["HTTP_HOST"];
    }
    
    public function getClientAddress ($trustForwardedHeader=false)
    {
        return $trustForwardedHeader === true ? $_SERVER["HTTP_X_FORWARDED_FOR"] : $_SERVER["REMOTE_ADDR"];
    }
    
    public function getUri ()
    {
        return $_SERVER["REQUEST_URI"];
    }
    
    public function getUserAgent ()
    {
        return $_SERVER["HTTP_USER_AGENT"];
    }
}

?>