<?php

namespace NeoPHP\web\http;

use DateTime;

trait ResponseTrait 
{
    private $content;
    
    public function addHeader ($name, $value)
    {
        ResponseHeaders::getInstance()->addHeader($name, $value);
    }
    
    public function addRawHeader ($header)
    {
        ResponseHeaders::getInstance()->addRawHeader($header);
    }
    
    public function setStatusCode ($code)
    {
        ResponseHeaders::getInstance()->setStatusCodeHeader($code);
    }
    
    public function setContentType ($contentType)
    {
        $this->addHeader("Content-Type", $contentType);
    }
    
    public function setExpires (DateTime $dateTime)
    {
        $this->addHeader("Expires", $dateTime->format(DateTime::RFC1123));
    }
    
    public function setETag ($etag)
    {
        $this->addHeader("E-Tag", $etag); 
    }
    
    public function addCookie ($name, $value=null, $expire=null, $path=null, $domain=null, $secure=false, $httponly=false)
    {
        ResponseHeaders::getInstance()->addCookieHeader ($name, $value, $expire, $path, $domain, $secure, $httponly);
    }
    
    public function getHeaders()
    {
        return ResponseHeaders::getInstance();
    }
    
    public function setContent ($content)
    {
        $this->content = $content;
    }
    
    public function sendHeaders ()
    {
        ResponseHeaders::getInstance()->send();
    }
    
    public function sendContent ()
    {
        if (isset($this->content))
            print($this->content);
    }
    
    public function send ()
    {
        $this->sendHeaders();
        $this->sendContent();
    }
}