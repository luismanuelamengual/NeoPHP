<?php

namespace NeoPHP\Core;

use ErrorException;
use Exception;
use NeoPHP\Config\Properties;
use NeoPHP\Core\Controllers\Controllers;
use NeoPHP\Routing\RouteNotFoundException;

/**
 * Class Application
 * @package NeoPHP\Core
 */
abstract class Application {

    private static $basePath;
    private static $configPath;

    /**
     * @param $basePath
     * @throws Exception
     */
    public static function init($basePath) {

        //register paths
        self::$basePath = $basePath;
        self::$configPath = self::$basePath . DIRECTORY_SEPARATOR . "config";

        //register error handlers
        if (Properties::get("app.debug") === true) {
            $whoops = new \Whoops\Run;
            $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
            $whoops->register();
        }
        else {
            set_error_handler([__CLASS__, "handleError"], E_ALL | E_STRICT);
            set_exception_handler([__CLASS__, "handleException"]);
        }

        //execute boot actions
        $bootActions = Properties::get("app.bootActions", []);
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
     * Returns the configurations path
     */
    public static function getConfigPath() {
        return self::$configPath;
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
     * @param $errno
     * @param $errstr
     * @param $errfile
     * @param $errline
     * @param $errcontext
     * @throws ErrorException
     */
    public static function handleError($errno, $errstr, $errfile, $errline, $errcontext) {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    /**
     * @param $ex
     */
    public static function handleException($ex) {
        if (php_sapi_name() === 'cli') {
            echo "ERROR: " . $ex->getMessage();
        }
        else {
            if ($ex instanceof RouteNotFoundException) {
                http_response_code(404);
            }
            else {
                http_response_code(500);
            }
            echo "ERROR: " . $ex->getMessage();
            echo "<pre>";
            echo "## " . $ex->getFile() . "(" . $ex->getLine() . ")<br>";
            echo print_r($ex->getTraceAsString(), true);
            echo "</pre>";
        }
    }
}