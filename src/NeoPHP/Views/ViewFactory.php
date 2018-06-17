<?php

namespace NeoPHP\Views;

/**
 * Class ViewFactory
 * @package Sitrack\mvc\views
 */
abstract class ViewFactory {

    /**
     * @param $name string name of the view
     * @param array $parameters (optional) parameters to the view
     * @return View created View
     */
    public abstract function create($name, array $parameters = []): View;
}