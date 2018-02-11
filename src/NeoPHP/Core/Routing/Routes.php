<?php

namespace NeoPHP\Core\Routing;

/**
 * Class Routes
 * @package NeoPHP\Core\Routing
 */
abstract class Routes {

    const ROUTES_KEY = "__routes";

    const ROUTE_GENERIC_PATH = "*";
    const ROUTE_PARAMETER_PREFIX = ":";
    const ROUTE_PARAMETER_WILDCARD = "%";
    const ROUTE_PATH_SEPARATOR = "/";

    private static $routes = [];
    private static $beforeRoutes = [];
    private static $afterRoutes = [];
    private static $errorRoutes = [];

    /**
     * @param $path
     * @param $action
     */
    public static function before($path, $action) {
        self::addRoute(self::$beforeRoutes, null, $path, $action);
    }

    /**
     * @param $path
     * @param $action
     */
    public static function after($path, $action) {
        self::addRoute(self::$afterRoutes, null, $path, $action);
    }

    /**
     * @param $path
     * @param $action
     */
    public static function error($path, $action) {
        self::addRoute(self::$errorRoutes, null, $path, $action);
    }

    /**
     * @param $path
     * @param $action
     */
    public static function get($path, $action) {
        self::addRoute(self::$routes, "GET", $path, $action);
    }

    /**
     * @param $path
     * @param $action
     */
    public static function post($path, $action) {
        self::addRoute(self::$routes, "POST", $path, $action);
    }

    /**
     * @param $path
     * @param $action
     */
    public static function put($path, $action) {
        self::addRoute(self::$routes, "PUT", $path, $action);
    }

    /**
     * @param $path
     * @param $action
     */
    public static function delete($path, $action) {
        self::addRoute(self::$routes, "DELETE", $path, $action);
    }

    /**
     * @param $path
     * @param $action
     */
    public static function request($path, $action) {
        self::addRoute(self::$routes, null, $path, $action);
    }

    /**
     * @param $routesCollection
     * @param $method
     * @param $path
     * @param $action
     */
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

    /**
     * @param $routeIndex
     * @param $method
     * @param $path
     * @return null
     */
    private static function findRoute (&$routeIndex, $method, $path) {
        return self::findRouteInIndex($routeIndex, $method, self::getPathParts($path));
    }

    /**
     * @param $routeIndex
     * @param $method
     * @param array $pathParts
     * @return null
     */
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

    /**
     * @param $path
     * @return string
     */
    private static function normalizePath($path) {
        return trim($path, "/");
    }

    /**
     * @param $path
     * @return array
     */
    private static function getPathParts($path) {
        return explode("/", self::normalizePath($path));
    }

    /**
     * @return bool|string
     */
    private static function getRequestPath () {
        $path = "";
        if (!empty($_SERVER["REDIRECT_URL"])) {
            $path = $_SERVER["REDIRECT_URL"];
            if (!empty($_SERVER["CONTEXT_PREFIX"])) {
                $path = substr($path, strlen($_SERVER["CONTEXT_PREFIX"]));
            }
        }
        return $path;
    }

    /**
     *
     */
    public static function handleRequest () {
        $path = self::getRequestPath();
        echo "path: $path";
    }
}