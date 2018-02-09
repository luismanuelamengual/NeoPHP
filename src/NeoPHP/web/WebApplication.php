<?php

namespace NeoPHP\web;

use NeoPHP\mvc\MVCApplication;
use NeoPHP\web\http\Response;
use Throwable;

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
class WebApplication extends MVCApplication {

    private $publicPath;

    /**
     * WebApplication constructor.
     */
    public function __construct($basePath) {
        parent::__construct($basePath);
        $this->publicPath = $basePath . DIRECTORY_SEPARATOR . "public";
    }

    /**
     * Metodo que se encarga de procesar una peticion HTTP
     */
    public function handleRequest() {
        try {
            $action = null;
            if (!empty($_SERVER["REDIRECT_URL"])) {
                $action = $_SERVER["REDIRECT_URL"];
                if (!empty($_SERVER["CONTEXT_PREFIX"]))
                    $action = substr($action, strlen($_SERVER["CONTEXT_PREFIX"]));
            }
            else {
                $action = "";
            }

            //handle action
        }
        catch (Throwable $ex) {

            $response = new Response();
            $response->setStatusCode(500);
            $responseContent = "";
            $responseContent .= "ERROR: " . $ex->getMessage();
            $responseContent .= "<pre>";
            $responseContent .= print_r ($ex->getTraceAsString(), true);
            $responseContent .= "</pre>";
            $response->setContent($responseContent);
            $response->send();
        }
    }

    /**
     * @return string
     */
    public function getPublicPath() {
        return $this->publicPath;
    }

    /**
     * @return string
     */
    public function getPublicBaseUrl() {
        $baseUrl = isset($_SERVER["REQUEST_SCHEME"]) ? $_SERVER["REQUEST_SCHEME"] : "http";
        $baseUrl .= "://";
        $baseUrl .= $_SERVER["SERVER_NAME"];
        $baseUrl .= (empty($_SERVER["CONTEXT_PREFIX"]) ? "/" : $_SERVER["CONTEXT_PREFIX"]);
        return $baseUrl;
    }

    /**
     * @param $resource
     * @return string
     */
    public function getResourceUrl($resource) {
        return $this->getPublicBaseUrl() . $resource;
    }
}