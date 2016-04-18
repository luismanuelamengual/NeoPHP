<?php

namespace NeoPHP\web;

use NeoPHP\mvc\Controller;
use NeoPHP\util\TraitUtils;
use NeoPHP\web\http\Request;
use NeoPHP\web\http\Session;

abstract class WebController extends Controller
{
    protected final function getBaseUrl ()
    {
        return $this->getApplication()->getBaseUrl();
    }
    
    protected final function getUrl ($action="", $params=array())
    {
        return $this->getApplication()->getUrl($action, $params);
    }
    
    /**
     * Obtiene la petición web efectuada
     * @return Request Petición web
     */
    protected final function getRequest ()
    {
        return Request::getInstance();
    }
    
    /**
     * Obtiene la referencia a la sesion de la aplicación
     * @return Session sesion web
     */
    protected final function getSession ()
    {
        return Session::getInstance();
    }
    
    protected function onAfterActionExecution ($action, $parameters, $response)
    {
        if (!empty($response) && is_object($response) && TraitUtils::isUsingTrait($response, "NeoPHP\\web\\http\\ResponseTrait"))
        {
            $response->send();
        }
        else
        {
            parent::onAfterActionExecution($action, $parameters, $response);
        }
    }
}

?>