<?php

namespace NeoPHP\Routing;

use NeoPHP\Http\Request;

/**
 * Class DefaultRoutesManager
 * @package NeoPHP\Routing
 */
class RoutesManager {

    const ROUTE_ACTION_KEY = '__routeAction';
    const ROUTE_AUTO_GENERATION_KEY = '__routeAutoGeneration';

    const ROUTE_GENERIC_PATH = '*';
    const ROUTE_PARAMETER_PREFIX = ':';
    const ROUTE_PARAMETER_WILDCARD = '%';
    const ROUTE_PATH_SEPARATOR = '/';
    const ROUTE_GENERIC_METHOD = 'ANY';

    private $routesIndex = [];

    /**
     * @param $method
     * @param $path
     * @param $action
     */
    public function registerRoute($method, $path, $action) {
        $path = trim($path, self::ROUTE_PATH_SEPARATOR);
        $pathParts = !empty($path)? explode(self::ROUTE_PATH_SEPARATOR, $path) : [];
        if (empty($method)) {
            $method = self::ROUTE_GENERIC_METHOD;
        }
        $routesIndex = &$this->routesIndex;
        foreach ($pathParts as $pathPart) {
            if ($pathPart[0] == self::ROUTE_PARAMETER_PREFIX) {
                $pathPart = self::ROUTE_PARAMETER_WILDCARD;
            }
            $routesIndex = &$routesIndex[$pathPart];
        }
        $routesIndex[self::ROUTE_ACTION_KEY][$method] = [$path, $action];
    }

    /**
     * @param $method
     * @param $path
     * @param $namespace
     */
    public function registerAutoGenerationRoute($method, $path, $namespace = null) {
        $path = trim($path, self::ROUTE_PATH_SEPARATOR);
        $pathParts = !empty($path)? explode(self::ROUTE_PATH_SEPARATOR, $path) : [];
        if (empty($method)) {
            $method = self::ROUTE_GENERIC_METHOD;
        }
        $routesIndex = &$this->routesIndex;
        foreach ($pathParts as $pathPart) {
            $routesIndex = &$routesIndex[$pathPart];
        }
        $methodAutoRouteIndex = &$routesIndex[self::ROUTE_AUTO_GENERATION_KEY][$method];
        if (empty($methodAutoRouteIndex)) {
            $methodAutoRouteIndex = [];
        }
        $methodAutoRouteIndex[] = [$path, $namespace];
    }

    /**
     * Obtiene la ruta más especifica
     * @return null|Route ruta
     */
    public function getRoute() {
        $route = null;
        $request = get_request();
        $requestMethod = $request->method();
        $requestPathParts = $request->pathParts();
        $routesIndex = $this->routesIndex;
        if (array_key_exists(self::ROUTE_GENERIC_PATH, $routesIndex)) {
            $route = $this->getRouteFromIndex($routesIndex[self::ROUTE_GENERIC_PATH], $requestMethod,$requestPathParts);
        }
        foreach ($requestPathParts as $requestPathPart) {
            if (empty($requestPathPart)) {
                continue;
            }

            if (array_key_exists($requestPathPart, $routesIndex)) {
                $routesIndex = &$routesIndex[$requestPathPart];
            }
            else if (array_key_exists(self::ROUTE_PARAMETER_WILDCARD, $routesIndex)) {
                $routesIndex = &$routesIndex[self::ROUTE_PARAMETER_WILDCARD];
            }
            else {
                $routesIndex = null;
                break;
            }

            if (array_key_exists(self::ROUTE_GENERIC_PATH, $routesIndex)) {
                $route = $this->getRouteFromIndex($routesIndex[self::ROUTE_GENERIC_PATH], $requestMethod,$requestPathParts);
            }
        }
        if ($routesIndex != null) {
            $route = $this->getRouteFromIndex($routesIndex, $requestMethod,$requestPathParts);
        }
        return $route;
    }

