<?php

namespace NeoPHP\Routing;

use NeoPHP\Core\Application;

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
        self::addRoute(self::$beforeRoutes, "ANY", $path, $action);
    }

    /**
     * @param $path
     * @param $action
     */
    public static function after($path, $action) {
        self::addRoute(self::$afterRoutes, "ANY", $path, $action);
    }

    /**
     * @param $path
     * @param $action
     */
    public static function error($path, $action) {
        self::addRoute(self::$errorRoutes, "ANY", $path, $action);
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
     * @param $resourcePath
     * @param $controllerClass
     */
    public static function resource($resourcePath, $controllerClass) {
        $normalizedResourcePath = self::normalizePath($resourcePath);
        self::addRoute(self::$routes, "GET", $normalizedResourcePath, $controllerClass . "@getResources");
        self::addRoute(self::$routes, "GET", $normalizedResourcePath . self::ROUTE_PATH_SEPARATOR . ":id", $controllerClass . "@getResource");
        self::addRoute(self::$routes, "POST", $normalizedResourcePath . self::ROUTE_PATH_SEPARATOR . ":id", $controllerClass . "@updateResource");
        self::addRoute(self::$routes, "PUT", $normalizedResourcePath, $controllerClass . "@createResource");
        self::addRoute(self::$routes, "DELETE", $normalizedResourcePath . self::ROUTE_PATH_SEPARATOR . ":id", $controllerClass . "@deleteResource");
    }

    /**
     * @param $routesCollection
     * @param $method
     * @param $path
     * @param $action
     */
    private static function addRoute(&$routesCollection, $method, $path, $action) {
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
    private static function findRoutes(&$routeIndex, $method, $pathParts = []) {
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
    private static function getRequestPath() {
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
     * @param $routeAction
     * @param array $routeParameters
     * @return mixed|null
     * @throws \Exception
     */
    private static function executeRoute($routeAction, array $routeParameters = []) {
        $response = null;
        if (is_callable($routeAction)) {
            $response = call_user_func_array($routeAction, $routeParameters);
        }
        else {
            $response = Application::execute($routeAction, $routeParameters);
        }
        return $response;
    }

    /**
     * @param $routePath
     * @param $pathParts
     * @return array
     */
    private static function getRouteParameters($routePath, $pathParts) {
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
        return $routeParameters;
    }

    /**
     *
     * @throws \Throwable
     */
    public static function handleRequest() {
        $path = self::getRequestPath();
        $pathParts = self::getPathParts($path);
        $method = $_SERVER["REQUEST_METHOD"];
        try {
            $routes = self::findRoutes(self::$routes, $method, $pathParts);
            if (!empty($routes)) {
                $beforeRoutes = self::findRoutes(self::$beforeRoutes, $method, $pathParts);
                foreach ($beforeRoutes as $route) {
                    self::executeRoute($route[1]);
                }
                $result = null;
                foreach ($routes as $route) {
                    $routeResult = self::executeRoute($route[1], self::getRouteParameters($route[0], $pathParts));
                    if (!empty($routeResult)) {
                        $result = $routeResult;
                    }
                }
                $afterRoutes = self::findRoutes(self::$afterRoutes, $method, $pathParts);
                foreach ($afterRoutes as $route) {
                    self::executeRoute($route[1], [&$result]);
                }
            }
            else {
                throw new RouteNotFoundException("Route \"$path\" not found !!");
            }
        }
        catch (\Throwable $ex) {
            $errorRoutes = self::findRoutes(self::$errorRoutes, $method, $pathParts);
            if (!empty($errorRoutes)) {
                foreach ($errorRoutes as $route) {
                    self::executeRoute($route[1], [$ex]);
                }
            }
            else {
                throw $ex;
            }
        }
    }
}