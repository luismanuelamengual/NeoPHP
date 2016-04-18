<?php

namespace NeoPHP\mvc;

use Exception;
use NeoPHP\core\Object;
use ReflectionFunction;
use ReflectionMethod;

abstract class Controller extends Object
{
    protected static $instances = array();
    
    protected function __construct ()
    {
    }
    
    public static function getInstance()
    {
        $calledClass = get_called_class();
        if (!isset(self::$instances[$calledClass]))
            self::$instances[$calledClass] = new $calledClass();
        return self::$instances[$calledClass];
    }
    
    protected static function getApplication ()
    {
        return MVCApplication::getInstance();
    }
    
    protected final function getLogger ()
    {
        return $this->getApplication()->getLogger();
    }
    
    protected final function getTranslator ()
    {
        return $this->getApplication()->getTranslator();
    }
    
    protected final function getConnection ($connectionName="main")
    {
        return $this->getApplication()->getConnection($connectionName);
    }
    
    protected function getText ($key, $language=null)
    {
        return $this->getTranslator()->getText($key, $language);
    }
    
    public function executeAction ($action, array $parameters = array())
    {
        $response = false;
        $controllerMethod = $this->getMethodForAction($action);
        if (method_exists($this, $controllerMethod))
        {
            try
            {
                if ($this->onBeforeActionExecution($action, $parameters) == true)
                {
                    $response = $this->callMethod($controllerMethod, $parameters);
                    $response = $this->onAfterActionExecution ($action, $parameters, $response);
                }
            }
            catch (Exception $ex)
            {
                if (method_exists($this, "onActionError"))
                    $this->onActionError($action, $ex);
                else
                    throw $ex;
            }
        }
        else
        {
            throw new ControllerNotFoundException("Action method \"$controllerMethod\" not found in controller \"{$this->getClassName()}\"");
        }
        return $response;
    }
    
    protected function onBeforeActionExecution ($action, $parameters)
    {  
        return true;
    }
    
    protected function onAfterActionExecution ($action, $parameters, $response)
    {
        if (!empty($response))
        {
            if ($response instanceof View)
            {
                $response->render();
            }
            else if (is_object($response))
            {
                print json_encode($response);
            }
            else
            {
                print $response;
            }
        }
    }
    
    protected function getMethodForAction ($action)
    {
        return $action . "Action";
    }
    
    protected function callMethod ($method, array $parameters=array())
    {
        $parameterIndex = 0;
        $callable = [$this, $method];
        $callableParameters = array();
        $callableData = is_array($callable)? (new ReflectionMethod($callable[0],$callable[1])) : (new ReflectionFunction($callable));
        foreach ($callableData->getParameters() as $parameter)
        {
            $parameterName = $parameter->getName();
            $parameterValue = null;
            if (array_key_exists($parameterName, $parameters))
                $parameterValue = $parameters[$parameterName];
            else if (array_key_exists($parameterIndex, $parameters))
                $parameterValue = $parameters[$parameterIndex];       
            if ($parameterValue == null && $parameter->isOptional())
                $parameterValue = $parameter->getDefaultValue();
            $callableParameters[] = $parameterValue;
            $parameterIndex++;
        }
        return call_user_func_array($callable, $callableParameters);
    }
}

?>