<?php

namespace NeoPHP\mvc\views;

use NeoPHP\mvc\MVCApplication;

/**
 * Class ViewFactory
 * @package NeoPHP\mvc\views
 */
abstract class ViewFactory {

    protected $application;

    /**
     * ViewFactory constructor.
     * @param MVCApplication $application
     */
    public function __construct(MVCApplication $application) {
        $this->application = $application;
    }


    /**
     * @param $name string name of the view
     * @param array $parameters (optional) parameters to the view
     * @return View created View
     */
    public abstract function createView($name, array $parameters = []): View;
}