    /**
     * Obtiene una ruta desde un indice de rutas
     * @param $routesIndex
     * @param $requestMethod
     * @param $requestPathParts
     * @return null|Route
     */
    private function getRouteFromIndex (&$routesIndex, $requestMethod, $requestPathParts) {
        $route = null;
        if (array_key_exists(self::ROUTE_ACTION_KEY, $routesIndex)) {
            $testRoutes = $routesIndex[self::ROUTE_ACTION_KEY];
            if (array_key_exists($requestMethod, $testRoutes)) {
                $route = $this->createRoute($requestPathParts, $testRoutes[$requestMethod]);
            }
            else if (array_key_exists(self::ROUTE_GENERIC_METHOD, $testRoutes)) {
                $route = $this->createRoute($requestPathParts, $testRoutes[self::ROUTE_GENERIC_METHOD]);
            }
        }
        else if (array_key_exists(self::ROUTE_AUTO_GENERATION_KEY, $routesIndex)) {
            $testAutoRoutes = $routesIndex[self::ROUTE_AUTO_GENERATION_KEY];
            if (array_key_exists($requestMethod, $testAutoRoutes)) {
                $route = $this->createAutoGenerationRoute($requestPathParts, $testAutoRoutes[$requestMethod]);
            }
            else if (array_key_exists(self::ROUTE_GENERIC_METHOD, $testAutoRoutes)) {
                $route = $this->createAutoGenerationRoute($requestPathParts, $testAutoRoutes[self::ROUTE_GENERIC_METHOD]);
            }
        }
        else if (array_key_exists(self::ROUTE_GENERIC_PATH, $routesIndex)) {
            $route = $this->getRouteFromIndex($routesIndex[self::ROUTE_GENERIC_PATH], $requestMethod, $requestPathParts);
        }
        return $route;
    }

    /**
     * @param $requestPathParts
     * @param $routeArray
     * @return Route ruta creada
     */
    private function createRoute ($requestPathParts, $routeArray) : Route {
        $routePath = $routeArray[0];
        $routeAction = $routeArray[1];
        $routeParameters = [];
        if (strpos($routePath, ':')) {
            $routePathParts = explode(self::ROUTE_PATH_SEPARATOR, trim($routePath, self::ROUTE_PATH_SEPARATOR));;
            for ($i = 0; $i < sizeof($routePathParts); $i++) {
                $routePathPart = $routePathParts[$i];
                if ($routePathPart[0] == self::ROUTE_PARAMETER_PREFIX) {
                    $parameterName = substr($routePathPart, 1);
                    $parameterValue = $requestPathParts[$i];
                    $routeParameters[$parameterName] = $parameterValue;
                }
            }
        }
        return new Route($routeAction, $routeParameters);
    }

    /**
     * @param $requestPathParts
     * @param $routeArray
     * @return Route
     */
    private function createAutoGenerationRoute ($requestPathParts, $routeArray) {

        $action = null;
        foreach ($routeArray as $route) {
            $basePath = $route[0];
            $baseNamespace = $route[1];

            //Obtención de las partes del path
            $basePathParts = explode(Request::PATH_SEPARATOR, trim($basePath, Request::PATH_SEPARATOR));
            if (sizeof($basePathParts) > 1) {
                $requestPathParts = array_slice($requestPathParts, sizeof($basePathParts) - 1);
            }
            $pathPartsSize = sizeof($requestPathParts);

            //Obtención del nombre de la clase de controlador
            $controllerClassName = $baseNamespace;
            if ($pathPartsSize > 1) {
                for ($i = 0; $i < $pathPartsSize - 1; $i++) {
                    if (!empty($controllerClassName)) {
                        $controllerClassName .= '\\';
                    }
                    $requestPathPart = $requestPathParts[$i];
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

            if (class_exists($controllerClassName)) {
                //Obtención del nombre de la metodo del controlador
                $controllerAction = (empty($requestPathParts) || empty($requestPathParts[$pathPartsSize - 1])) ? 'index' : $requestPathParts[$pathPartsSize - 1];
                $controllerAction = str_replace(' ', '', ucwords(str_replace('_', ' ', $controllerAction)));
                $controllerAction .= get_property('routes.actions_suffix', 'Action');
                $controllerAction[0] = strtolower($controllerAction[0]);

                //Obtención del nombre de la acción
                $routeAction = $controllerClassName . '@' . $controllerAction;

                //Creación de la acción
                $action = new Route($routeAction, []);
                break;
            }
        }
        return $action;
    }
}