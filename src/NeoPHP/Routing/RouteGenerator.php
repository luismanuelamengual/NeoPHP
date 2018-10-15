<?php

namespace NeoPHP\Routing;

/**
 * Interface RouteActionGenerator
 * @package NeoPHP\Routing
 */
interface RouteGenerator  {

    /**
     * @param $method
     * @param array $path
     * @return mixed
     */
    public function generateRoute($method, array $path) : ?Route;
}
