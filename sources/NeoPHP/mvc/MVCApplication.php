<?php

namespace NeoPHP\mvc;

use Exception;
use NeoPHP\app\Application;
use NeoPHP\util\StringUtils;

class MVCApplication extends Application 
{   
    public function processAction ($action, $params=array())
    {
        $actionExecuted = false;
        try
        {
            $action = $this->normalizeAction($action);
            $this->executeAction ($action, $params);
            $actionExecuted = true;
        }
        catch (Exception $exception)
        {
            try 
            {
                $this->onActionError ($action, $exception);
            } 
            catch (Exception $error) {}
        }
        return $actionExecuted;
    }
    
    protected function onActionError ($action, Exception $ex)
    {
        $this->getLogger()->error($ex);
    }
    
    protected function normalizeAction ($action="")
    {
        $action = trim($action);
        if (!StringUtils::startsWith($action, "/"))
            $action = "/" . $action;
        return $action;
    }
    
    protected function executeAction ($action, $params=array())
    {
        $controllerAction = $action;
        if (StringUtils::endsWith($controllerAction, "/"))
            $controllerAction .= "index";
        $controllerAction = basename($controllerAction);
        $controller = $this->getControllerForAction($action);
        return $controller->executeAction ($controllerAction, $params);
    }
    
    protected function getControllerForAction ($action)		
    {		     
        $controllerClassName = $this->getControllerClassNameForAction($action);
        try 
        {
            $controller = $controllerClassName::getInstance();
        } 
        catch (Exception $ex) 
        {
            throw new ControllerNotFoundException("Controller not found for action \"$action\". Controllers namespace is \"{$this->getProperty("controllersNamespace", "controller")}\"");
        }
        
        $baseControllerClassName = Controller::getClassName();
        if (!($controller instanceof $baseControllerClassName))
            throw new ControllerNotFoundException("Invalid controller for action \"$action\"");
        return $controller;
    }
    
    protected function getControllerClassNameForAction ($action)
    {
        $controllerAction = $action;
        if (substr_count($controllerAction, "/") <= 1)
            $controllerAction = "/main" . $controllerAction;
        if (StringUtils::endsWith($controllerAction, "/"))
            $controllerAction .= "index";
        
        $controllerName = substr(dirname($controllerAction), 1);		
        $controllerName = (strpos($controllerName, "/") != false? (dirname($controllerName) . "/" . ucfirst(basename($controllerName))) : ucfirst($controllerName)) . "Controller";
        $controllersNamespace = $this->getProperty("controllersNamespace", "controller");
        return $controllersNamespace . "\\" . str_replace("/", "\\", $controllerName);
    }
}

?>