<?php

namespace NeoPHP\Routing;

/**
 * Class RoutesCollection
 * @package NeoPHP\Routing
 */
abstract class RoutesCollection {

    /**
     * @param $method
     * @param $path
     * @param $action
     * @return mixed
     */
    public abstract function registerRoute ($method, $path, $action);

    /**
     * @param $requestMethod
     * @param $requestPath
     * @return mixed
     */
    public abstract function getMatchedRoutes ($requestMethod, $requestPath);
}