<?php

namespace NeoPHP\mvc\views;
use NeoPHP\mvc\MVCApplication;

/**
 * Class ViewFactoriesProvider
 * @package NeoPHP\mvc\views
 */
abstract class ViewFactoriesProvider {

    protected $application;
    protected $initialized;
    protected $viewFactories;

    /**
     * ViewFactoriesProvider constructor.
     * @param MVCApplication $application
     */
    public function __construct(MVCApplication $application) {
        $this->application = $application;
        $this->viewFactories = [];
        $this->initialized = false;
    }

    /**
     * @param $viewFactoryName
     * @param $viewFactoryClassName
     */
    protected function registerViewFactory($viewFactoryName, $viewFactoryClassName) {

    }

    /**
     * @param $viewFactoryName
     * @return mixed
     */
    public function getViewFactory($viewFactoryName) {
        if (!$this->initialized) {
            $this->initiailze();
            $this->initialized = true;
        }
        return $this->viewFactories[$viewFactoryName];
    }

    /**
     * @return mixed
     */
    protected abstract function initialize ();
}