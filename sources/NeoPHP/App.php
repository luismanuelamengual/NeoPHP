<?php

/**
 * Si se activa el modo REST se requieren 3 cosas:
 * 1) Activación del modulo rewrite. Se hace con el siguiente comando: "sudo a2enmod rewrite" 
 * 2) Configurar en el archivo de configuración de apache para el DirectoryIndex adecuado la propiedad "AllowOverride All"
 * 3) Utilización de un archivo .htaccess en el raiz del proyecto con el siguient contenido
 * DirectoryIndex index.php
 * <IfModule mod_rewrite.c>
 *   RewriteEngine On
 *   RewriteRule ^$ index.php [QSA,L]
 *   RewriteCond %{REQUEST_FILENAME} !-f
 *   RewriteCond %{REQUEST_FILENAME} !-d
 *   RewriteRule ^(.*)$ index.php [QSA,L]
 * </IfModule>
 */
final class App
{
    private static $instance;
    private $restfull;
    private $controllers = array();
    private $connections = array();
    
    private function __construct ()
    {
        set_error_handler(array("App", "errorHandler"), E_ALL);
    }

    public static function getInstance ()
    {
        if (!isset(self::$instance))
            self::$instance = new self;
        return self::$instance;
    }
    
    public function start ()
    {
        $this->executeAction(($this->restfull)? substr($_SERVER["REQUEST_URI"], strlen(dirname($_SERVER["SCRIPT_NAME"]))+1) : (!empty($_REQUEST['action'])? $_REQUEST['action'] : null));
    }
    
    public function setRestfull ($restfull)
    {
        $this->restfull = $restfull;
    }
    
    public function executeAction ($action, $params=array())
    {
        try
        {
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
            return $this->getController($controllerName)->executeAction($controllerAction, $params);
        }
        catch (Exception $ex)
        {
            $this->getLogger()->error ($ex);
            exit;
        }
    }

    public function redirectAction ($action, $params=array())
    {
        $this->redirect($this->getUrl($action, $params));
    }
    
    public function redirect ($url)
    {
        header("Location: " . $url);
    }
    
    public function getBasePath()
    {
        return dirname($_SERVER["SCRIPT_FILENAME"]) . DIRECTORY_SEPARATOR;
    }
    
    public function getBaseUrl()
    {
        return dirname($_SERVER["SCRIPT_NAME"]) . "/";
    }
    
    public function getUrl ($action, $params=array())
    {
        $url = $this->getBaseUrl();
        if (!$this->restfull)
            $url .= "?action=";
        $url .= $action;
        if (sizeof($params) > 0)
            $url .= (($this->restfull)?"?":"&") . http_build_query($params);
        return $url;
    }
    
    public function getController ($controllerName)
    {
        if (!isset($this->controllers[$controllerName]))
        {
            require_once ('NeoPHP/Controller.php');
            $this->controllers[$controllerName] = $this->get($controllerName, "controller", "controllers/");
        }
        return $this->controllers[$controllerName];
    }
    
    public function getConnection ($connectionName)
    {
        if (!isset($this->connections[$connectionName]))
        {
            require_once ('NeoPHP/Connection.php');
            $this->connections[$connectionName] = $this->get($connectionName, "connection", "connections/");
        }
        return $this->connections[$connectionName];
    }
    
    public function getView ($viewName)
    {
        require_once ('NeoPHP/View.php');
        return $this->get($viewName, "view", "views/");
    }
    
    public function getModel ($modelName)
    {
        require_once ('NeoPHP/Model.php');
        return $this->get($modelName, "model", "models/");
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
        require_once ('NeoPHP/Session.php');
        return Session::getInstance();
    }
    
    public function getPreferences ()
    {
        require_once ('NeoPHP/Preferences.php');
        return Preferences::getInstance();
    }
    
    public function getTranslator ()
    {
        require_once ('NeoPHP/Translator.php');
        return Translator::getInstance();
    }
    
    public function getLogger ()
    {
        require_once ('NeoPHP/Logger.php');
        return Logger::getInstance();
    }
    
    public function errorHandler ($errno, $errstr, $errfile, $errline)
    {
        throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
    }
}

?>
