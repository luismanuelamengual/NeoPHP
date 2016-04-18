<?php

namespace NeoPHP\web;

use NeoPHP\mvc\TemplateView;
use NeoPHP\web\http\Request;
use NeoPHP\web\http\ResponseTrait;
use NeoPHP\web\http\Session;

class WebTemplateView extends TemplateView
{
    use ResponseTrait;
    
    protected final function getBaseUrl ()
    {
        return $this->getApplication()->getBaseUrl();
    }
    
    protected final function getResourceUrl ($resource)
    {
        return $this->getApplication()->getResourceUrl ($resource);
    }
    
    protected final function getUrl ($action="", $params=[])
    {
        return $this->getApplication()->getUrl($action, $params);
    }
    
    protected final function getRequest ()
    {
        return Request::getInstance();   
    }
    
    protected final function getSession ()
    {
        return Session::getInstance();
    }
    
    protected final function getBladeExtensions ()
    {
        $extensions = [];
        $extensions[] = function ($value) { return preg_replace('/(?<!\w)(\s*)@resource(\s*\(.*\))/', '$1<?php echo $this->getResourceUrl$2; ?>', $value); };
        $extensions[] = function ($value) { return preg_replace('/(?<!\w)(\s*)@url(\s*\(.*\))/', '$1<?php echo $this->getUrl$2; ?>', $value); };
        return $extensions;
    }
    
    public function send() 
    {   
        $this->setContent($this->render(true));
        $this->sendHeaders();
        $this->sendContent();
    }
}

?>