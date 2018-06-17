<?php

namespace NeoPHP;

use Exception;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use NeoPHP\Console\Commands;
use NeoPHP\Controllers\Controllers;
use NeoPHP\Routing\Routes;

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
    private $localConfigPath;

    private $modules = [];

    /**
     * @param $basePath
     * @return Application
     */
    public static function create($basePath): Application {
        self::$instance = new Application($basePath);
        return self::$instance;
    }

    /**
     * @return Application
     */
    public static function get(): Application {
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
     * @param null $localConfigPath
     * @return null|string
     */
    public function localConfigPath($localConfigPath = null) {
        if ($localConfigPath != null) {
            $this->localConfigPath = $localConfigPath;
        }
        else {
            if (!isset($this->localConfigPath)) {
                $this->localConfigPath = $this->basePath() . DIRECTORY_SEPARATOR . "config.local";
            }
        }
        return $this->localConfigPath;
    }

    /**
     * Agrega un nuevo modulo a la aplicación
     * @param Module $module
     */
    public function addModule (Module $module) {
        $this->modules[] = $module;
    }

    /**
     * Inicializa la aplicación
     * @throws Exception
     */
    public function start() {
        foreach ($this->modules as $module) {
            $module->start();
        }

        if (php_sapi_name() == "cli") {
            Commands::handleCommand();
        }
        else {
            Routes::handleRequest();
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
        if (is_string($action)) {
            $actionParts = explode("@", $action);
            $controllerClass = $actionParts[0];
            $controllerMethodName = sizeof($actionParts) > 1 ? $actionParts[1] : "index";
            $controller = Controllers::get($controllerClass);
            if ($controller == null) {
                throw new ActionNotFoundException ( "Controller \"$controllerClass\" not found !!");
            }
            if (method_exists($controller, $controllerMethodName)) {
                $controllerMethodParams = $this->getParameterValues(new ReflectionMethod($controller, $controllerMethodName), $parameters);
                $result = call_user_func_array([$controller, $controllerMethodName], $controllerMethodParams);
            }
            else {
                throw new ActionNotFoundException ("Method \"$controllerMethodName\" not found in controller \"$controllerClass\" !!");
            }
        }
        else if (is_callable($action)) {
            $actionParams = $this->getParameterValues(new ReflectionFunction($action), $parameters);
            $result = call_user_func_array($action, $actionParams);
        }
        return $result;
    }

    /**
     * @param ReflectionMethod|ReflectionFunction $function
     * @param array $parameters
     * @return array
     * @throws \ReflectionException
     */
    private function getParameterValues ($function, array $parameters = []) {
        $functionParams = [];
        $parameterIndex = 0;
        foreach ($function->getParameters() as $parameter) {
            $parameterName = $parameter->getName();
            $parameterValue = null;

            if (array_key_exists($parameterName, $parameters)) {
                $parameterValue = $parameters[$parameterName];
            }
            else if ($parameter->hasType()) {
                $type = $parameter->getType();
                if (!$type->isBuiltin()) {
                    $typeName = (string)$type;
                    if (array_key_exists($typeName, $parameters)) {
                        $parameterValue = $parameters[$typeName];
                    }
                    else if (!$parameter->isDefaultValueAvailable()) {
                        $typeClass = new ReflectionClass($typeName);
                        foreach ($typeClass->getMethods(ReflectionMethod::IS_STATIC) as $staticMethod) {
                            if ($staticMethod->getReturnType() != null && ((string)$staticMethod->getReturnType() == $typeName) && $staticMethod->getNumberOfParameters() == 0) {
                                $parameterValue = $staticMethod->invoke(null);
                                break;
                            }
                        }
                    }
                }
            }

            if ($parameterValue == null) {
                if (array_key_exists($parameterIndex, $parameters)) {
                    $parameterValue = $parameters[$parameterIndex];
                    $parameterIndex++;
                }
                else if ($parameter->isDefaultValueAvailable()) {
                    $parameterValue = $parameter->getDefaultValue();
                }
            }
            $functionParams[] = $parameterValue;
        }
        return $functionParams;
    }
}