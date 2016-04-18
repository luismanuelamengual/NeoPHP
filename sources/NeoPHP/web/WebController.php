<?php

namespace NeoPHP\web;

use NeoPHP\mvc\Controller;
use NeoPHP\util\TraitUtils;
use NeoPHP\web\http\Request;
use NeoPHP\web\http\Session;

abstract class WebController extends Controller
{
    public function __construct (WebApplication $application)
    {
        parent::__construct($application);
    }
    
    protected final function getUrl ($action="", $params=[])
    {
        return $this->application->getUrl($action, $params);
    }
    
    /**
     * Obtiene la petición web efectuada
     * @return Request Objeto de petición web
     */
    protected final function getRequest ()
    {
        return $this->application->getRequest();
    }
    
    /**
     * Obtiene la sesión http 
     * @return Session Sesión http
     */
    protected final function getSession ()
    {
        return $this->application->getSession();
    }
    
    protected function onAfterAction ($action, $parameters, $response)
    {   
        if (!empty($response) && is_object($response) && TraitUtils::isUsingTrait($response, "NeoPHP\\web\\http\\ResponseTrait"))
        {
            $response->send();
        }
        else
        {
            parent::onAfterAction($action, $parameters, $response);
        }
    }
}