<?php

namespace NeoPHP\Routing;

/**
 * Class Route
 * @package Sitrack\Routing
 */
class Route {

    private $action;
    private $parameters;

    /**
     * Route constructor.
     * @param $action
     * @param $parameters
     */
    public function __construct($action, $parameters = []) {
        $this->action = $action;
        $this->parameters = $parameters;
    }

    /**
     * @return mixed
     */
    public function getAction() {
        return $this->action;
    }

    /**
     * @param mixed $action
     */
    public function setAction($action) {
        $this->action = $action;
    }

    /**
     * @return array
     */
    public function getParameters(): array {
        return $this->parameters;
    }

    /**
     * @param array $parameters
     */
    public function setParameters(array $parameters) {
        $this->parameters = $parameters;
    }
}