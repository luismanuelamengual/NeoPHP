<?php

namespace NeoPHP\mvc;

use Exception;
use NeoPHP\app\Application;
use NeoPHP\core\IllegalArgumentException;
use NeoPHP\mvc\manager\DefaultModelManager;
use NeoPHP\mvc\manager\ModelManager;
use NeoPHP\util\StringUtils;

abstract class MVCApplication extends Application 
{
    private $routes = [];
    private $controllers = [];
    private $managers = [];
    private $registeredManagers = [];
    
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
     * @param type $controllerClass Controlador que debe tomar la ruta
     */
    public function addRoute ($path, $controllerClass)
    {
        $this->routes[$this->normalizePath($path)] = $controllerClass;
    }
    
    /**
     * Registra un nuevo manager para un modelo de clase en particular
     * @param type $modelClass Clase del modelo
     * @param type $managerClass Clase del manager que lo va a manejar
     */
    public function registerManager ($modelClass, $managerClass)
    {
        $this->registeredManagers[$modelClass] = $managerClass;
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
     * Obtiene un manejador de modelos a través de un modelo en particular 
     * @param $modelClass Clase del modelo que maneja el manager
     * @return ModelManager Manejador de modelos
     */
    public final function getManager ($modelClass)
    {
        if (!isset($this->managers[$modelClass]))
        {
            $manager = null;
            if (isset($this->registeredManagers[$modelClass]))
            {
                $managerClassName = $this->registeredManagers[$modelClass];
                $manager = new $managerClassName($this, $modelClass);
            }
            else
            {
                if (isset($this->getProperties()->defaultModelManagerClass))
                {
                    $defaultManagerClassName = $this->getProperties()->defaultModelManagerClass;
                    $manager = new $defaultManagerClassName($this, $modelClass);
                }
                else
                {
                    $manager = new DefaultModelManager($this, $modelClass);
                }
            }
            $this->managers[$modelClass] = $manager;
        }
        return $this->managers[$modelClass];
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