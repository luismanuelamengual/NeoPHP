<?php

namespace NeoPHP\mvc\models;

use NeoPHP\mvc\MVCApplication;

/**
 * Class ModelManagersProvider
 * @package NeoPHP\mvc\models
 */
abstract class ModelManagersProvider {

    protected $application;
    protected $initialized;
    protected $managers;

    /**
     * ModelManagersProvider constructor.
     * @param MVCApplication $application
     */
    public function __construct(MVCApplication $application) {
        $this->application = $application;
        $this->managers = [];
        $this->initialized = false;
    }

    /**
     * @param $modelClassName
     * @param $modelManagerClassName
     */
    protected function registerManager($modelClassName, $modelManagerClassName) {

    }

    /**
     * @param $modelClassName
     * @return mixed
     */
    public function getManager($modelClassName) {
        if (!$this->initialized) {
            $this->initiailze();
            $this->initialized = true;
        }
        return $this->managers[$modelClassName];
    }

    /**
     * @return mixed
     */
    protected abstract function initialize ();
}