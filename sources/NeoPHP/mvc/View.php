<?php

namespace NeoPHP\mvc;

use NeoPHP\core\Object;

abstract class View extends Object
{ 
    /**
     * Obtiene la aplicación MVC asociada a la vista
     * @return MVCApplication aplicación mvc
     */
    protected final static function getApplication ()
    {
        return MVCApplication::getInstance();
    }
    
    protected final function getProperties ()
    {
        return $this->getApplication()->getProperties();
    }
    
    protected final function getLogger ()
    {
        return $this->getApplication()->getLogger();
    }
    
    protected final function getTranslator ()
    {
        return $this->getApplication()->getTranslator();
    }
    
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

?>