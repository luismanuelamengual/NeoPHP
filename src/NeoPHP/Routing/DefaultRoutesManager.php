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
        if (empty($method)) {
            $method = "ANY";
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
     * @param $requestMethod
     * @param $requestPath
     * @return array
     */
    public function getMatchedRoutes($requestMethod, $requestPath) {
        $routes = [];
        $requestPathParts = explode(self::ROUTE_PATH_SEPARATOR, trim($requestPath, self::ROUTE_PATH_SEPARATOR));
        $routesIndex = &$this->routesIndex;
        if (array_key_exists(self::ROUTE_GENERIC_PATH, $routesIndex)) {
            $testRoutes = $routesIndex[self::ROUTE_GENERIC_PATH][self::ROUTE_ACTION_KEY];
            if (array_key_exists($requestMethod, $testRoutes)) {
                $routes[] = $this->createRoute($requestPathParts,$testRoutes[$requestMethod]);
            }
            if (array_key_exists("ANY", $testRoutes)) {
                $routes[] = $this->createRoute($requestPathParts,$testRoutes["ANY"]);
            }
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
                $testRoutes = $routesIndex[self::ROUTE_GENERIC_PATH][self::ROUTE_ACTION_KEY];
                if (array_key_exists($requestMethod, $testRoutes)) {
                    $routes[] = $this->createRoute($requestPathParts,$testRoutes[$requestMethod]);
                }
                if (array_key_exists("ANY", $testRoutes)) {
                    $routes[] = $this->createRoute($requestPathParts,$testRoutes["ANY"]);
                }
            }
        }
        if (array_key_exists(self::ROUTE_ACTION_KEY, $routesIndex)) {
            $testRoutes = $routesIndex[self::ROUTE_ACTION_KEY];
            if (array_key_exists($requestMethod, $testRoutes)) {
                $routes[] = $this->createRoute($requestPathParts,$testRoutes[$requestMethod]);
            }
            if (array_key_exists("ANY", $testRoutes)) {
                $routes[] = $this->createRoute($requestPathParts,$testRoutes["ANY"]);
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