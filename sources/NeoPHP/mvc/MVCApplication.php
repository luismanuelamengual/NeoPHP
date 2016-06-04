<?php

namespace NeoPHP\mvc;

use Exception;
use NeoPHP\app\Application;
use NeoPHP\core\IllegalArgumentException;
use NeoPHP\util\StringUtils;

abstract class MVCApplication extends Application 
{
    private $routes = [];
    private $controllers = [];
    private $managers = [];
    
    public function __construct($basePath) 
    {   
        parent::__construct($basePath);
        if (isset($this->getProperties()->routes))
        {
            foreach ($this->getProperties()->routes as $route)
            {
                $this->addRoute($route->path, $route->controller);
            }
        }
    }
    
    protected function processAction ($action, array $parameters=[])
    {
        $actionExecuted = false;
        try
        {
            $this->executeAction ($action, $parameters);
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
    
    /**
     * Agrega una nueva ruta para llegar a una acción de controlador
     * @param type $path Ruta a procesar
     * @param type $controllerClassName Controlador que debe tomar la ruta
     */
    public function addRoute ($path, $controllerClassName)
    {
        $this->routes[$this->normalizePath($path)] = $controllerClassName;
    }
    
    protected function onActionError ($action, Exception $ex)
    {
        $this->getLogger()->error($ex);
    }
    
    protected function normalizePath ($path="")
    {
        if (!StringUtils::startsWith($path, "/"))
            $path = "/" . $path;
        if (!StringUtils::endsWith($path, "/"))
            $path .= "/";
        return $path;
    }
    
    protected function executeAction ($action, $params=array())
    {
        $controllerPath = "";
        $controllerAction = "";
        if (StringUtils::endsWith($action, "/"))
        {
            $controllerPath = $action;
            $controllerAction = "";
        }
        else
        {
            $controllerPath = dirname($action);
            if ($controllerPath == ".") $controllerPath = "/";
            $controllerAction = basename($action);
        }
        $controller = $this->getControllerForPath($controllerPath);
        return $controller->executeAction ($controllerAction, $params);
    }
    
    protected function getControllerForPath ($path)		
    {	
        $path = $this->normalizePath($path);
        
        $controllerClassName = null;
        
        if (isset($this->routes[$path]))
        {
            $controllerClassName = $this->routes[$path];
        }
        else if (isset($this->getProperties()->controllersNamespace))
        {
            $controllerPath = dirname($path);
            $controllerName = basename($path);
            if (empty($controllerName))
                $controllerName = "main";
            $controllerClassName = $this->getProperties()->controllersNamespace;
            if ($controllerPath != "/")
                $controllerClassName .= str_replace("/", "\\", $controllerPath);
            $controllerClassName .= "\\";
            $controllerClassName .= ucfirst($controllerName) . "Controller";
        }
        
        if ($controllerClassName == null)
            throw new NoRouteException("No controller registered for path \"$path\". Add new route via method \"addRoute\"");
        
        try
        {
            $controller = $this->getController($controllerClassName);
        }
        catch (Exception $ex)
        {
            throw new NoRouteException("Invalid or Non existent controller for className \"$controllerClassName\" Caused by: " . $ex->getMessage());
        }
        
        return $controller;
    }
    
    /**
     * Obtiene un controlador para un clase dada
     * @param $controllerClass clase de controlador a obtener
     * @return Controller Controlador
     * @throws IllegalArgumentException
     */
    public function getController ($controllerClass)
    {
        if (!isset($this->controllers[$controllerClass]))
        {
            if (!class_exists($controllerClass))
                throw new IllegalArgumentException("Controller \"$controllerClass\" not found !!.");
            
            if (!is_subclass_of($controllerClass, Controller::class))
                throw new IllegalArgumentException("Invalid controller class \"$controllerClass\". Make sure this extends Controller");
     
            $this->controllers[$controllerClass] = new $controllerClass($this);
        }
        return $this->controllers[$controllerClass];
    }
    
    /**
     * Obtiene un manejador de modelos
     * @param $managerClass Clase del manejador de modelos
     * @return ModelManager Manejador de modelos
     * @throws IllegalArgumentException
     */
    public final function getManager ($managerClass)
    {
        if (!isset($this->managers[$managerClass]))
        {
            if (!class_exists($managerClass))
                throw new IllegalArgumentException("Manager \"$managerClass\" not found !!.");

            if (!is_subclass_of($managerClass, ModelManager::class))
                throw new IllegalArgumentException("Invalid manager class \"$controllerClass\". Make sure this extends ModelManager");
        
            $this->managers[$managerClass] = new $managerClass($this);
        }
        return $this->managers[$managerClass];
    }
    
    /**
     * Crea una vista a partir de una clase
     * @param $viewClass Claes de la vista a crear
     * @return View Vista a ser obtenida
     * @throws IllegalArgumentException
     */
    public final function createView ($viewClass)
    {
        if (!class_exists($viewClass))
            throw new IllegalArgumentException("View \"$viewClass\" not found !!.");

        if (!is_subclass_of($viewClass, View::class))
            throw new IllegalArgumentException("Invalid view class \"$viewClass\". Make sure this extends View");
        
        return new $viewClass($this);
    }
    
    /**
     * Crea una vista de template
     * @param $templateName nombre del template a renderizar
     * @return TemplateView Vista de template
     */
    public function createTemplateView ($templateName, array $parameters = [])
    {
        return new TemplateView($this, $templateName, $parameters); 
    }
}