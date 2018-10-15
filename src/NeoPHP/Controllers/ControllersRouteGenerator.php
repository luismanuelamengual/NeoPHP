<?php

namespace NeoPHP\Controllers;

use NeoPHP\Routing\Route;
use NeoPHP\Routing\RouteGenerator;

/**
 * Class ControllersRouteActionGenerator
 * @package NeoPHP\Routing
 */
class ControllersRouteGenerator implements RouteGenerator {

    private $namespace;

    /**
     * ControllersRouteActionGenerator constructor.
     * @param $namespace
     */
    public function __construct($namespace) {
        $this->namespace = $namespace;
    }

    /**
     * Generates a route for accessing controllers
     * @param string $method
     * @param array $path
     * @return mixed|null|string
     */
    public function generateRoute($method, array $path) : ?Route {
        $pathPartsSize = sizeof($path);

        //Obtención del nombre de la clase de controlador
        $controllerClassName = $this->namespace;
        if ($pathPartsSize > 1) {
            for ($i = 0; $i < $pathPartsSize - 1; $i++) {
                if (!empty($controllerClassName)) {
                    $controllerClassName .= '\\';
                }
                $requestPathPart = $path[$i];
                $requestPathPart = str_replace(' ', '', ucwords(str_replace('_', ' ', $requestPathPart)));
                $controllerClassName .= $requestPathPart;
            }
        }
        else {
            if (!empty($controllerClassName)) {
                $controllerClassName .= '\\';
            }
            $controllerClassName .= 'Main';
        }
        $controllerClassName .= get_property('routes.controllers_suffix', 'Controller');

        $route = null;
        if (class_exists($controllerClassName)) {
            //Obtención del nombre de la metodo del controlador
            $controllerAction = (empty($path) || empty($path[$pathPartsSize - 1])) ? 'index' : $path[$pathPartsSize - 1];
            $controllerAction = str_replace(' ', '', ucwords(str_replace('_', ' ', $controllerAction)));
            $controllerAction .= get_property('routes.actions_suffix', 'Action');
            $controllerAction[0] = strtolower($controllerAction[0]);

            //Obtención del nombre de la acción
            $action = $controllerClassName . '@' . $controllerAction;

            $route = new Route($action);
        }
        return $route;
    }
}
