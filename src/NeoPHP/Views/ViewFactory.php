<?php

namespace NeoPHP\Views;

/**
 * Class ViewFactory
 * @package NeoPHP\mvc\views
 */
abstract class ViewFactory {

    private $config;

    /**
     * ViewFactory constructor.
     * @param $config
     */
    public function __construct(array $config = []) {
        $this->config = $config;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function get($key) {
        return $this->config[$key];
    }

    /**
     * @param $key
     * @param $value
     */
    public function set($key, $value) {
        $this->config[$key] = $value;
    }

    /**
     * @param $key
     * @return bool
     */
    public function has($key) {
        return isset($this->config[$key]);
    }

    /**
     * @param $name string name of the view
     * @param array $parameters (optional) parameters to the view
     * @return View created View
     */
    public abstract function createView($name, array $parameters = []): View;
}