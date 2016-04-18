<?php

namespace NeoPHP\web;

use Exception;
use NeoPHP\web\http\Request;

abstract class WebRestController extends WebController
{
    public function indexAction ()
    {
        $methodName = "";
        switch ($this->getRequest()->getMethod())
        {
            case "GET":
                $methodName = "getResourceAction";
                break;
            case "PUT":
                $methodName = "createResourceAction";
                break;
            case "POST":
                $methodName = "updateResourceAction";
                break;
            case "DELETE":
                $methodName = "deleteResourceAction";
                break;
        }
        
        if (!method_exists($this, $methodName))
            throw new Exception ("Rest method \"$methodName\" doesnt exists in controller \"" . $this->getClassName() . "\"");
        
        $response = $this->callMethod($methodName, Request::getInstance()->getParameters()->get());
        return $response;
    }
}