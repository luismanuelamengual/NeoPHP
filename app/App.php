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
    private $session;
    private $preferences;
    private $controllers = array();
    private $connections = array();
    private $views = array();
    private static $instance;
    
    private function __construct() 
    {
        $this->loadDefaultPreferences();
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
        $controller->executeAction($controllerAction, $params);
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
        if (empty($this->session))
        {
            require_once ('app/Session.php');
            $this->session = Session::getInstance();
        }    
        return $this->session;
    }
    
    public function getPreferences ()
    {
        if (empty($this->preferences))
        {
            require_once ('app/Preferences.php');
            $this->preferences = Preferences::getInstance();
        }
        return $this->preferences;
    }
    
    protected function loadDefaultPreferences ()
    {
    }
}

?>
