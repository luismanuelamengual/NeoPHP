<?php

namespace NeoPHP\Routing;

use NeoPHP\Http\Request;

/**
 * Class Routes
 * @package NeoPHP\Routing
 */
class Routes {

    private static $routes;
    private static $beforeRoutes;
    private static $afterRoutes;
    private static $errorRoutes;

    /**
     * Static initialization
     */
    private static function init () {
        self::$routes = new DefaultRoutesManager();
        self::$beforeRoutes = new DefaultRoutesManager();
        self::$afterRoutes = new DefaultRoutesManager();
        self::$errorRoutes = new DefaultRoutesManager();
    }

    /**
     * @param $path
     * @param $action
     */
    public static function before($path, $action) {
        self::$beforeRoutes->registerRoute (null, $path, $action);
    }

    /**
     * @param $path
     * @param $action
     */
    public static function after($path, $action) {
        self::$afterRoutes->registerRoute(null, $path, $action);
    }

    /**
     * @param $path
     * @param $action
     */
    public static function error($path, $action) {
        self::$errorRoutes->registerRoute(null, $path, $action);
    }

    /**
     * @param $path
     * @param $action
     */
    public static function get($path, $action) {
        self::$routes->registerRoute("GET", $path, $action);
    }

    /**
     * @param $path
     * @param $action
     */
    public static function post($path, $action) {
        self::$routes->registerRoute("POST", $path, $action);
    }

    /**
     * @param $path
     * @param $action
     */
    public static function put($path, $action) {
        self::$routes->registerRoute("PUT", $path, $action);
    }

    /**
     * @param $path
     * @param $action
     */
    public static function delete($path, $action) {
        self::$routes->registerRoute("DELETE", $path, $action);
    }

    /**
     * @param $path
     * @param $action
     */
    public static function any($path, $action) {
        self::$routes->registerRoute(null, $path, $action);
    }

    /**
     * @param $resourcePath
     * @param $controllerClass
     */
    public static function resource($resourcePath, $controllerClass) {
        self::$routes->registerRoute("GET", $resourcePath, $controllerClass . "@getResources");
        self::$routes->registerRoute("GET", $resourcePath . Request::PATH_SEPARATOR . ":id", $controllerClass . "@getResource");
        self::$routes->registerRoute("POST", $resourcePath . Request::PATH_SEPARATOR . ":id", $controllerClass . "@updateResource");
        self::$routes->registerRoute("PUT", $resourcePath, $controllerClass . "@createResource");
        self::$routes->registerRoute("DELETE", $resourcePath . Request::PATH_SEPARATOR . ":id", $controllerClass . "@deleteResource");
    }

    /**
     *
     * @throws \Throwable
     */
    public static function handleRequest() {
        $request = Request::getInstance();
        $requestMethod = $request->getMethod();
        $requestPath = $request->getPath();
        try {
            $routes = self::$routes->getMatchedRoutes($requestMethod, $requestPath);
            if (!empty($routes)) {
                foreach (self::$beforeRoutes->getMatchedRoutes($requestMethod, $requestPath) as $route) {
                    self::executeRoute($route);
                }
                $result = null;
                foreach ($routes as $route) {
                    $routeResult = self::executeRoute($route);
                    if (!empty($routeResult)) {
                        $result = $routeResult;
                    }
                }
                foreach (self::$afterRoutes->getMatchedRoutes($requestMethod, $requestPath) as $route) {
                    $route->setParameters([&$result]);
                    self::executeRoute($route);
                }
            }
            else {
                throw new RouteNotFoundException("Route \"" . $requestPath . "\" not found !!");
            }
        }
        catch (\Throwable $ex) {
            $routes = self::$errorRoutes->getMatchedRoutes($requestMethod, $requestPath);
            if (!empty($routes)) {
                foreach ($routes as $route) {
                    $route->setParameters([$ex]);
                    self::executeRoute($route);
                }
            }
            else {
                throw $ex;
            }
        }
    }

    /**
     * @param Route $route
     * @return mixed|null
     */
    private static function executeRoute (Route $route) {
        $result = null;
        if (is_callable($route->getAction())) {
            $result = call_user_func($route->getAction(), $route->getParameters());
        }
        else {
            $result = getApp()->execute($route->getAction(), $route->getParameters());
        }
        return $result;
    }
}

$routesClass = new \ReflectionClass(Routes::class);
$initMethod = $routesClass->getMethod("init");
$initMethod->setAccessible(true);
$initMethod->invoke(null);