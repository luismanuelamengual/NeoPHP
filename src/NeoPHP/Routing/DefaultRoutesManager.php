<?php

namespace NeoPHP\Routing;

/**
 * Class DefaultRoutesManager
 * @package NeoPHP\Routing
 */
class DefaultRoutesManager extends RoutesManager {

    const ROUTE_ACTION_KEY = "__routeAction";

    const ROUTE_GENERIC_PATH = "*";
    const ROUTE_PARAMETER_PREFIX = ":";
    const ROUTE_PARAMETER_WILDCARD = "%";
    const ROUTE_PATH_SEPARATOR = '/';

    private $routesIndex = [];

    /**
     * @param $method
     * @param $path
     * @param $action
     */
    public function registerRoute($method, $path, $action) {
        $pathParts = explode(self::ROUTE_PATH_SEPARATOR, trim($path, self::ROUTE_PATH_SEPARATOR));
        $routesIndex = &$this->routesIndex[isset($method)? $method : "ANY"];
        foreach ($pathParts as $pathPart) {
            if ($pathPart[0] == self::ROUTE_PARAMETER_PREFIX) {
                $pathPart = self::ROUTE_PARAMETER_WILDCARD;
            }
            $routesIndex = &$routesIndex[$pathPart];
        }
        $routesIndex[self::ROUTE_ACTION_KEY] = [$path, $action];
    }

    /**
     * @param $requestMethod
     * @param $requestPath
     * @return array
     */
    public function getMatchedRoutes($requestMethod, $requestPath) {
        $routes = [];
        $requestPathParts = explode(self::ROUTE_PATH_SEPARATOR, trim($requestPath, self::ROUTE_PATH_SEPARATOR));
        $routesIndex = &$this->routesIndex[isset($method)? $method : "ANY"];
        if ($routesIndex != null) {
            if (array_key_exists(self::ROUTE_GENERIC_PATH, $routesIndex)) {
                $routes[] = $this->createRoute($requestPathParts, $routesIndex[self::ROUTE_GENERIC_PATH][self::ROUTE_ACTION_KEY]);
            }
            foreach ($requestPathParts as $requestPathPart) {
                if (!array_key_exists($requestPathPart, $routesIndex)) {
                    if (array_key_exists(self::ROUTE_PARAMETER_WILDCARD, $routesIndex)) {
                        $requestPathPart = self::ROUTE_PARAMETER_WILDCARD;
                    }
                    else {
                        break;
                    }
                }
                $routesIndex = &$routesIndex[$requestPathPart];
                if (array_key_exists(self::ROUTE_GENERIC_PATH, $routesIndex)) {
                    $routes[] = $this->createRoute($requestPathParts, $routesIndex[self::ROUTE_GENERIC_PATH][self::ROUTE_ACTION_KEY]);
                }
            }
            if (array_key_exists(self::ROUTE_ACTION_KEY, $routesIndex)) {
                $routes[] = $this->createRoute($requestPathParts, $routesIndex[self::ROUTE_ACTION_KEY]);
            }
        }

        return $routes;
    }

    /**
     * @param $requestPathParts
     * @param $routeArray
     * @return Route
     */
    private function createRoute ($requestPathParts, &$routeArray) : Route {
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
}