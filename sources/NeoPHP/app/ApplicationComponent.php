<?php

namespace NeoPHP\app;

use NeoPHP\core\Object;

abstract class ApplicationComponent extends Object
{
    protected $application;
    protected $context;
    
    public function __construct (Application $application)
    {
        $this->application = $application;
    }
    
    /**
     * Obtiene el contexto al cual pertenece el componente
     * @return ApplicationContext Contexto al que pertenece el componente
     */
    protected function getContext ()
    {
        if (empty($this->context))
            $this->context = $this->findContext();
        return $this->context;
    }
    
    /**
     * Encuentra el contexto al cual pertenece el componente
     * @return ApplicationContext Contexto al que pertenece el componente
     */
    private function findContext (ApplicationContext $context=null, $sourcesPath=null)
    {
        if ($context == null)
            $context = $this->application;
        
        if ($sourcesPath == null)   
        {
            $sourcesPath = $this->getClass()->getFileName();
            $className = $this->getClass()->getName();
            for ($i = 0; $i <= substr_count($className, "\\"); $i++)
                $sourcesPath = dirname($sourcesPath);
        }
        
        $foundContext = null;
        if ($context->getSourcesPath() == $sourcesPath)
        {
            $foundContext = $context;
        }
        else
        {
            foreach ($context->getLibraries() as $library)
            {
                $foundContext = $this->findContext($library, $sourcesPath);
                if ($foundContext != null)
                    break;
            }
        }
        return $foundContext;
    }
}