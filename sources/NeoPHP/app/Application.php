<?php

namespace NeoPHP\app;

use ErrorException;
use NeoPHP\sql\Connection;
use NeoPHP\util\logging\handler\FileHandler;
use NeoPHP\util\logging\Logger;
use NeoPHP\util\memory\MemCache;
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
    
    /**
     * Obtiene una nueva conexión de base de datos en funcion del nombre especificado
     * @param string $connectionName Nombre de la conexión que se desea obtener
     * @return Connection conexión de base de datos
     */
    public function getConnection ($connectionName="main")
    {
        if (!isset($this->connections)) 
            $this->connections = [];
        if (!isset($this->connections[$connectionName]))
        {
            if (!isset($this->getProperties()->connections))
                throw new Exception ("Connections property not found !!");
            
            $connectionProperty = null; 
            if (is_object($this->getProperties()->connections))
            {
                $connectionProperty = $this->getProperties()->connections[$connectionName];
            }
            else
            {
                foreach ($this->getProperties()->connections as $testConnectionProperty)
                {
                    if ($testConnectionProperty->name = $connectionName)
                    {
                        $connectionProperty = $testConnectionProperty;
                        break;
                    }
                }
            }
            if (!isset($connectionProperty))
                throw new Exception ("Connection \"$connectionName\" not found !!");

            $connection = new Connection();
            $connection->setLogger($this->getLogger());
            $connection->setDriver($connectionProperty->driver);
            $connection->setDatabase($connectionProperty->database);
            $connection->setHost(isset($connectionProperty->host)? $connectionProperty->host : "localhost");
            $connection->setPort(isset($connectionProperty->port)? $connectionProperty->port : "");
            $connection->setUsername(isset($connectionProperty->username)? $connectionProperty->username : "");
            $connection->setPassword(isset($connectionProperty->password)? $connectionProperty->password : "");
            $this->connections[$connectionName] = $connection;
        }
        return $this->connections[$connectionName];
    }
    
    /**
     * Obtiene el manejador de cache asociado a la aplicación
     * @return MemCache Manejador de cache
     */
    public function getCacheManager ()
    {
        if (!isset($this->cacheManager))
        {
            $host = MemCache::DEFAULT_HOST;
            $port = MemCache::DEFAULT_PORT;
            if (isset($this->getProperties()->cacheServer))
            {
                if (isset($this->getProperties()->cacheServer->host))
                    $host = $this->getProperties()->cacheServer->host;
                if (isset($this->getProperties()->cacheServer->port))
                    $port = $this->getProperties()->cacheServer->port;
            }
            $this->cacheManager = new MemCache($host, $port);
        }
        return $this->cacheManager;
    }
}