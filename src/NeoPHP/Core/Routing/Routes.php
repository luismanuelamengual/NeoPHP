<?php

namespace NeoPHP\Core\Routing;

use Exception;
use NeoPHP\Core\Controllers\Controllers;

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
    public static function any($path, $action) {
        self::addRoute(self::$routes, "ANY", $path, $action);
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
        $routesCollection[self::ROUTES_KEY][$method] = [$path, $action];
    }

    /**
     * @param $routeIndex
     * @param $method
     * @param array $pathParts
     * @return null
     */
    private static function findRoutes (&$routeIndex, $method, $pathParts = []) {
        $routes = [];
        $pathPart = array_shift($pathParts);
        if ($pathPart != null) {
            if (array_key_exists(self::ROUTE_GENERIC_PATH, $routeIndex)) {
                $routes = array_merge($routes, self::findRoutes($routeIndex[self::ROUTE_GENERIC_PATH], $method));
            }
            if (array_key_exists(self::ROUTE_PARAMETER_WILDCARD, $routeIndex)) {
                $routes = array_merge($routes, self::findRoutes($routeIndex[self::ROUTE_PARAMETER_WILDCARD], $method, $pathParts));
            }
            if (array_key_exists($pathPart, $routeIndex)) {
                $routes = array_merge($routes, self::findRoutes($routeIndex[$pathPart], $method, $pathParts));
            }
        }
        else {
            if (array_key_exists(self::ROUTE_GENERIC_PATH, $routeIndex)) {
                $genericRoutesIndex = $routeIndex[self::ROUTE_GENERIC_PATH];
                if (array_key_exists(self::ROUTES_KEY, $genericRoutesIndex)) {
                    $availableRoutes = $genericRoutesIndex[self::ROUTES_KEY];
                    if (array_key_exists($method, $availableRoutes)) {
                        $routes[] = $availableRoutes[$method];
                    }
                    else if (array_key_exists("ANY", $availableRoutes)) {
                        $routes[] = $availableRoutes["ANY"];
                    }
                }
            }
            if (array_key_exists(self::ROUTES_KEY, $routeIndex)) {
                $availableRoutes = $routeIndex[self::ROUTES_KEY];
                if (array_key_exists($method, $availableRoutes)) {
                    $routes[] = $availableRoutes[$method];
                }
                else if (array_key_exists("ANY", $availableRoutes)) {
                    $routes[] = $availableRoutes["ANY"];
                }
            }
        }
        return $routes;
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
     * @param $route
     * @throws Exception
     */
    private static function executeRoute ($pathParts, $route) {
        $routePath = $route[0];
        $routeAction = $route[1];
        $routeParameters = [];
        if (strpos($routePath, ':')) {
            $routePathParts = self::getPathParts($routePath);
            for ($i = 0; $i < sizeof($routePathParts); $i++) {
                $routePathPart = $routePathParts[$i];
                if ($routePathPart[0] == self::ROUTE_PARAMETER_PREFIX) {
                    $parameterName = substr($routePathPart, 1);
                    $parameterValue = $pathParts[$i];
                    $routeParameters[$parameterName] = $parameterValue;
                }
            }
        }
        Controllers::execute($routeAction, $routeParameters);
    }

    /**
     *
     */
    public static function handleRequest () {
        $path = self::getRequestPath();
        $pathParts = self::getPathParts($path);
        $method = $_SERVER["REQUEST_METHOD"];
        try {
            $routes = self::findRoutes(self::$routes, $method, $pathParts);
            if (!empty($routes)) {
                $beforeRoutes = self::findRoutes(self::$beforeRoutes, $method, $pathParts);
                foreach ($beforeRoutes as $route) {
                    self::executeRoute($pathParts, $route);
                }
                foreach ($routes as $route) {
                    self::executeRoute($pathParts, $route);
                }
                $afterRoutes = self::findRoutes(self::$afterRoutes, $method, $pathParts);
                foreach ($afterRoutes as $route) {
                    self::executeRoute($pathParts, $route);
                }
            }
            else {
                throw new RouteNotFoundException("Route \"$path\" not found !!");
            }
        }
        catch (\Throwable $ex) {
            $exceptionHandled = false;
            try {
                $errorRoutes = self::findRoutes(self::$errorRoutes, $method, $pathParts);
                if (!empty($errorRoutes)) {
                    foreach ($errorRoutes as $route) {
                        self::executeRoute($pathParts, $route);
                    }
                    $exceptionHandled = true;
                }
            }
            catch (\Throwable $ex1) {
            }

            if (!$exceptionHandled) {
                if ($ex instanceof RouteNotFoundException) {
                    http_response_code(404);
                }
                else {
                    http_response_code(500);
                }
                echo "ERROR: " . $ex->getMessage();
                echo "<pre>";
                echo print_r($ex->getTraceAsString(), true);
                echo "</pre>";
            }
        }
    }
}