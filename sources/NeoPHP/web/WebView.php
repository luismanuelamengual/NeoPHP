<?php

namespace NeoPHP\web;

use NeoPHP\mvc\View;
use NeoPHP\web\http\Request;
use NeoPHP\web\http\ResponseTrait;

abstract class WebView extends View
{   
    use ResponseTrait;
    
    protected final function getBaseUrl ()
    {
        return $this->getApplication()->getBaseUrl();
    }
    
    protected final function getUrl ($action="", $params=array())
    {
        return $this->getApplication()->getUrl($action, $params);
    }
    
    protected final function getRequest ()
    {
        return Request::getInstance();
    }
    
    protected final function getSession ()
    {
        return $this->getRequest()->getSession();
    }
    
    public function sendContent() 
    {
        $this->render();
    }
}

?>
