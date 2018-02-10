<?php

namespace NeoPHP\Core\Routing;

abstract class Routes {

    const ROUTES_KEY = "__routes";

    const ROUTE_GENERIC_PATH = "*";
    const ROUTE_PARAMETER_PREFIX = ":";
    const ROUTE_PARAMETER_WILDCARD = "%";
    const ROUTE_PATH_SEPARATOR = "/";

    private static $routes = [];

    public static function get($path, $action) {
        self::addRoute(self::$routes, "GET", $path, $action);
    }

    public static function post($path, $action) {
        self::addRoute(self::$routes, "POST", $path, $action);
    }

    private static function addRoute (&$routesCollection, $method, $path, $action) {
        $pathParts = self::getPathParts($path);
        foreach ($pathParts as $pathPart) {
            $pathIndex = null;
            if ($pathPart[0] == self::ROUTE_PARAMETER_PREFIX) {
                $pathIndex = self::ROUTE_PARAMETER_WILDCARD;
            }
            else {
                $pathIndex = $pathPart;
            }
            $routesCollection = &$routesCollection[$pathIndex];
        }
        $routesCollection[self::ROUTES_KEY][] = [$method, $path, $action];
    }

    private static function findRoute (&$routesCollection, $method, $path) {
        $pathParts = self::getPathParts($path);
        $route = self::findRouteInIndex($routesCollection, $method, $pathParts);
        return $route;
    }

    private static function findRouteInIndex (&$routeIndex, $method, $pathParts = []) {
        $route = null;
        $pathPart = array_shift($pathParts);
        if ($pathPart != null) {
            if (array_key_exists($pathPart, $routeIndex)) {
                $route = self::findRouteInIndex($routeIndex[$pathPart], $method, $pathParts);
            }
            if ($route == null) {
                if (array_key_exists(self::ROUTE_PARAMETER_WILDCARD, $routeIndex)) {
                    $route = self::findRouteInIndex($routeIndex[self::ROUTE_PARAMETER_WILDCARD], $method, $pathParts);
                }
            }
            if ($route == null) {
                if (array_key_exists(self::ROUTE_GENERIC_PATH, $routeIndex)) {
                    $route = self::findRouteInIndex($routeIndex[self::ROUTE_GENERIC_PATH], $method);
                }
            }
        }
        else {
            foreach ($routeIndex[self::ROUTES_KEY] as $indexRoute) {
                if ($indexRoute[0] == null || $indexRoute[0] == $method) {
                    $route = $indexRoute;
                    break;
                }
            }
            if ($route == null) {
                $genericRouteIndex = $routeIndex[self::ROUTE_GENERIC_PATH];
                if ($genericRouteIndex != null) {
                    foreach ($genericRouteIndex[self::ROUTES_KEY] as $indexRoute) {
                        if ($indexRoute[0] == null || $indexRoute[0] == $method) {
                            $route = $indexRoute;
                            break;
                        }
                    }
                }
            }
        }
        return $route;
    }

    private static function normalizePath($path) {
        return trim($path, "/");
    }

    private static function getPathParts($path) {
        return explode("/", self::normalizePath($path));
    }

    public static function handleRequest ($path) {
        $route = self::findRoute(self::$routes, "GET", $path);

        echo "<pre>";
        print_r ($route);
        echo "</pre>";
    }
}