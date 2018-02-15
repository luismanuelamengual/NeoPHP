<?php

namespace NeoPHP\Core;

use Exception;

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
     * @param mixed $basePath
     */
    public function setBasePath($basePath) {
        $this->basePath = $basePath;
    }

    /**
     * @throws Exception
     */
    public function init() {
        $bootActions = config("app.bootActions", []);
        foreach ($bootActions as $bootAction) {
            $this->execute($bootAction);
        }
    }

    /**
     * @param $facadeName
     * @param $implementation
     */
    public function registerFacadeImpl ($facadeName, $implementation) {
        $this->facades[$facadeName] = $implementation;
    }

    /**
     * @param $facadeName
     * @return mixed
     */
    public function getFacadeImpl ($facadeName) {
        return isset($this->facades[$facadeName])? $this->facades[$facadeName] : null;
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
        $controller = controller($controllerClass);
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