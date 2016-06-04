<?php

namespace NeoPHP\app;

use ErrorException;
use NeoPHP\util\logging\handler\FileHandler;
use NeoPHP\util\logging\Logger;
use NeoPHP\util\translation\Translator;

abstract class Application extends ApplicationContext
{
    private $storagePath;
    
    public function __construct ($basePath)
    {
        parent::__construct($basePath);
        $this->storagePath = (isset($this->getProperties()->storagePath))? $this->getProperties()->storagePath : $this->basePath . DIRECTORY_SEPARATOR . "storage";    
        
        //Instalar el handler de errores de la aplicación
        set_error_handler(function($errno, $errstr, $errfile, $errline) { throw new ErrorException($errstr, $errno, 0, $errfile, $errline); }, E_ALL);
    }
    
    /**
     * Obtiene el path para guardado de información adicional de la aplicación
     */
    public function getStoragePath ()
    {
        return $this->storagePath;
    }
    
    /**
     * Obtiene el logger de la aplicación
     * @return Logger Logger de la aplicación
     */
    public function getLogger ()
    {
        if (!isset($this->logger))
        {
            $this->logger = new Logger();
            $this->logger->addHandler(new FileHandler($this->getStoragePath() . DIRECTORY_SEPARATOR . "logs" . DIRECTORY_SEPARATOR . "{Y}-{m}-{d}.txt"));
        }
        return $this->logger;
    }
    
    /**
     * Obtiene el traductor de la aplicación
     * @return Translator Traductor de la aplicación
     */
    public function getTranslator ()
    {
        if (!isset($this->translator))
        {
            $this->translator = new Translator();
            foreach ($this->getResourcePaths() as $resourcePath)
                $this->translator->addResourcePath($resourcePath . DIRECTORY_SEPARATOR . "lang");
            
            $translator = $this->translator;
            $this->addListener("libraryAdded", function (Library $library) use ($translator) 
            {
                foreach ($library->getResourcePaths() as $resourcePath)
                    $translator->addResourcePath($resourcePath . DIRECTORY_SEPARATOR . "lang");
            });
        }
        return $this->translator;
    }
}