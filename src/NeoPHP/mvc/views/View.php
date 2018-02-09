<?php

namespace NeoPHP\mvc\views;

use NeoPHP\mvc\MVCApplication;

abstract class View {

    protected $application;
    protected $name;
    protected $parameters;

    public function __construct(MVCApplication $application, $name, array $parameters = []) {
        $this->application = $application;
        $this->name = $name;
        $this->parameters = $parameters;
    }

    public function __get($name) {
        return $this->get($name);
    }

    public function __isset($name) {
        return $this->has($name);
    }

    public function __set($name, $value) {
        $this->set($name, $value);
    }

    public final function set($name, $value) {
        $this->parameters[$name] = $value;
    }

    public final function get($name) {
        return $this->parameters[$name];
    }

    public final function has($name) {
        return isset($this->parameters[$name]);
    }

    public function __toString() {
        return $this->render(true);
    }

    public final function render($return = false) {
        if ($return == true) {
            ob_start();
            $this->onRender();
            return ob_get_clean();
        }
        else {
            $this->onRender();
        }
    }

    protected abstract function onRender();
}