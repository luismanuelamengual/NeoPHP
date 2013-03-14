<?php

/**
 * Clase App - Atención, para un correcto funcionamiento es necesario 
 * utilizar las siguientes configuraciones en PHP.ini
 * session.auto_start = 1
 * session.use_cookies = 1
 * session.use_trans_sid = 0;
 */
class App
{
    private $views = array();
    private $controllers = array();
    private $connections = array();
    private static $instance;
    
    private function __construct() 
    {
        set_error_handler(array("App", "errorHandler"), E_ALL);
    }

    public static function getInstance()
    {
        if ( !isset(self::$instance))
            self::$instance = new self;
        return self::$instance;
    }
 
    public function getBasePath()
    {
        $reflectionObject = new ReflectionObject(App::getInstance());
        return dirname(dirname($reflectionObject->getFileName()));
    }
    
    public function getBaseUrl()
    {
        $scriptName = $_SERVER["SCRIPT_NAME"];
        return substr($scriptName, 0, strpos($scriptName, "index.php"));        
    }
    
    public function getUrl($action, $params=array())
    {
        $url = $this->getBaseUrl() . "?action=" . $action;
        if (sizeof($params) > 0)
            $url .= "&" . http_build_query($params);
        return $url;
    }
    
    public function executeAction($action, $params=array())
    {
        $returnValue = FALSE;
        try
        {
            if ($action == null)
                $action = "default";
            $controllerSeparatorPosition = strrpos($action, "/");
            if ($controllerSeparatorPosition === FALSE)
            {
                $controllerName = "main";
                $controllerAction = $action;
            }
            else
            {
                $controllerName = substr($action,0,$controllerSeparatorPosition);
                $controllerAction = substr($action,$controllerSeparatorPosition+1,strlen($action));
            }
            $controller = $this->getController($controllerName);
            $returnValue = $controller->executeAction($controllerAction, $params);
        }
        catch (Exception $ex)
        {
            $this->getLogger()->error ($ex);
            print($ex);
            exit;
        }
        return $returnValue;
    }

    public function redirectAction($action, $params=array())
    {
        $this->redirect($this->getUrl($action, $params));
    }
    
    public function redirect($url)
    {
        header("Location: " . $url);
    }
    
    public function getController ($controllerName)
    {
        if (!isset($this->controllers[$controllerName]))
        {
            require_once ('app/Controller.php');
            $this->controllers[$controllerName] = $this->get($controllerName, "controller", "controllers/");
        }
        return $this->controllers[$controllerName];
    }
    
    public function getView ($viewName)
    {
        if (!isset($this->views[$viewName]))
        {
            require_once ('app/View.php');
            $this->views[$viewName] = $this->get($viewName, "view", "views/");
        }
        return $this->views[$viewName];
    }
    
    public function getConnection ($connectionName)
    {
        if (!isset($this->connections[$connectionName]))
        {
            require_once ('app/Connection.php');
            $this->connections[$connectionName] = $this->get($connectionName, "connection", "connections/");
        }
        return $this->connections[$connectionName];
    }
    
    public function get ($name, $category, $basePath = "")
    {
        $pathSeparatorPosition = strrpos($name, "/");
        $pathSeparatorPosition = ($pathSeparatorPosition != false)? ($pathSeparatorPosition+1) : 0;
        $className = ucfirst(substr($name,$pathSeparatorPosition,strlen($name))) . ucfirst($category);
        require_once ("app/" . $basePath . substr($name,0,$pathSeparatorPosition) . $className . '.php');
        return new $className;
    }
    
    public function getSession ()
    {
        require_once ('app/Session.php');
        return Session::getInstance();
    }
    
    public function getPreferences ()
    {
        require_once ('app/Preferences.php');
        return Preferences::getInstance();
    }
    
    public function getTranslator ()
    {
        require_once ('app/Translator.php');
        return Translator::getInstance();
    }
    
    public function getLogger ()
    {
        require_once ('app/Logger.php');
        return Logger::getInstance();
    }
    
    public function errorHandler ($errno, $errstr, $errfile, $errline)
    {
        throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
    }
}

?>
