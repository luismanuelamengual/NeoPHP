<?php

namespace NeoPHP\Core\Routing;

use NeoPHP\Http\Response;

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
    private static function findRoutes (&$routeIndex, $method, $path) {
        return self::findRoutesInIndex($routeIndex, $method, self::getPathParts($path));
    }

    /**
     * @param $routeIndex
     * @param $method
     * @param array $pathParts
     * @return null
     */
    private static function findRoutesInIndex (&$routeIndex, $method, $pathParts = []) {
        $routes = [];
        $pathPart = array_shift($pathParts);
        if ($pathPart != null) {
            if (array_key_exists(self::ROUTE_GENERIC_PATH, $routeIndex)) {
                $routes = array_merge($routes, self::findRoutesInIndex($routeIndex[self::ROUTE_GENERIC_PATH], $method));
            }
            if (array_key_exists(self::ROUTE_PARAMETER_WILDCARD, $routeIndex)) {
                $routes = array_merge($routes, self::findRoutesInIndex($routeIndex[self::ROUTE_PARAMETER_WILDCARD], $method, $pathParts));
            }
            if (array_key_exists($pathPart, $routeIndex)) {
                $routes = array_merge($routes, self::findRoutesInIndex($routeIndex[$pathPart], $method, $pathParts));
            }
        }
        else {
            if (array_key_exists(self::ROUTE_GENERIC_PATH, $routeIndex)) {
                $genericRoutesIndex = $routeIndex[self::ROUTE_GENERIC_PATH];
                if (array_key_exists(self::ROUTES_KEY, $genericRoutesIndex)) {
                    foreach ($genericRoutesIndex[self::ROUTES_KEY] as $indexRoute) {
                        if ($indexRoute[0] == null || $indexRoute[0] == $method) {
                            $routes[] = $indexRoute;
                        }
                    }
                }
            }
            if (array_key_exists(self::ROUTES_KEY, $routeIndex)) {
                foreach ($routeIndex[self::ROUTES_KEY] as $indexRoute) {
                    if ($indexRoute[0] == null || $indexRoute[0] == $method) {
                        $routes[] = $indexRoute;
                    }
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
     * @param $action
     */
    private static function executeAction ($action) {
        echo "Execute: $action<br>";
    }

    /**
     *
     */
    public static function handleRequest () {
        $path = self::getRequestPath();
        $method = $_SERVER["REQUEST_METHOD"];
        try {
            $routes = self::findRoutes(self::$routes, $method, $path);
            if (!empty($routes)) {
                $beforeRoutes = self::findRoutes(self::$beforeRoutes, $method, $path);
                foreach ($beforeRoutes as $route) {
                    self::executeAction($route[2]);
                }
                foreach ($routes as $route) {
                    self::executeAction($route[2]);
                }
                $afterRoutes = self::findRoutes(self::$afterRoutes, $method, $path);
                foreach ($afterRoutes as $route) {
                    self::executeAction($route[2]);
                }
            }
            else {
                throw new RouteNotFoundException("Route \"$path\" not found !!");
            }
        }
        catch (\Throwable $ex) {
            $exceptionHandled = false;
            try {
                $errorRoutes = self::findRoutes(self::$errorRoutes, $method, $path);
                if (!empty($errorRoutes)) {
                    foreach ($errorRoutes as $route) {
                        self::executeAction($route[2]);
                    }
                    $exceptionHandled = true;
                }
            }
            catch (\Throwable $ex1) {
            }

            if (!$exceptionHandled) {
                $response = new Response();
                if ($ex instanceof RouteNotFoundException) {
                    $response->setStatusCode(404);
                }
                else {
                    $response->setStatusCode(500);
                }
                $responseContent = "";
                $responseContent .= "ERROR: " . $ex->getMessage();
                $responseContent .= "<pre>";
                $responseContent .= print_r($ex->getTraceAsString(), true);
                $responseContent .= "</pre>";
                $response->setContent($responseContent);
                $response->send();
            }
        }
    }
}