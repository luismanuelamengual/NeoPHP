<?php

/**
 * Si se activa el modo REST se requieren 4 cosas:
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
 * 4) Las url de archivos css y js deben ser completas, NO relativas
 */
final class App
{
    private static $instance;
    private $appFolderName;
    private $restfull;
    private $loader;
    
    private function __construct ()
    {
        set_error_handler(array("App", "errorHandler"), E_ALL);
        $this->appFolderName = "app";
        $this->restfull = false;
    }

    public static function getInstance ()
    {
        if (!isset(self::$instance))
            self::$instance = new self;
        return self::$instance;
    }
    
    public function handleRequest ()
    {
        $this->executeAction(($this->restfull)? substr($_SERVER["REQUEST_URI"], strlen(dirname($_SERVER["SCRIPT_NAME"]))+1) : (!empty($_REQUEST['action'])? $_REQUEST['action'] : null));
    }
    
    public function setAppFolderName ($appFolderName)
    {
        $this->appFolderName = $appFolderName;
        if (!empty($this->loader))
            $this->loader->setBasePath($this->appFolderName);
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
    
    public function getLoader ()
    {
        if (empty($this->loader))
        {
            require_once ("NeoPHP/Loader.php");
            $this->loader = new Loader($this->appFolderName);
        }
        return $this->loader;
    }
    
    public function getSession ()
    {
        return $this->getLoader()->getSingletonInstance("session", "NeoPHP");
    }
    
    public function getServer ()
    {
        return $this->getLoader()->getSingletonInstance("server", "NeoPHP");
    }
    
    public function getRequest ()
    {
        return $this->getLoader()->getSingletonInstance("request", "NeoPHP");
    }
    
    public function getSettings ()
    {
        return $this->getLoader()->getCacheInstance("settings", "NeoPHP");
    }
    
    public function getTranslator ()
    {
        return $this->getLoader()->getCacheInstance("translator", "NeoPHP");
    }
    
    public function getLogger ()
    {
        return $this->getLoader()->getCacheInstance("logger", "NeoPHP");
    }
    
    public function getController ($controllerName)
    {
        require_once("NeoPHP/Controller.php");
        return $this->getLoader()->getCacheInstance("controllers/" . $controllerName . "Controller");
    }
    
    public function getConnection ($connectionName)
    {
        require_once("NeoPHP/Connection.php");
        return $this->getLoader()->getCacheInstance("connections/" . $connectionName . "Connection");
    }
    
    public function getView ($viewName, $params=array())
    {
        require_once("NeoPHP/View.php");
        return $this->getLoader()->getInstance("views/" . $viewName . "View", $params);
    }
    
    public function getModel ($modelName, $params=array())
    {
        require_once("NeoPHP/Model.php");
        return $this->getLoader()->getInstance("models/" . $modelName . "Model", $params);
    }
    
    public function errorHandler ($errno, $errstr, $errfile, $errline)
    {
        throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
    }
}

?>
