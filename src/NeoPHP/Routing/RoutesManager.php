<?php

namespace NeoPHP\Routing;

/**
 * Class RoutesManager
 * @package NeoPHP\Routing
 */
abstract class RoutesManager {

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