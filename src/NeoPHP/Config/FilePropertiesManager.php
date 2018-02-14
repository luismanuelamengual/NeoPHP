<?php

namespace NeoPHP\Config;

/**
 * Class FilePropertiesManager
 * @package NeoPHP\Config
 */
class FilePropertiesManager implements PropertiesManager {

    private $repositoryPath;
    private $properties = [];

    /**
     * FilePropertiesManager constructor.
     * @param string $repositoryPath
     */
    public function __construct(string $repositoryPath) {
        $this->repositoryPath = $repositoryPath;
    }

    /**
     * @return string
     */
    public function getRepositoryPath(): string {
        return $this->repositoryPath;
    }

    /**
     * @param $key
     * @param null $defaultValue
     * @return array|mixed|null
     */
    public function get($key, $defaultValue = null) {
        $keyTokens = explode(".", $key);
        if (!isset($this->properties[$keyTokens[0]])) {
            $this->loadPropertiesModule($keyTokens[0]);
        }
        $propertyValue = $this->properties;
        foreach ($keyTokens as $keyToken) {
            if (isset($propertyValue[$keyToken])) {
                $propertyValue = $propertyValue[$keyToken];
            }
            else {
                $propertyValue = null;
                break;
            }
        }
        return $propertyValue == null? $defaultValue : $propertyValue;
    }

    /**
     * @param $key
     * @param $value
     */
    public function set($key, $value) {
        $keyTokens = explode(".", $key);
        $propertyKey = &$this->properties;
        foreach ($keyTokens as $keyToken) {
            if (!isset($propertyKey[$keyToken])) {
                $propertyKey[$keyToken] = [];
            }
            $propertyKey = &$propertyKey[$keyToken];
        }
        $propertyKey = $value;
    }

    /**
     * @param $moduleName
     */
    private function loadPropertiesModule($moduleName) {
        $moduleFileName = $this->repositoryPath . DIRECTORY_SEPARATOR . $moduleName . ".php";
        if (file_exists($moduleFileName)) {
            $this->properties[$moduleName] = @include_once($moduleFileName);
        }
    }
}