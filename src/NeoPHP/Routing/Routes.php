<?php

namespace NeoPHP\Routing;

use Exception;
use NeoPHP\Controllers\ControllersRouteActionGenerator;
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

    /** @var RoutesManager */
    private static $routes;

    /** @var RoutesManager */
    private static $beforeRoutes;

    /** @var RoutesManager */
    private static $afterRoutes;

    /** @var RoutesManager */
    private static $errorRoutes;

    /** @var RoutesManager */
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
    public static function controllersGet ($path, $namespace = null) {
        self::get($path, new ControllersRouteActionGenerator($namespace));
    }

    /**
     * @param $path
     * @param $namespace
     */
    public static function controllersPut ($path, $namespace = null) {
        self::put($path, new ControllersRouteActionGenerator($namespace));
    }

    /**
     * @param $path
     * @param $namespace
     */
    public static function controllersPost ($path, $namespace = null) {
        self::post($path, new ControllersRouteActionGenerator($namespace));
    }

    /**
     * @param $path
     * @param $namespace
     */
    public static function controllersDelete ($path, $namespace = null) {
        self::delete($path, new ControllersRouteActionGenerator($namespace));
    }

    /**
     * @param $path
     * @param $namespace
     */
    public static function controllers ($path, $namespace = null) {
        self::any($path, new ControllersRouteActionGenerator($namespace));
    }

    /**
     * Procesa una ruta no encontrada
     * @throws Exception
     */
    private static function handleRequestNotFound () {
        $response = get_response();
        $response->clear();
        $response->statusCode(Response::HTTP_NOT_FOUND);
        $notFoundRoutes = self::$notFoundRoutes->getRoutes();
        if (!empty($notFoundRoutes)) {
            foreach ($notFoundRoutes as $notFoundRoute) {
                $routeResult = self::executeAction($notFoundRoute->getAction(), array_merge($_REQUEST, $notFoundRoute->getParameters()));
                if (isset($routeResult)) {
                    $response->content($routeResult);
                }
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
        $errorRoutes = self::$errorRoutes->getRoutes();
        if (!empty($errorRoutes)) {
            foreach ($errorRoutes as $errorRoute) {
                $routeResult = self::executeAction($errorRoute->getAction(), array_merge($_REQUEST, $errorRoute->getParameters(), [Throwable::class => $ex, Exception::class => $ex]));
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

    /**
     * Procesa una petición web
     */
    public static function handleRequest() {
        $response = get_response();
        try {
            $routes = self::$routes->getRoutes();
            if (!empty($routes)) {
                $beforeRoutes = self::$beforeRoutes->getRoutes();
                if (!empty($beforeRoutes)) {
                    foreach ($beforeRoutes as $beforeRoute) {
                        self::executeAction($beforeRoute->getAction(), array_merge($_REQUEST, $beforeRoute->getParameters()));
                    }
                }

                try {
                    foreach ($routes as $route) {
                        $routeResult = self::executeAction($route->getAction(), array_merge($_REQUEST, $route->getParameters()));
                        if (isset($routeResult)) {
                            $response->content($routeResult);
                        }
                    }
                    $afterRoutes = self::$afterRoutes->getRoutes();
                    if (!empty($afterRoutes)) {
                        foreach ($afterRoutes as $afterRoute) {
                            $routeResult = self::executeAction($afterRoute->getAction(), array_merge($_REQUEST, $afterRoute->getParameters()));
                            if (isset($routeResult)) {
                                $response->content($routeResult);
                            }
                        }
                    }
                    $response->send();
                }
                catch (ActionNotFoundException $notFoundException) {
                    self::handleRequestNotFound();
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