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
     * @param null $parameters
     */
    public static function beforeGet($path, $action, $parameters = null) {
        self::$beforeRoutes->registerRoute(Request::METHOD_GET, $path, $action, $parameters);
    }

    /**
     * @param $path
     * @param $action
     * @param null $parameters
     */
    public static function beforePost($path, $action, $parameters = null) {
        self::$beforeRoutes->registerRoute(Request::METHOD_POST, $path, $action, $parameters);
    }

    /**
     * @param $path
     * @param $action
     * @param null $parameters
     */
    public static function beforePut($path, $action, $parameters = null) {
        self::$beforeRoutes->registerRoute(Request::METHOD_PUT, $path, $action, $parameters);
    }

    /**
     * @param $path
     * @param $action
     * @param null $parameters
     */
    public static function beforeDelete($path, $action, $parameters = null) {
        self::$beforeRoutes->registerRoute(Request::METHOD_DELETE, $path, $action, $parameters);
    }

    /**
     * @param $path
     * @param $action
     * @param null $parameters
     */
    public static function before($path, $action, $parameters = null) {
        self::$beforeRoutes->registerRoute(null, $path, $action, $parameters);
    }

    /**
     * @param $path
     * @param $action
     * @param null $parameters
     */
    public static function afterGet($path, $action, $parameters = null) {
        self::$afterRoutes->registerRoute(Request::METHOD_GET, $path, $action, $parameters);
    }

    /**
     * @param $path
     * @param $action
     * @param null $parameters
     */
    public static function afterPost($path, $action, $parameters = null) {
        self::$afterRoutes->registerRoute(Request::METHOD_POST, $path, $action, $parameters);
    }

    /**
     * @param $path
     * @param $action
     * @param null $parameters
     */
    public static function afterPut($path, $action, $parameters = null) {
        self::$afterRoutes->registerRoute(Request::METHOD_PUT, $path, $action, $parameters);
    }

    /**
     * @param $path
     * @param $action
     * @param null $parameters
     */
    public static function afterDelete($path, $action, $parameters = null) {
        self::$afterRoutes->registerRoute(Request::METHOD_DELETE, $path, $action, $parameters);
    }

    /**
     * @param $path
     * @param $action
     * @param null $parameters
     */
    public static function after($path, $action, $parameters = null) {
        self::$afterRoutes->registerRoute(null, $path, $action, $parameters);
    }

    /**
     * @param $path
     * @param $action
     * @param null $parameters
     */
    public static function error($path, $action, $parameters = null) {
        self::$errorRoutes->registerRoute(null, $path, $action, $parameters);
    }

    /**
     * @param $path
     * @param $action
     * @param null $parameters
     */
    public static function notFound($path, $action, $parameters = null) {
        self::$notFoundRoutes->registerRoute(null, $path, $action, $parameters);
    }

    /**
     * @param $path
     * @param $action
     * @param null $parameters
     */
    public static function get($path, $action, $parameters = null) {
        self::$routes->registerRoute(Request::METHOD_GET, $path, $action, $parameters);
    }

    /**
     * @param $path
     * @param $action
     * @param null $parameters
     */
    public static function post($path, $action, $parameters = null) {
        self::$routes->registerRoute(Request::METHOD_POST, $path, $action, $parameters);
    }

    /**
     * @param $path
     * @param $action
     * @param null $parameters
     */
    public static function put($path, $action, $parameters = null) {
        self::$routes->registerRoute(Request::METHOD_PUT, $path, $action, $parameters);
    }

    /**
     * @param $path
     * @param $action
     * @param null $parameters
     */
    public static function delete($path, $action, $parameters = null) {
        self::$routes->registerRoute(Request::METHOD_DELETE, $path, $action, $parameters);
    }

    /**
     * @param $path
     * @param $action
     * @param null $parameters
     */
    public static function any($path, $action, $parameters = null) {
        self::$routes->registerRoute(null, $path, $action, $parameters);
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
        self::$routes->registerRoute(Request::METHOD_GET, $path, function($path) use ($namespace) {
            return self::handleControllersRequest($path, $namespace);
        }/*, ["namespace"=>$namespace]*/);

        //self::$routes->registerAutoGenerationRoute(Request::METHOD_GET, $path, $namespace);
    }

    /**
     * @param $path
     * @param $namespace
     */
    public static function controllersPut ($path, $namespace = null) {
        //self::$routes->registerAutoGenerationRoute(Request::METHOD_PUT, $path, $namespace);
    }

    /**
     * @param $path
     * @param $namespace
     */
    public static function controllersPost ($path, $namespace = null) {
        //self::$routes->registerAutoGenerationRoute(Request::METHOD_POST, $path, $namespace);
    }

    /**
     * @param $path
     * @param $namespace
     */
    public static function controllersDelete ($path, $namespace = null) {
        //self::$routes->registerAutoGenerationRoute(Request::METHOD_DELETE, $path, $namespace);
    }

    /**
     * @param $path
     * @param $namespace
     */
    public static function controllers ($path, $namespace = null) {
        //self::$routes->registerAutoGenerationRoute(null, $path, $namespace);
    }

    /**
     * Procesa una ruta no encontrada
     * @throws Exception
     */
    private static function handleRequestNotFound () {
        $response = get_response();
        $response->clear();
        $response->statusCode(Response::HTTP_NOT_FOUND);
        $notFoundRoutes = self::$notFoundRoutes->getRoute();
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

    public static function handleControllersRequest ($path, $namespace) {
        echo "<br>*******************************";
        echo "<br>ESTAMOS EN CONTROLLERS REQUEST";
        echo "<pre>";
        print_r ($path);
        print_r ($namespace);
        echo "</pre>";
        echo "<br>*******************************";
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



/*
    private function createAutoGenerationRoute ($requestPathParts, $routeArray) {

        $action = null;
        foreach ($routeArray as $route) {
            $basePath = $route[0];
            $baseNamespace = $route[1];

            //Obtención de las partes del path
            $basePathParts = explode(Request::PATH_SEPARATOR, trim($basePath, Request::PATH_SEPARATOR));
            if (sizeof($basePathParts) > 1) {
                $requestPathParts = array_slice($requestPathParts, sizeof($basePathParts) - 1);
            }
            $pathPartsSize = sizeof($requestPathParts);

            //Obtención del nombre de la clase de controlador
            $controllerClassName = $baseNamespace;
            if ($pathPartsSize > 1) {
                for ($i = 0; $i < $pathPartsSize - 1; $i++) {
                    if (!empty($controllerClassName)) {
                        $controllerClassName .= '\\';
                    }
                    $requestPathPart = $requestPathParts[$i];
                    $requestPathPart = str_replace(' ', '', ucwords(str_replace('_', ' ', $requestPathPart)));
                    $controllerClassName .= $requestPathPart;
                }
            }
            else {
                if (!empty($controllerClassName)) {
                    $controllerClassName .= '\\';
                }
                $controllerClassName .= 'Main';
            }
            $controllerClassName .= get_property('routes.controllers_suffix', 'Controller');

            if (class_exists($controllerClassName)) {
                //Obtención del nombre de la metodo del controlador
                $controllerAction = (empty($requestPathParts) || empty($requestPathParts[$pathPartsSize - 1])) ? 'index' : $requestPathParts[$pathPartsSize - 1];
                $controllerAction = str_replace(' ', '', ucwords(str_replace('_', ' ', $controllerAction)));
                $controllerAction .= get_property('routes.actions_suffix', 'Action');
                $controllerAction[0] = strtolower($controllerAction[0]);

                //Obtención del nombre de la acción
                $routeAction = $controllerClassName . '@' . $controllerAction;

                //Creación de la acción
                $action = new Route($routeAction, []);
                break;
            }
        }
        return $action;
    }
    */