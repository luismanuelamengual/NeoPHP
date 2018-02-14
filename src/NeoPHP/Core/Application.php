<?php

namespace NeoPHP\Core;

use Exception;
use NeoPHP\Core\Controllers\Controllers;

/**
 * Class Application
 * @package NeoPHP\Core
 */
abstract class Application {

    private static $facades = [];
    private static $basePath;

    /**
     * @param $basePath
     * @throws Exception
     */
    public static function init($basePath) {

        //register paths
        self::$basePath = $basePath;

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
            self::execute($bootAction);
        }
    }

    /**
     * @return mixed
     */
    public static function getBasePath() {
        return self::$basePath;
    }

    /**
     * @param $action
     * @param array $parameters
     * @return mixed|null
     * @throws Exception
     */
    public static function execute($action, array $parameters = []) {
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

    /**
     * @param $class
     * @param $implementation
     */
    public static function registerFacadeImpl ($class, $implementation) {
        self::$facades[$class] = $implementation;
    }

    /**
     * @param $class
     * @return mixed
     */
    public static function getFacadeImpl ($class) {
        return isset(self::$facades[$class])? self::$facades[$class] : null;
    }
}