<?php

namespace NeoPHP\web;

use NeoPHP\mvc\TemplateView;
use NeoPHP\web\http\ResponseTrait;

class WebTemplateView extends TemplateView
{
    use ResponseTrait;
    
    public function __construct (WebApplication $application, $templateName, array $parameters = [])
    {
        parent::__construct($application, $templateName, $parameters);
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
    
    public function send() 
    {
        $this->setContent($this->render(true));
        $this->sendHeaders();
        $this->sendContent();
    }
}