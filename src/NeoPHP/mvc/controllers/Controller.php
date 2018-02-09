<?php

namespace NeoPHP\mvc\controllers;

use NeoPHP\app\ApplicationComponent;
use NeoPHP\mvc\ModelManager;

/**
 * Class Controller
 * @package NeoPHP\mvc\controllers
 */
abstract class Controller {

    protected $application;

    /**
     * Controller constructor.
     * @param MVCApplication $application
     */
    public function __construct(MVCApplication $application) {
        $this->application = $application;
    }
}