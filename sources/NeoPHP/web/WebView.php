<?php

namespace NeoPHP\web;

use NeoPHP\mvc\View;
use NeoPHP\web\http\ResponseTrait;

abstract class WebView extends View
{   
    use ResponseTrait;
    
    public function __construct (WebApplication $application)
    {
        parent::__construct($application);
    }
    
    protected final function getBaseUrl ()
    {
        return $this->application->getBaseUrl();
    }
    
    protected final function getResourceUrl ($resource)
    {
        return $this->application->getResourceUrl ($resource);
    }
    
    protected final function getUrl ($action="", $params=[])
    {
        return $this->application->getUrl($action, $params);
    }
    
    protected final function getRequest ()
    {
        return $this->application->getRequest();
    }
    
    protected final function getSession ()
    {
        return $this->application->getSession();
    }
    
    public function sendContent() 
    {
        $this->render();
    }
}