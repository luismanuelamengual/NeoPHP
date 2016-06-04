<?php

namespace NeoPHP\web;

use Exception;
use NeoPHP\mvc\MVCApplication;
use NeoPHP\mvc\NoRouteException;
use NeoPHP\util\StringUtils;
use NeoPHP\web\http\Request;
use NeoPHP\web\http\Response;
use NeoPHP\web\http\Session;

/**
 * Si se activa el modo "prettyUrls" se requieren 4 cosas:
 * 1) Activación del modulo rewrite. Se hace con el siguiente comando: "sudo a2enmod rewrite" 
 * 2) Configurar en el archivo de configuración de apache para el DirectoryIndex adecuado la propiedad "AllowOverride All"
 * 3) Utilización de un archivo de configuración .htaccess (para apache) en el raiz del proyecto con el siguiente contenido
 * [APACHE] 
 * DirectoryIndex index.php
 * RewriteEngine on
 * RewriteCond %{REQUEST_FILENAME} !-d
 * RewriteCond %{REQUEST_FILENAME} !-f
 * RewriteRule ^ index.php [L]
 * NOTA: En caso de tener alias a un proyecto debería ser (RewriteRule . /{alias}/index.php
 * [NGINX]
 * location / {
 * try_files $uri $uri/ /index.php?$query_string;
 * }
 * 4) Las url de archivos css y js deben ser completas, NO relativas
 */
class WebApplication extends MVCApplication
{
    /**
     * Metodo que se encarga de procesar una peticion HTTP
     */
    public function handleRequest () 
    {
        $action = null;
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
        $this->processAction($action, Request::getInstance()->getParameters()->get());
    }
    
    protected function onActionError ($action, Exception $ex)
    {
        $response = new Response();
        if ($ex instanceof NoRouteException)
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
    
    /**
     * Obtiene la petición web efectuada
     * @return Request Petición web
     */
    public final function getRequest ()
    {
        return Request::getInstance();
    }
    
    /**
     * Obtiene la referencia a la sesion de la aplicación
     * @return Session sesion web
     */
    public final function getSession ()
    {
        return Session::getInstance();
    }
    
    /**
     * Obtiene el asset manager asociado a la aplicación
     * @return WebAssetsManager assets manager asociado a la aplicación
     */
    public function getAssetsManager ()
    {
        if (!isset($this->assetsManager))
        {
            $this->assetsManager = new WebAssetsManager();
            $this->assetsManager->setAssetsPath($this->getPublicPath() . DIRECTORY_SEPARATOR . "assets" );
            $this->assetsManager->setAssetsBaseUrl($this->getPublicBaseUrl() . "assets");
        }
        return $this->assetsManager;
    }
    
    /**
     * Crea una vista de template
     * @param $templateName nombre del template a renderizar
     * @return WebTemplateView Vista de template
     */
    public function createTemplateView ($templateName, array $parameters = [])
    {
        return new WebTemplateView($this, $templateName, $parameters); 
    }
    
    public function getPublicPath ()
    {
        return realpath(".");
    }
    
    public function getPublicBaseUrl ()
    {
        $baseUrl = isset($_SERVER["REQUEST_SCHEME"])?$_SERVER["REQUEST_SCHEME"]:"http";
        $baseUrl .= "://";
        $baseUrl .= $_SERVER["SERVER_NAME"];
        $baseUrl .= (empty($_SERVER["CONTEXT_PREFIX"])? "/" : $_SERVER["CONTEXT_PREFIX"]);
        return $baseUrl;
    }
    
    public function getResourceUrl ($resource)
    {
        return $this->getPublicBaseUrl() . $resource;
    }
    
    public function getUrl ($action="", $params=[])
    {
        $action = trim($action);
        if (StringUtils::startsWith($action, "/"))
            $action = substr($action, 1);
        $url = $this->getPublicBaseUrl();
        $url .= $action;
        if (sizeof($params) > 0)
            $url .= "?" . http_build_query($params);
        return $url;
    }
}