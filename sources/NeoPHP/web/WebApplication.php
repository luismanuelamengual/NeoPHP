<?php

namespace NeoPHP\web;

use Exception;
use NeoPHP\mvc\ControllerNotFoundException;
use NeoPHP\mvc\MVCApplication;
use NeoPHP\util\StringUtils;
use NeoPHP\web\http\Request;
use NeoPHP\web\http\Response;

/**
 * Si se activa el modo REST se requieren 4 cosas:
 * 1) Activación del modulo rewrite. Se hace con el siguiente comando: "sudo a2enmod rewrite" 
 * 2) Configurar en el archivo de configuración de apache para el DirectoryIndex adecuado la propiedad "AllowOverride All"
 * 3) Utilización de un archivo .htaccess en el raiz del proyecto con el siguiente contenido
 * DirectoryIndex index.php
 * <IfModule mod_rewrite.c>
 *     RewriteEngine on
 *     RewriteCond %{REQUEST_FILENAME} !-d
 *     RewriteCond %{REQUEST_FILENAME} !-f
 *     RewriteRule ^([\w|\/]*)$ index.php [L]
 * </IfModule>
 * NOTA: En caso de tener alias a un proyecto debería ser (RewriteRule . /{alias}/index.php
 * 4) Las url de archivos css y js deben ser completas, NO relativas
 */
class WebApplication extends MVCApplication
{    
    private $restfull;
    private $actionParameterName = "action";
        
    public function setRestFull ($restfull)
    {
        $this->restfull = $restfull;
    }

    public function isRestFull ()
    {
        return $this->restfull;
    }
    
    public function setActionParameterName ($actionParameterName)
    {
        $this->actionParameterName = $actionParameterName;
    }
    
    public function getActionParameterName ()
    {
        return $this->actionParameterName;
    }

    public function getAssetsManager ()
    {
        if (!isset($this->assetsManager))
            $this->assetsManager = new WebAssetsManager();
        return $this->assetsManager;
    }
    
    public function getPublicPath ()
    {
        return realpath(".");
    }
    
    public function getBaseUrl ()
    {
        $baseUrl = isset($_SERVER["REQUEST_SCHEME"])?$_SERVER["REQUEST_SCHEME"]:"http";
        $baseUrl .= "://";
        $baseUrl .= $_SERVER["SERVER_NAME"];
        $baseUrl .= (empty($_SERVER["CONTEXT_PREFIX"])? "/" : $_SERVER["CONTEXT_PREFIX"]);
        return $baseUrl;
    }
    
    public function getResourceUrl ($resource)
    {
        return $this->getBaseUrl() . $resource;
    }
    
    public function getUrl ($action="", $params=array())
    {
        $action = trim($action);
        if (StringUtils::startsWith($action, "/"))
            $action = substr($action, 1);
        $url = $this->getBaseUrl();
        if ($this->restfull)
        {
            $url .= $action;
        }
        else
        {
            if (!empty($action))
                $params[$this->getActionParameterName()] = $action;
        }
        
        if (sizeof($params) > 0)
            $url .= "?" . http_build_query($params);
        return $url;
    }
    
    public function handleRequest () 
    {
        $action = null;
        if ($this->restfull)
        {
            if (!empty($_SERVER["REDIRECT_URL"]))
            {
                $action = $_SERVER["REDIRECT_URL"];
                if (!empty($_SERVER["CONTEXT_PREFIX"]))
                    $action = substr ($action, strlen($_SERVER["CONTEXT_PREFIX"]));
            }
            else
            {
                $action = "";
            }
        }
        else
        {
            $actionParameterName = $this->getActionParameterName();
            if (!empty($_REQUEST[$actionParameterName]))
                $action = $_REQUEST[$actionParameterName];
        }
        $this->processAction($action, Request::getInstance()->getParameters()->get());
    }
    
    protected function onActionError ($action, Exception $ex)
    {
        $response = new Response();
        if ($ex instanceof ControllerNotFoundException)
        {
            $response->setStatusCode(404);
        }
        else
        {
            $response->setStatusCode(500);
            parent::onActionError($action, $ex);
        }
        
        $responseContent = "";
        $responseContent .= "ERROR: " . $ex->getMessage();
        $responseContent .= "<pre>";
        $responseContent .= print_r ($ex->getTraceAsString(), true);
        $responseContent .= "</pre>";
        $response->setContent($responseContent);
        $response->send();
    }
    
    protected function getControllerForAction($action) 
    {
        $controller = parent::getControllerForAction($action);
        $webControllerClassName = WebController::getClassName();
        if (!($controller instanceof $webControllerClassName))
            throw new ControllerNotFoundException("Controller \"{$controller::getClassName()}\" must extend \"$webControllerClassName\"");
        return $controller;
    }
}

?>