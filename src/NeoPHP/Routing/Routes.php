<?php

namespace NeoPHP\Routing;

use NeoPHP\Core\Application;

/**
 * Class Routes
 * @package NeoPHP\Core\Routing
 */
abstract class Routes {

    const ROUTE_GENERIC_PATH = "*";
    const ROUTE_PARAMETER_PREFIX = ":";
    const ROUTE_PARAMETER_WILDCARD = "%";
    const ROUTE_PATH_SEPARATOR = "/";

    private static $requestPath;
    private static $requestPathParts = [];
    private static $routes = [];
    private static $beforeRoutes = [];
    private static $afterRoutes = [];
    private static $errorRoutes = [];

    /**
     * Static initialization
     */
    private static function init() {
        self::$requestPath = self::getRequestPath();
        self::$requestPathParts = self::getPathParts(self::$requestPath);
    }

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
        self::addRoute(self::$routes, null, $path, $action);
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
     * @return bool
     */
    private static function addRoute(&$routesCollection, $method, $path, $action) {
        if ($method == null || $method == $_SERVER["REQUEST_METHOD"]) {
            $routeParameters = [];
            $pathParts = self::getPathParts($path);
            for ($i = 0; $i < sizeof($pathParts); $i++) {
                $pathPart = $pathParts[$i];
                if ($pathPart == self::ROUTE_GENERIC_PATH) {
                    $route = new \stdClass();
                    $route->action = $action;
                    $route->parameters = $routeParameters;
                    $routesCollection[] = $route;
                    return true;
                }
                if (!isset(self::$requestPathParts[$i])) {
                    return false;
                }
                if ($pathPart != self::$requestPathParts[$i]) {
                    if ($pathPart[0] == self::ROUTE_PARAMETER_PREFIX) {
                        $routeParameters[substr($pathPart, 1)] = self::$requestPathParts[$i];
                    }
                    else {
                        return false;
                    }
                }
            }
            if (!isset(self::$requestPathParts[$i])) {
                $route = new \stdClass();
                $route->action = $action;
                $route->parameters = $routeParameters;
                $routesCollection[] = $route;
                return true;
            }
        }
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
     *
     * @throws \Throwable
     */
    public static function handleRequest() {
        try {
            if (!empty(self::$routes)) {
                foreach (self::$beforeRoutes as $route) {
                    self::executeRoute($route->action, $route->parameters);
                }
                $result = null;
                foreach (self::$routes as $route) {
                    $routeResult = self::executeRoute($route->action, $route->parameters);
                    if (!empty($routeResult)) {
                        $result = $routeResult;
                    }
                }
                foreach (self::$afterRoutes as $route) {
                    self::executeRoute($route->action, [&$result]);
                }
            }
            else {
                throw new RouteNotFoundException("Route \"" . self::$requestPath . "\" not found !!");
            }
        }
        catch (\Throwable $ex) {
            if (!empty(self::$errorRoutes)) {
                foreach (self::$errorRoutes as $route) {
                    self::executeRoute($route->action, [$ex]);
                }
            }
            else {
                throw $ex;
            }
        }
    }
}

$routesClass = new \ReflectionClass(Routes::class);
$initMethod = $routesClass->getMethod("init");
$initMethod->setAccessible(true);
$initMethod->invoke(null);