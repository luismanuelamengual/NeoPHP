<?php

namespace NeoPHP\web\routes;

/**
 * Class Route
 * @package NeoPHP\web\routes
 */
class Route {

    private $method;
    private $path;
    private $action;

    /**
     * Route constructor.
     * @param string $method
     * @param string $path
     * @param string $action
     */
    public function __construct(string $method, string $path, string $action) {
        $this->method = $method;
        $this->path = $path;
        $this->action = $action;
    }

    /**
     * @return string
     */
    public function getMethod(): string {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getPath(): string {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getAction(): string {
        return $this->action;
    }
}