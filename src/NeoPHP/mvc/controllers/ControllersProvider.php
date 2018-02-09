<?php

namespace NeoPHP\mvc\controllers;

use NeoPHP\mvc\MVCApplication;

/**
 * Class ControllersProvider
 * @package NeoPHP\mvc\controllers
 */
class ControllersProvider {

    private $application;
    private $controllers;

    /**
     * ControllersProvider constructor.
     * @param MVCApplication $application
     */
    public function __construct(MVCApplication $application) {
        $this->application = $application;
        $this->controllers = [];
    }

    /**
     * @param $controllerClass
     * @return mixed
     */
    public function getController($controllerClass) {
        if (!isset($this->controllers[$controllerClass])) {
            if (!class_exists($controllerClass)) {
                throw new IllegalArgumentException("Controller \"$controllerClass\" not found !!.");
            }
            if (!is_subclass_of($controllerClass, Controller::class)) {
                throw new IllegalArgumentException("Invalid controller class \"$controllerClass\". Make sure this extends Controller");
            }
            $this->controllers[$controllerClass] = new $controllerClass($this->application);
        }
        return $this->controllers[$controllerClass];
    }
}