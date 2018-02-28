<?php

namespace NeoPHP\Core;

use Exception;
use NeoPHP\Controllers\Controllers;

/**
 * Class Application
 * @package NeoPHP\Core
 */
class Application {

    private static $instance;

    private $basePath;
    private $storagePath;
    private $resourcesPath;
    private $configPath;

    /**
     * @param $basePath
     * @return Application
     */
    public static function create($basePath) {
        self::$instance = new Application($basePath);
        return self::$instance;
    }

    /**
     * @return Application
     */
    public static function get() {
        return self::$instance;
    }

    /**
     * Application constructor.
     * @param $basePath
     */
    private function __construct($basePath) {
        $this->basePath = $basePath;
        set_error_handler("handle_error", E_ALL | E_STRICT);
        set_exception_handler("handle_exception");
    }

    /**
     * @param null $basePath
     * @return mixed
     */
    public function basePath($basePath = null) {
        if ($basePath != null) {
            $this->basePath = $basePath;
        }
        return $this->basePath;
    }

    /**
     * Returns the storage path
     * @param null $storagePath
     * @return mixed|null
     */
    public function storagePath($storagePath = null) {
        if ($storagePath != null) {
            $this->storagePath = $storagePath;
        }
        else if (!isset($this->storagePath)) {
            $this->storagePath = get_property("app.storagePath", $this->basePath . DIRECTORY_SEPARATOR . "storage");
        }
        return $this->storagePath;
    }

    /**
     * Returns the resources path
     * @param null $resourcesPath
     * @return mixed|null
     */
    public function resourcesPath($resourcesPath = null) {
        if ($resourcesPath != null) {
            $this->resourcesPath = $resourcesPath;
        }
        else if (!isset($this->resourcesPath)) {
            $this->resourcesPath = get_property("app.resourcesPath", $this->basePath . DIRECTORY_SEPARATOR . "resources");
        }
        return $this->resourcesPath;
    }

    /**
     * @param null $configPath
     * @return null|string
     */
    public function configPath($configPath = null) {
        if ($configPath != null) {
            $this->configPath = $configPath;
        }
        else {
            if (!isset($this->configPath)) {
                $this->configPath = $this->basePath() . DIRECTORY_SEPARATOR . "config";
            }
        }
        return $this->configPath;
    }

    /**
     * @throws Exception
     */
    public function init() {
        $bootActions = get_property("app.bootActions", []);
        foreach ($bootActions as $bootAction) {
            $this->execute($bootAction);
        }
        fire_event("application_init");
    }

    /**
     * @param $action
     * @param array $parameters
     * @return mixed|null
     * @throws Exception
     */
    public function execute($action, array $parameters = []) {
        $result = null;
        $actionParts = explode("@", $action);
        $controllerClass = $actionParts[0];
        $controllerMethodName = sizeof($actionParts) > 1 ? $actionParts[1] : "index";
        $controller = Controllers::get($controllerClass);
        if (method_exists($controller, $controllerMethodName)) {
            $controllerMethodParams = [];
            $controllerMethod = new \ReflectionMethod($controller, $controllerMethodName);
            $parameterIndex = 0;
            foreach ($controllerMethod->getParameters() as $parameter) {
                $parameterName = $parameter->getName();
                $parameterValue = null;
                if (array_key_exists($parameterName, $parameters)) {
                    $parameterValue = $parameters[$parameterName];
                }
                else if (array_key_exists($parameterIndex, $parameters)) {
                    $parameterValue = $parameters[$parameterIndex];
                }
                else if ($parameter->isOptional()) {
                    $parameterValue = $parameter->getDefaultValue();
                }
                $controllerMethodParams[] = $parameterValue;
                $parameterIndex++;
            }
            $result = call_user_func_array([$controller, $controllerMethodName], $controllerMethodParams);
        }
        else {
            throw new Exception ("Method \"$controllerMethodName\" not found in controller \"$controllerClass\" !!");
        }
        return $result;
    }
}