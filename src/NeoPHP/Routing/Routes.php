<?php

namespace NeoPHP\Routing;

use Exception;
use ReflectionClass;
use NeoPHP\ActionNotFoundException;
use NeoPHP\Http\Request;
use NeoPHP\Http\Response;
use Throwable;

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
        self::$routes = new RoutesManager();
        self::$beforeRoutes = new RoutesManager();
        self::$afterRoutes = new RoutesManager();
        self::$errorRoutes = new RoutesManager();
        self::$notFoundRoutes = new RoutesManager();
    }

    /**
     * @param $path
     * @param $action
     */
    public static function beforeGet($path, $action) {
        self::$beforeRoutes->registerRoute(Request::METHOD_GET, $path, $action);
    }

    /**
     * @param $path
     * @param $action
     */
    public static function beforePost($path, $action) {
        self::$beforeRoutes->registerRoute(Request::METHOD_POST, $path, $action);
    }

    /**
     * @param $path
     * @param $action
     */
    public static function beforePut($path, $action) {
        self::$beforeRoutes->registerRoute(Request::METHOD_PUT, $path, $action);
    }

    /**
     * @param $path
     * @param $action
     */
    public static function beforeDelete($path, $action) {
        self::$beforeRoutes->registerRoute(Request::METHOD_DELETE, $path, $action);
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
    public static function afterGet($path, $action) {
        self::$afterRoutes->registerRoute(Request::METHOD_GET, $path, $action);
    }

    /**
     * @param $path
     * @param $action
     */
    public static function afterPost($path, $action) {
        self::$afterRoutes->registerRoute(Request::METHOD_POST, $path, $action);
    }

    /**
     * @param $path
     * @param $action
     */
    public static function afterPut($path, $action) {
        self::$afterRoutes->registerRoute(Request::METHOD_PUT, $path, $action);
    }

    /**
     * @param $path
     * @param $action
     */
    public static function afterDelete($path, $action) {
        self::$afterRoutes->registerRoute(Request::METHOD_DELETE, $path, $action);
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
        self::$routes->registerRoute(Request::METHOD_GET, $path, $action);
    }

    /**
     * @param $path
     * @param $action
     */
    public static function post($path, $action) {
        self::$routes->registerRoute(Request::METHOD_POST, $path, $action);
    }

    /**
     * @param $path
     * @param $action
     */
    public static function put($path, $action) {
        self::$routes->registerRoute(Request::METHOD_PUT, $path, $action);
    }

    /**
     * @param $path
     * @param $action
     */
    public static function delete($path, $action) {
        self::$routes->registerRoute(Request::METHOD_DELETE, $path, $action);
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
        self::$routes->registerRoute(Request::METHOD_GET, $resourcePath, $controllerClass . "@getResources");
        self::$routes->registerRoute(Request::METHOD_GET, $resourcePath . Request::PATH_SEPARATOR . ":id", $controllerClass . "@getResource");
        self::$routes->registerRoute(Request::METHOD_POST, $resourcePath . Request::PATH_SEPARATOR . ":id", $controllerClass . "@updateResource");
        self::$routes->registerRoute(Request::METHOD_PUT, $resourcePath, $controllerClass . "@createResource");
        self::$routes->registerRoute(Request::METHOD_DELETE, $resourcePath . Request::PATH_SEPARATOR . ":id", $controllerClass . "@deleteResource");
    }

    /**
     * @param $path
     * @param $namespace
     */
    public static function routeGet ($path, $namespace = null) {
        self::$routes->registerAutoGenerationRoute(Request::METHOD_GET, $path, $namespace);
    }

    /**
     * @param $path
     * @param $namespace
     */
    public static function routePut ($path, $namespace = null) {
        self::$routes->registerAutoGenerationRoute(Request::METHOD_PUT, $path, $namespace);
    }

    /**
     * @param $path
     * @param $namespace
     */
    public static function routePost ($path, $namespace = null) {
        self::$routes->registerAutoGenerationRoute(Request::METHOD_POST, $path, $namespace);
    }

    /**
     * @param $path
     * @param $namespace
     */
    public static function routeDelete ($path, $namespace = null) {
        self::$routes->registerAutoGenerationRoute(Request::METHOD_DELETE, $path, $namespace);
    }

    /**
     * @param $path
     * @param $namespace
     */
    public static function route ($path, $namespace = null) {
        self::$routes->registerAutoGenerationRoute(null, $path, $namespace);
    }

    /**
     * Procesa una ruta no encontrada
     * @throws Exception
     */
    private static function handleRequestNotFound () {
        $response = get_response();
        $response->clear();
        $response->statusCode(Response::HTTP_NOT_FOUND);
        $notFoundRoute = self::$notFoundRoutes->getRoute();
        if (!empty($notFoundRoute)) {
            $routeResult = self::executeAction($notFoundRoute->getAction(), array_merge($_REQUEST, $notFoundRoute->getParameters()));
            if (isset($routeResult)) {
                $response->content($routeResult);
            }
            $response->send();
        }
        else {
            handle_error_code(Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Procesa una excepcion en la ejecución de una ruta
     * @throws Throwable
     */
    private static function handleRequestException (Throwable $ex) {
        ob_clean();
        $response = get_response();
        $response->clear();
        $errorRoute = self::$errorRoutes->getRoute();
        if (!empty($routes)) {
            $routeResult = self::executeAction($errorRoute->getAction(), array_merge($_REQUEST, $errorRoute->getParameters(), [Throwable::class=>$ex, Exception::class=>$ex]));
            if (!empty($routeResult)) {
                $response->content($routeResult);
            }
            $response->send();
        }
        else {
            throw $ex;
        }
    }

    /**
     * Procesa una petición web
     */
    public static function handleRequest() {
        $response = get_response();
        try {
            $route = self::$routes->getRoute();
            if (!empty($route)) {
                $beforeRoute = self::$beforeRoutes->getRoute();
                $executeRoute = true;
                if (!empty($beforeRoute)) {
                    $beforeRouteResponse = self::executeAction($beforeRoute->getAction(), array_merge($_REQUEST, $beforeRoute->getParameters()));
                    if (isset($beforeRouteResponse) && is_bool($beforeRouteResponse)) {
                        $executeRoute = $beforeRouteResponse;
                    }
                }

                if ($executeRoute) {
                    try {
                        $routeResult = self::executeAction($route->getAction(), array_merge($_REQUEST, $route->getParameters()));
                        if (isset($routeResult)) {
                            $response->content($routeResult);
                        }
                        $afterRoute = self::$afterRoutes->getRoute();
                        if (!empty($afterRoute)) {
                            $routeResult = self::executeAction($afterRoute->getAction(), array_merge($_REQUEST, $afterRoute->getParameters()));
                            if (isset($routeResult)) {
                                $response->content($routeResult);
                            }
                        }
                        $response->send();
                    }
                    catch (ActionNotFoundException $notFoundException) {
                        self::handleRequestNotFound();
                    }
                }
            }
            else {
                self::handleRequestNotFound();
            }
        }
        catch (Throwable $ex) {
            self::handleRequestException($ex);
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

$routesClass = new ReflectionClass(Routes::class);
$initMethod = $routesClass->getMethod("init");
$initMethod->setAccessible(true);
$initMethod->invoke(null);