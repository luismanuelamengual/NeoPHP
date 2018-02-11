<?php

namespace NeoPHP\Core\Controllers;

use Exception;

/**
 * Class Controllers
 * @package NeoPHP\mvc\controllers
 */
abstract class Controllers {

    private static $controllers = [];

    /**
     * @param $controllerClass
     * @return mixed
     */
    public static function getController($controllerClass) {
        if (!isset(self::$controllers[$controllerClass])) {
            self::$controllers[$controllerClass] = new $controllerClass;
        }
        return self::$controllers[$controllerClass];
    }

    /**
     * @param $action
     * @param array $parameters
     * @return mixed|null
     * @throws Exception
     */
    public static function execute($action, array $parameters) {
        $result = null;
        $actionParts = explode("@", $action);
        $controllerClass = $actionParts[0];
        $controllerMethodName = sizeof($actionParts) > 1 ? $actionParts[1] : "index";
        $controller = self::getController($controllerClass);
        if (method_exists($controller, $controllerMethodName)) {
            $controllerMethodParams = [];
            $controllerMethod = new \ReflectionMethod($controller, $controllerMethodName);
            foreach ($controllerMethod->getParameters() as $parameter) {
                $parameterName = $parameter->getName();
                $parameterValue = null;
                if (array_key_exists($parameterName, $parameters)) {
                    $parameterValue = $parameters[$parameterName];
                }
                else if ($parameter->isOptional()) {
                    $parameterValue = $parameter->getDefaultValue();
                }
                $controllerMethodParams[] = $parameterValue;
            }
            $result = call_user_func_array([$controller, $controllerMethodName], $controllerMethodParams);
        }
        else {
            throw new Exception ("Method \"$controllerMethodName\" not found in controller \"$controllerClass\" !!");
        }
        return $result;
    }
}