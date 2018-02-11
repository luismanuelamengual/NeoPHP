<?php

namespace NeoPHP\Core\Controllers;
use Exception;

/**
 * Class Controllers
 * @package NeoPHP\mvc\controllers
 */
abstract class Controllers {

    /**
     * @param $action
     * @param array $parameters
     * @throws Exception
     */
    public static function execute($action, array $parameters) {
        $actionParts = explode("@", $action);
        $controllerClass = $actionParts[0];
        $controllerMethod = sizeof($actionParts) > 1? $actionParts[1] : "index";
        $controller = new $controllerClass;
        if (method_exists($controller, $controllerMethod)) {
            call_user_func(array($controller, $controllerMethod));
        }
        else {
            throw new Exception ("Method \"$controllerMethod\" not found in controller \"$controllerClass\" !!");
        }
    }
}