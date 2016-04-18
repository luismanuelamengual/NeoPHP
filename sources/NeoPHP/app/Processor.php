<?php

namespace NeoPHP\app;

abstract class Processor
{
    /**
     * Obtiene la aplicación asociada
     * @return ProcessorApplication
     */
    protected function getApplication()
    {
        return ProcessorApplication::getInstance();
    }
    
    /**
     * Obtiene el despachador de eventos asociado a la aplicación
     * @return EventDispatcher
     */
    protected function getEventDispatcher()
    {
        return $this->getApplication()->getEventDispatcher();
    }
    
    protected function getLogger()
    {
        return $this->getApplication()->getLogger();
    }
    
    public abstract function start ();
    public abstract function stop ();
}

?>
