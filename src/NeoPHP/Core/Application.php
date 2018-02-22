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
        set_error_handler("handleError", E_ALL | E_STRICT);
        set_exception_handler("handleException");
    }

    /**
     * @return mixed
     */
    public function getBasePath() {
        return $this->basePath;
    }

    /**
     * Returns the storage path
     */
    public function getStoragePath() {
        if (!isset($this->storagePath)) {
            $this->storagePath = getProperty("app.storagePath", $this->basePath . DIRECTORY_SEPARATOR . "storage");
        }
        return $this->storagePath;
    }

    /**
     * Returns the resources path
     */
    public function getResourcesPath() {
        if (!isset($this->resourcesPath)) {
            $this->resourcesPath = getProperty("app.resourcesPath", $this->basePath . DIRECTORY_SEPARATOR . "resources");
        }
        return $this->resourcesPath;
    }

    /**
     * @throws Exception
     */
    public function init() {
        $bootActions = getProperty("app.bootActions", []);
        foreach ($bootActions as $bootAction) {
            $this->execute($bootAction);
        }
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