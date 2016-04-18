<?php

namespace NeoPHP\app;

use NeoPHP\core\ClassLoader;
use NeoPHP\core\Object;
use NeoPHP\util\properties\PropertiesManager;
use NeoPHP\util\eventhandling\EventDispatcherTrait;

abstract class ApplicationContext extends Object
{
    const PROPERTIES_FILENAME = "properties.json";
    
    use EventDispatcherTrait;
    
    protected $basePath;
    protected $sourcesPath;
    protected $resourcesPath;
    protected $libraries = [];
    
    public function __construct ($basePath)
    {
        $this->basePath = $basePath;
        $this->sourcesPath = (isset($this->getProperties()->sourcesPath))? $this->getProperties()->sourcesPath : $this->basePath . DIRECTORY_SEPARATOR . "sources";    
        $this->resourcesPath = (isset($this->getProperties()->resourcesPath))? $this->getProperties()->resourcesPath : $this->basePath . DIRECTORY_SEPARATOR . "resources";
        
        //Agregar las librerias dependientes de este contexto
        if (isset($this->getProperties()->libraries))
            foreach ($this->getProperties()->libraries as $libraryPath)
                $this->addLibrary(new Library($libraryPath));
        
        //Registrar el path de fuentes del contexto
        ClassLoader::getInstance()->addIncludePath($this->sourcesPath);
    }
    
    /**
     * Obtiene el manager de propiedades
     * @return PropertiesManager Manager de propiedades
     */
    public function getProperties ()
    {
        if (!isset($this->properties))
        {
            $this->properties = new PropertiesManager();
            $this->properties->addPropertiesFile($this->basePath . DIRECTORY_SEPARATOR . self::PROPERTIES_FILENAME);
        }
        return $this->properties;
    }
    
    /**
     * Obtiene el path principal de la aplicación
     */
    public function getBasePath ()
    {
        return $this->basePath;
    }
    
    /**
     * Obtiene el path para guardado de códigos fuente de la aplicación
     */
    public function getSourcesPath ()
    {
        return $this->sourcesPath;
    }
    
    /**
     * Obtiene los paths de recursos de la aplicación
     */
    public function getResourcePaths ()
    {
        $resourcePaths = [];
        $resourcePaths[] = $this->resourcesPath;
        foreach ($this->libraries as $library)
            $resourcePaths = array_merge($resourcePaths, $library->getResourcePaths());
        return $resourcePaths;
    }
    
    /**
     * Agrega una nueva libreria al contexto de aplicación
     * @param Library $library
     */
    public function addLibrary (Library $library)
    {
        $this->libraries[] = $library;
        $this->fireEvent("libraryAdded", [$library]);
    }
    
    /**
     * Obtiene las librerias registradas para el contexto
     * @return array librerias registradas para el contexto de aplicación
     */
    public function getLibraries ()
    {
        return $this->libraries;
    }
}