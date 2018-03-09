<?php

namespace NeoPHP\Routing;

use NeoPHP\Http\Request;
use NeoPHP\Http\Response;

/**
 * Class Routes
 * @package NeoPHP\Routing
 */
class Routes {

    private static $routes;
    private static $beforeRoutes;
    private static $afterRoutes;
    private static $errorRoutes;
    private static $notFoundRoutes;

    /**
     * Static initialization
     */
    private static function init() {
        self::$routes = new DefaultRoutesManager();
        self::$beforeRoutes = new DefaultRoutesManager();
        self::$afterRoutes = new DefaultRoutesManager();
        self::$errorRoutes = new DefaultRoutesManager();
        self::$notFoundRoutes = new DefaultRoutesManager();

        register_event_listener("application_start", function() {
            self::handleRequest();
        });
    }

    /**
     * @param $path
     * @param $action
     */
    public static function before($path, $action) {
        self::$beforeRoutes->registerRoute(null, $path, $action);
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
    public static function notFound($path, $action) {
        self::$notFoundRoutes->registerRoute(null, $path, $action);
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
     * Handles a request
     * Executes the registered routes that matches the request
     * @throws \Throwable
     */
    private static function handleRequest() {
        $request = get_request();
        $response = get_response();
        $requestMethod = $request->method();
        $requestPath = $request->path();
        try {
            $routes = self::$routes->getMatchedRoutes($requestMethod, $requestPath);
            if (!empty($routes)) {
                foreach (self::$beforeRoutes->getMatchedRoutes($requestMethod, $requestPath) as $route) {
                    self::executeAction($route->getAction(), array_merge($_REQUEST, $route->getParameters()));
                }
                if (!$response->sent()) {
                    foreach ($routes as $route) {
                        $routeResult = self::executeAction($route->getAction(), array_merge($_REQUEST, $route->getParameters()));
                        if (!empty($routeResult)) {
                            $response->content($routeResult);
                        }
                    }
                    if (!$response->sent()) {
                        foreach (self::$afterRoutes->getMatchedRoutes($requestMethod, $requestPath) as $route) {
                            $routeResult = self::executeAction($route->getAction(), array_merge($_REQUEST, $route->getParameters()));
                            if (!empty($routeResult)) {
                                $response->content($routeResult);
                            }
                        }
                    }
                }
                $response->send();
            }
            else {
                $response->clear();
                $response->statusCode(Response::HTTP_NOT_FOUND);
                $notFoundRoutes = self::$notFoundRoutes->getMatchedRoutes($requestMethod, $requestPath);
                if (!empty($notFoundRoutes)) {
                    foreach ($notFoundRoutes as $route) {
                        $routeResult = self::executeAction($route->getAction(), array_merge($_REQUEST, $route->getParameters()));
                        if (!empty($routeResult)) {
                            $response->content($routeResult);
                        }
                    }
                    $response->send();
                }
                else {
                    handle_error_code(404);
                }
            }
        }
        catch (\Throwable $ex) {
            ob_clean();
            $response->clear();
            $routes = self::$errorRoutes->getMatchedRoutes($requestMethod, $requestPath);
            if (!empty($routes)) {
                foreach ($routes as $route) {
                    $routeResult = self::executeAction($route->getAction(), array_merge($_REQUEST, $route->getParameters(), [\Throwable::class=>$ex, \Exception::class=>$ex]));
                    if (!empty($routeResult)) {
                        $response->content($routeResult);
                    }
                }
                $response->send();
            }
            else {
                throw $ex;
            }
        }
    }

    /**
     * @param $action
     * @param array $parameters
     * @return mixed|null
     * @throws \Exception
     */
    private static function executeAction($action, array $parameters = []) {
        return get_app()->execute($action, $parameters);
    }
}

$routesClass = new \ReflectionClass(Routes::class);
$initMethod = $routesClass->getMethod("init");
$initMethod->setAccessible(true);
$initMethod->invoke(null);