<?php

namespace NeoPHP\Views;

/**
 * Class View
 * @package NeoPHP\Views
 */
abstract class View {

    protected $name;
    protected $parameters;

    /**
     * View constructor.
     * @param $name
     * @param array $parameters
     */
    public function __construct($name, array $parameters = []) {
        $this->name = $name;
        $this->parameters = $parameters;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name) {
        return $this->get($name);
    }

    /**
     * @param $name
     * @return bool
     */
    public function __isset($name) {
        return $this->has($name);
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value) {
        $this->set($name, $value);
    }

    /**
     * @param $name
     * @param $value
     */
    public final function set($name, $value) {
        $this->parameters[$name] = $value;
    }

    /**
     * @param $name
     * @return mixed
     */
    public final function get($name) {
        return $this->parameters[$name];
    }

    /**
     * @param $name
     * @return bool
     */
    public final function has($name) {
        return isset($this->parameters[$name]);
    }

    /**
     * @return string
     */
    public function __toString() {
        return $this->render(true);
    }

    /**
     * @param bool $return
     * @return string
     */
    public final function render($return = false) {
        if ($return == true) {
            ob_start();
            $this->renderContent();
            return ob_get_clean();
        }
        else {
            $this->renderContent();
        }
    }

    /**
     * @return mixed
     */
    protected abstract function renderContent();
}