<?php

namespace NeoPHP\mvc\views;

/**
 * Class BladeViewFactory
 * @package NeoPHP\mvc\views
 */
abstract class BladeViewFactory extends ViewFactory {

    public function createView($name, array $parameters = []): View {
        return new BladeView($this->application, $name, $parameters);
    }
}