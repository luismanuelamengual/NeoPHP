<?php

namespace NeoPHP\app;

use ErrorException;
use NeoPHP\core\ClassLoader;
use NeoPHP\core\Object;
use NeoPHP\sql\Connection;
use NeoPHP\util\logging\handler\FileHandler;
use NeoPHP\util\logging\Logger;
use NeoPHP\util\properties\PropertiesManager;
use NeoPHP\util\translation\Translator;
use ReflectionClass;

abstract class Application extends Object
{
    const PROPERTIES_FILENAME = "app.properties";
    
    protected static $instances = array();
    protected $name;
    protected $basePath;
    
    public function __construct ($basePath)
    {
        $this->basePath = $basePath;
        
        //Instalar el handler de errores de la aplicación
        set_error_handler(array($this, "errorHandler"), E_ALL);
        
        //Regsitrar la clase para la obtención de la aplicación via "getInstance"
        $class = new ReflectionClass($this->getClassName());
        while ($class !== false)
        {
            $className = $class->getName();
            self::$instances[$className] = $this;
            $class = $class->getParentClass();
            if ($className == get_class())
                break;
        }
        
        //Registrar códigos fuentes de la aplicación
        ClassLoader::getInstance()->addIncludePath($this->getSourcesPath());
    }
    
    public static function getInstance()
    {
        return self::$instances[get_called_class()];
    }
    
    public function errorHandler ($errno, $errstr, $errfile, $errline)
    {
        throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
    }
    
    public function setName ($name)
    {
        $this->name = $name;
    }
    
    public function getName ()
    {
        return $this->name;
    }
    
    /**
     * Obtiene el path principal de la aplicación
     */
    public function getBasePath ()
    {
        return $this->basePath;
    }
    
    /**
     * Obtiene el path para guardado de información adicional de la aplicación
     */
    public function getStoragePath ()
    {
        return $this->getProperty("storagePath", $this->basePath . DIRECTORY_SEPARATOR . "storage");
    }
    
    /**
     * Obtiene el path para guardado de recursos de la aplicación
     */
    public function getResourcesPath ()
    {
        return $this->getProperty("resourcesPath", $this->basePath . DIRECTORY_SEPARATOR . "resources");
    }
    
    /**
     * Obtiene el path para guardado de códigos fuente de la aplicación
     */
    public function getSourcesPath ()
    {
        return $this->getProperty("sourcesPath", $this->basePath . DIRECTORY_SEPARATOR . "sources");
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
    
    public function getProperty ($propertyName, $defaultValue=null)
    {
        return !empty($this->getProperties()->$propertyName)? $this->getProperties()->$propertyName : $defaultValue;
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
            $this->translator->setResourcesPath($this->getResourcesPath() . DIRECTORY_SEPARATOR . "lang");
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
            $connectionPrefix = "connection_";
            if ($connectionName != "main") 
                $connectionPrefix .= $connectionName . "_";
            $connection = new Connection();
            $connection->setLogger($this->getLogger());
            $connection->setDriver($this->getProperty($connectionPrefix."driver"));
            $connection->setDatabase($this->getProperty($connectionPrefix."database"));
            $connection->setHost($this->getProperty($connectionPrefix."host", "localhost"));
            $connection->setPort($this->getProperty($connectionPrefix."port"));
            $connection->setUsername($this->getProperty($connectionPrefix."username"));
            $connection->setPassword($this->getProperty($connectionPrefix."password"));
            $this->connections[$connectionName] = $connection;
        }
        return $this->connections[$connectionName];
    }
}

?>