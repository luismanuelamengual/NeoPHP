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
        return $this->getRoutesFromIndex($routesIndex, $requestMethod, $requestPathParts);
    }

    /**
     * Obtiene una ruta desde un indice de rutas
     * @param $routesIndex
     * @param $requestMethod
     * @param $requestPathParts
     * @param int $requestPathIndex
     * @return array rutas obtenidas
     */
    private function getRoutesFromIndex (&$routesIndex, &$requestMethod, &$requestPathParts, $requestPathIndex = 0) : array {
        $routes = [];
        if (array_key_exists($requestPathIndex, $requestPathParts)) {
            $requestPathPart = $requestPathParts[$requestPathIndex];
            if (!empty($requestPathPart)) {
                if (array_key_exists($requestPathPart, $routesIndex)) {
                    $routes = array_merge($routes, $this->getRoutesFromIndex($routesIndex[$requestPathPart], $requestMethod, $requestPathParts, $requestPathIndex + 1));
                }
                if (array_key_exists(self::ROUTE_PARAMETER_WILDCARD, $routesIndex)) {
                    $routes = array_merge($routes, $this->getRoutesFromIndex($routesIndex[self::ROUTE_PARAMETER_WILDCARD], $requestMethod, $requestPathParts, $requestPathIndex + 1));
                }
            }
        }
        else if (array_key_exists(self::ROUTE_ACTIONS_KEY, $routesIndex)) {
            $testRoutes = &$routesIndex[self::ROUTE_ACTIONS_KEY];
            if (array_key_exists($requestMethod, $testRoutes)) {
                $routes = array_merge($routes, $this->getRoutesFromIndexMethodActions($testRoutes[$requestMethod], $requestPathParts));
            }
            if (array_key_exists(self::ROUTE_GENERIC_METHOD, $testRoutes)) {
                $routes = array_merge($routes, $this->getRoutesFromIndexMethodActions($testRoutes[self::ROUTE_GENERIC_METHOD], $requestPathParts));
            }
        }

        if (array_key_exists(self::ROUTE_GENERIC_ACTIONS_KEY, $routesIndex)) {
            $testRoutes = &$routesIndex[self::ROUTE_GENERIC_ACTIONS_KEY];
            if (array_key_exists($requestMethod, $testRoutes)) {
                $routes = array_merge($routes, $this->getRoutesFromIndexMethodActions($testRoutes[$requestMethod], $requestPathParts));
            }
            if (array_key_exists(self::ROUTE_GENERIC_METHOD, $testRoutes)) {
                $routes = array_merge($routes, $this->getRoutesFromIndexMethodActions($testRoutes[self::ROUTE_GENERIC_METHOD], $requestPathParts));
            }
        }
        return $routes;
    }

    /**
     * Obtiene rutas desde las acciones del metodo
     * @param array $methodActions
     * @param $requestPathParts
     * @return array rutas creadas
     */
    private function getRoutesFromIndexMethodActions (array &$methodActions, array &$requestPathParts) : array {
        $createdRoutes = [];
        foreach ($methodActions as $routeData) {
            $routePath = $routeData[0];
            $routeAction = $routeData[1];
            $routeParameters = [];
            if (strpos($routePath, self::ROUTE_PARAMETER_PREFIX) !== false || strpos($routePath, self::ROUTE_GENERIC_PATH) !== false) {
                $routePathParts = explode(self::ROUTE_PATH_SEPARATOR, trim($routePath, self::ROUTE_PATH_SEPARATOR));;
                for ($i = 0; $i < sizeof($routePathParts); $i++) {
                    $routePathPart = $routePathParts[$i];
                    if ($routePathPart == self::ROUTE_GENERIC_PATH) {
                        $routeParameters[self::ROUTE_PATH_PARAMETER_NAME] = array_slice($requestPathParts, $i);
                        break;
                    }
                    else if ($routePathPart[0] == self::ROUTE_PARAMETER_PREFIX) {
                        $parameterName = substr($routePathPart, 1);
                        $parameterValue = $requestPathParts[$i];
                        $routeParameters[$parameterName] = $parameterValue;
                    }
                }
            }
            $createdRoutes[] = new Route($routeAction, $routeParameters);
        }
        return $createdRoutes;
    }
}