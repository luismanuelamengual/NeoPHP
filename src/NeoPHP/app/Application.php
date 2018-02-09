<?php

namespace NeoPHP\app;

use NeoPHP\core\ClassLoader;
use NeoPHP\sql\Connection;
use NeoPHP\util\logging\handler\FileHandler;
use NeoPHP\util\logging\Logger;
use NeoPHP\util\Messages;
use NeoPHP\util\Properties;

/**
 * Class Application - Main application class
 * @package NeoPHP\app
 * @author Luis Manuel Amengual <luismanuelamengual@gmail.com>
 */
abstract class Application {

    protected $basePath;
    protected $configPath;
    protected $sourcesPath;
    protected $resourcesPath;
    protected $storagePath;

    /**
     * Application constructor.
     * @param $basePath string path for the application
     */
    public function __construct($basePath) {

        //Register application paths
        $this->basePath = $basePath;
        $this->configPath = $this->basePath . DIRECTORY_SEPARATOR . "config";
        $this->sourcesPath = $this->basePath . DIRECTORY_SEPARATOR . "src";
        $this->resourcesPath = $this->basePath . DIRECTORY_SEPARATOR . "resources";
        $this->storagePath = $this->basePath . DIRECTORY_SEPARATOR . "storage";

        //Register sources class path
        ClassLoader::getInstance()->addIncludePath($this->sourcesPath);
    }

    /**
     * Returns the base path of the application
     */
    public function getBasePath() {
        return $this->basePath;
    }

    /**
     * Returns the sources path
     */
    public function getSourcesPath() {
        return $this->sourcesPath;
    }

    /**
     * Returns the resources path
     */
    public function getResourcesPath() {
        return $this->resourcesPath;
    }

    /**
     * @return string path para guardado de información adicional de la aplicación
     */
    public function getStoragePath() {
        return $this->storagePath;
    }

    /**
     * @return string path de configuración de la aplicación
     */
    public function getConfigPath() {
        return $this->configPath;
    }

    /**
     * @return Properties get the application properties
     */
    public function getProperties(): Properties {
        if (!isset($this->properties)) {
            $this->properties = $this->createProperties();
        }
        return $this->properties;
    }

    /**
     * Returns the application logger
     * @return Logger Logger de la aplicación
     */
    public function getLogger(): Logger {
        if (!isset($this->logger)) {
            $this->logger = $this->createLogger();
        }
        return $this->logger;
    }

    /**
     * Returns the application messages manager
     * @return Messages manager
     */
    public function getMessages(): Messages {
        if (!isset($this->messages)) {
            $this->messages = $this->createMessages();
        }
        return $this->messages;
    }

    /**
     * @param string $dataSourceName name of the data source
     * @return Connection new connection
     */
    public function getConnection($dataSourceName=""): Connection {
        //Not implemented yet
    }

    /**
     * @return Properties new properties created
     */
    protected function createProperties(): Properties {
        $properties = new Properties();
        $properties->addPropertiesFile($this->configPath . DIRECTORY_SEPARATOR . "app.php");
        return $properties;
    }

    /**
     * @return Logger new logger created
     */
    protected function createLogger(): Logger {
        $logger = new Logger();
        $logger->addHandler(new FileHandler($this->storagePath . DIRECTORY_SEPARATOR . "logs" . DIRECTORY_SEPARATOR . "{Y}-{m}-{d}.txt"));
        return $logger;
    }

    /**
     * @return Messages new messages instances for the application
     */
    protected function createMessages(): Messages {
        $messages = new Messages();
        $messages->addResourcePath($this->resourcesPath . DIRECTORY_SEPARATOR . "messages");
        return $messages;
    }
}