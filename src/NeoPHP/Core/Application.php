<?php

namespace NeoPHP\Core;

use Exception;
use NeoPHP\Core\Controllers\Controllers;

/**
 * Class Application
 * @package NeoPHP\Core
 */
class Application {

    private static $instance;

    private $basePath;
    private $facades = [];

    /**
     * @return Application
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new Application();
        }
        return self::$instance;
    }

    /**
     * Application constructor.
     */
    private function __construct() {
    }

    /**
     * @return mixed
     */
    public function getBasePath() {
        return $this->basePath;
    }

    /**
     * @param mixed $basePath
     */
    public function setBasePath($basePath) {
        $this->basePath = $basePath;
    }

    /**
     * @throws Exception
     */
    public function init() {

        //register error handlers
        if (config("app.debug") === true) {
            $whoops = new \Whoops\Run;
            $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
            $whoops->register();
        }
        else {
            set_error_handler("handleError", E_ALL | E_STRICT);
            set_exception_handler("handleException");
        }

        //execute boot actions
        $bootActions = config("app.bootActions", []);
        foreach ($bootActions as $bootAction) {
            $this->execute($bootAction);
        }
    }

    /**
     * @param $class
     * @param $implementation
     */
    public function registerFacadeImpl ($class, $implementation) {
        $this->facades[$class] = $implementation;
    }

    /**
     * @param $class
     * @return mixed
     */
    public function getFacadeImpl ($class) {
        return isset($this->facades[$class])? $this->facades[$class] : null;
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
        $controller = Controllers::getController($controllerClass);
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