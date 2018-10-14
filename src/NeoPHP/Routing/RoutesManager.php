<?php

namespace NeoPHP\Routing;

/**
 * Class DefaultRoutesManager
 * @package NeoPHP\Routing
 */
class RoutesManager {

    const ROUTE_ACTIONS_KEY = '__routeActions';
    const ROUTE_GENERIC_ACTIONS_KEY = '__routeGenericActions';

    const ROUTE_GENERIC_PATH = '*';
    const ROUTE_PARAMETER_PREFIX = ':';
    const ROUTE_PARAMETER_WILDCARD = '%';
    const ROUTE_PATH_SEPARATOR = '/';
    const ROUTE_GENERIC_METHOD = 'ANY';

    const ROUTE_PATH_PARAMETER_NAME = "path";

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
        $routeActionsKey = self::ROUTE_ACTIONS_KEY;
        foreach ($pathParts as $pathPart) {
            if ($pathPart == self::ROUTE_GENERIC_PATH) {
                $routeActionsKey = self::ROUTE_GENERIC_ACTIONS_KEY;
                break;
            }
            if ($pathPart[0] == self::ROUTE_PARAMETER_PREFIX) {
                $pathPart = self::ROUTE_PARAMETER_WILDCARD;
            }
            $routesIndex = &$routesIndex[$pathPart];
        }
        $routesIndex[$routeActionsKey][$method][] = [$path, $action];
    }

    /**
     * Obtiene todas las rutas que hacen match con el método y el path
     * @return array rutas obtenidas
     */
    public function getRoutes() : array {
        $request = get_request();
        $requestMethod = $request->method();
        $requestPathParts = $request->pathParts();
        $routesIndex = $this->routesIndex;
        $routes = [];
        $this->getRoutesFromIndex($routes,$routesIndex, $requestMethod, $requestPathParts);
        return $routes;
    }

    /**
     * Obtiene una ruta desde un indice de rutas
     * @param $routes
     * @param $routesIndex
     * @param $requestMethod
     * @param $requestPathParts
     * @param int $requestPathIndex
     * @return array rutas obtenidas
     */
    private function getRoutesFromIndex (&$routes, &$routesIndex, &$requestMethod, &$requestPathParts, $requestPathIndex = 0) {
        if (!empty($requestPathParts[$requestPathIndex])) {
            $requestPathPart = $requestPathParts[$requestPathIndex];
            if (array_key_exists($requestPathPart, $routesIndex)) {
                $this->getRoutesFromIndex($routes,$routesIndex[$requestPathPart], $requestMethod, $requestPathParts, $requestPathIndex + 1);
            }
            if (array_key_exists(self::ROUTE_PARAMETER_WILDCARD, $routesIndex)) {
                $this->getRoutesFromIndex($routes,$routesIndex[self::ROUTE_PARAMETER_WILDCARD], $requestMethod, $requestPathParts, $requestPathIndex + 1);
            }
        }
        else if (array_key_exists(self::ROUTE_ACTIONS_KEY, $routesIndex)) {
            $testRoutes = &$routesIndex[self::ROUTE_ACTIONS_KEY];
            if (array_key_exists($requestMethod, $testRoutes)) {
                $this->getRoutesFromIndexMethodActions($routes,$testRoutes[$requestMethod], $requestPathParts);
            }
            if (array_key_exists(self::ROUTE_GENERIC_METHOD, $testRoutes)) {
                $this->getRoutesFromIndexMethodActions($routes,$testRoutes[self::ROUTE_GENERIC_METHOD], $requestPathParts);
            }
        }

        if (array_key_exists(self::ROUTE_GENERIC_ACTIONS_KEY, $routesIndex)) {
            $testRoutes = &$routesIndex[self::ROUTE_GENERIC_ACTIONS_KEY];
            if (array_key_exists($requestMethod, $testRoutes)) {
                $this->getRoutesFromIndexMethodActions($routes,$testRoutes[$requestMethod], $requestPathParts);
            }
            if (array_key_exists(self::ROUTE_GENERIC_METHOD, $testRoutes)) {
                $this->getRoutesFromIndexMethodActions($routes,$testRoutes[self::ROUTE_GENERIC_METHOD], $requestPathParts);
            }
        }
    }

    /**
     * Obtiene rutas desde las acciones del metodo
     * @param array routes
     * @param array $methodActions
     * @param $requestPathParts
     */
    private function getRoutesFromIndexMethodActions (&$routes, array &$methodActions, array &$requestPathParts) {
        foreach ($methodActions as $routeData) {
            $routePath = $routeData[0];
            $routeAction = $routeData[1];
            $routeParameters = [];
            if (strpos($routePath, self::ROUTE_PARAMETER_PREFIX) !== false || strpos($routePath, self::ROUTE_GENERIC_PATH) !== false) {
                $routePathParts = explode(self::ROUTE_PATH_SEPARATOR, trim($routePath, self::ROUTE_PATH_SEPARATOR));;
                for ($i = 0; $i < sizeof($routePathParts); $i++) {
                    $routePathPart = $routePathParts[$i];
                    if ($routePathPart == self::ROUTE_GENERIC_PATH) {
                        $path = array_slice($requestPathParts, $i);
                        $routeParameters[self::ROUTE_PATH_PARAMETER_NAME] = $path;
                        break;
                    }
                    else if ($routePathPart[0] == self::ROUTE_PARAMETER_PREFIX) {
                        $parameterName = substr($routePathPart, 1);
                        $parameterValue = $requestPathParts[$i];
                        $routeParameters[$parameterName] = $parameterValue;
                    }
                }
            }
            $routes[] = new Route($routeAction, $routeParameters);
        }
    }
}