<?php

namespace NeoPHP\mvc;

use NeoPHP\app\ApplicationComponent;
use NeoPHP\core\IllegalArgumentException;
use NeoPHP\mvc\Controller;
use NeoPHP\mvc\ModelManager;
use NeoPHP\mvc\MVCApplication;
use NeoPHP\mvc\NoRouteException;
use NeoPHP\mvc\TemplateView;
use NeoPHP\mvc\View;
use NeoPHP\util\logging\Logger;
use NeoPHP\util\properties\PropertiesManager;
use ReflectionFunction;
use ReflectionMethod;

abstract class Controller extends ApplicationComponent
{
    const ANNOTATION_ACTION = "action";
    const ANNOTATION_PARAMETER_NAME = "name";
   
    private $useAnnotations = false;
    
    public function __construct (MVCApplication $application)
    {
        parent::__construct($application);
        if (isset($this->getProperties()->useControllerAnnotations))
            $this->useAnnotations = $this->getProperties()->useControllerAnnotations;
    }
    
    /**
     * Obtiene el manager de propiedades de la aplicación
     * @return PropertiesManager Propiedades de la aplicación
     */
    protected final function getProperties ()
    {
        return $this->application->getProperties();
    }
    
    /**
     * Obtiene el logger de la aplicación
     * @return Logger Logger de la aplicación
     */
    protected final function getLogger ()
    {
        return $this->application->getLogger();
    }
    
    /**
     * Obtiene el manejador de modelos
     * @param string $managerClass
     * @return ModelManager Manejador de modelos
     */
    protected final function getManager ($managerClass)
    {
        return $this->application->getManager($managerClass);
    }
    
    /**
     * Obtiene un controlador para un clase dada
     * @param $controllerClass clase de controlador a obtener
     * @return Controller Controlador
     * @throws IllegalArgumentException
     */
    protected final function getController ($controllerClass)
    {
        return $this->application->getController($controllerClass);
    }
    
    /**
     * Crea una vista a partir de una clase
     * @param $viewClass Claes de la vista a crear
     * @return View Vista a ser obtenida
     * @throws IllegalArgumentException
     */
    protected final function createView ($viewClass)
    {
        return $this->application->createView($viewClass);
    }
    
    /**
     * Crea una vista de template
     * @param $templateName nombre del template a renderizar
     * @return TemplateView Vista de template
     */
    protected final function createTemplateView ($templateName, array $parameters = [])
    {
        return $this->application->createTemplateView($templateName, $parameters);
    }
    
    public function executeAction ($action, array $parameters = [])
    {
        $response = false;
        try
        {
            if ($this->onBeforeAction($action, $parameters) == true)
            {
                $response = $this->onAction ($action, $parameters);
                $response = $this->onAfterAction ($action, $parameters, $response);
            }
        }
        catch (Exception $ex)
        {
            if (method_exists($this, "onActionError"))
                $this->onActionError($action, $ex);
            else
                throw $ex;
        }
        return $response;
    }
    
    protected function onBeforeAction ($action, $parameters)
    {  
        return true;
    }
    
    protected function onAction ($action, $parameters)
    {
        $controllerMethod = $this->getMethodForAction($action);
        if ($controllerMethod == null)
        {
            throw new NoRouteException("Controller method for action \"$action\" not found in controller \"" . get_class($this) . "\"");
        }
        if (!method_exists($this, $controllerMethod))
        {
            throw new NoRouteException("Controller method \"$controllerMethod\" not found in controller \"" . get_class($this) . "\"");
        }
        return $this->callMethod($controllerMethod, $parameters);
    }
    
    protected function onAfterAction ($action, $parameters, $response)
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
        return $response;
    }
    
    private function getMethodForAction ($action)
    {
        if (empty($action))
            $action = "index";
        
        $methodName = null;
        if ($this->useAnnotations)
        {
            foreach ($this->getClass()->getMethods() as $method)
            {
                $annotations = $method->getAnnotations (self::ANNOTATION_ACTION);
                if (!empty($annotations))
                {
                    foreach ($annotations as $annotation)
                    {
                        $methodAction = $annotation->getParameter (self::ANNOTATION_PARAMETER_NAME);
                        if (empty($methodAction))
                            $methodAction = "index";

                        if ($action == $methodAction || (empty($action) && empty($methodAction)))
                        {
                            $methodName = $method->getName();
                            break;
                        }
                    }
                }
            }
        }
        else
        {
            $methodName = $action . "Action";
        }
        return $methodName;
    }
    
    private function callMethod ($method, array $parameters=[])
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