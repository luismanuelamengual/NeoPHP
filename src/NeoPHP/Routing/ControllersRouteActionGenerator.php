<?php

namespace NeoPHP\Routing;

/**
 * Class ControllersRouteActionGenerator
 * @package NeoPHP\Routing
 */
class ControllersRouteActionGenerator implements RouteActionGenerator {

    private $namespace;

    /**
     * ControllersRouteActionGenerator constructor.
     * @param $namespace
     */
    public function __construct($namespace) {
        $this->namespace = $namespace;
    }

    /**
     * @param array $method
     * @param array $path
     * @return mixed|null|string
     */
    public function generateAction($method, array $path) {
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

        $action = null;
        if (class_exists($controllerClassName)) {
            //Obtención del nombre de la metodo del controlador
            $controllerAction = (empty($path) || empty($path[$pathPartsSize - 1])) ? 'index' : $path[$pathPartsSize - 1];
            $controllerAction = str_replace(' ', '', ucwords(str_replace('_', ' ', $controllerAction)));
            $controllerAction .= get_property('routes.actions_suffix', 'Action');
            $controllerAction[0] = strtolower($controllerAction[0]);

            //Obtención del nombre de la acción
            $action = $controllerClassName . '@' . $controllerAction;
        }
        return $action;
    }
}
