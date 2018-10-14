<?php

namespace NeoPHP\Routing;

/**
 * Interface RouteActionGenerator
 * @package NeoPHP\Routing
 */
interface RouteActionGenerator  {

    /**
     * @param $method
     * @param array $path
     * @return mixed
     */
    public function generateAction($method, array $path);
}
