<?php

namespace NeoPHP\mvc;

use NeoPHP\app\ApplicationComponent;
use NeoPHP\util\logging\Logger;
use NeoPHP\util\properties\PropertiesManager;
use NeoPHP\util\translation\Translator;
use NeoPHP\web\WebAssetsManager;

abstract class View extends ApplicationComponent
{
    public function __construct (MVCApplication $application)
    {
        parent::__construct($application);
    }
    
    /**
     * Obtiene el manager de propiedades de la aplicación
     * @return PropertiesManager Propiedades de la aplicación
     */
    protected final function getProperties ()
    {
        return $this->application->getProperties();
    }
    
    /**
     * Obtiene el logger de la aplicación
     * @return Logger Logger de la aplicación
     */
    protected final function getLogger ()
    {
        return $this->application->getLogger();
    }
    
    /**
     * Obtiene el traductor de la aplicación
     * @return Translator Traductor de la aplicación
     */
    protected final function getTranslator ()
    {
        return $this->application->getTranslator();
    }
    
    /**
     * Obtiene el manejador de assets de la aplicación
     * @return WebAssetsManager Manager de assets
     */
    protected final function getAssetsManager ()
    {
        return $this->application->getAssetsManager();
    }
    
    /**
     * Traduce un texto al lenguage dado
     * @param type $key
     * @param type $language
     * @return string texto traducido
     */
    protected final function getText ($key, $language=null)
    {
        return $this->getTranslator()->getText($key, $language);
    }
    
    public function __toString() 
    {
        return $this->render(true);
    }
    
    public final function render ($return = false)
    {
        if ($return == true)
        {
            ob_start();
            $this->onRender();
            return ob_get_clean();
        }
        else
        {
            $this->onRender();
        }
    }
    
    protected abstract function onRender();
}