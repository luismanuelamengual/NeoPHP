<?php

namespace NeoPHP\Routing;

/**
 * Class Route
 * @package NeoPHP\Routing
 */
class Route {

    private $action;
    private $parameters;
    private $exclusive;

    /**
     * Route constructor.
     * @param $action
     * @param $parameters
     */
    public function __construct($action, $parameters = [], $exclusive = false) {
        $this->action = $action;
        $this->parameters = $parameters;
        $this->exclusive = $exclusive;
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

    /**
     * @return bool
     */
    public function isExclusive(): bool {
        return $this->exclusive;
    }

    /**
     * @param bool $exclusive
     */
    public function setExclusive(bool $exclusive): void {
        $this->exclusive = $exclusive;
    }
}