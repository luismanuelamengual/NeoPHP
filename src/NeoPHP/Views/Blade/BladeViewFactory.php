<?php

namespace NeoPHP\Views\Blade;

use NeoPHP\Views\View;
use NeoPHP\Views\ViewFactory;

/**
 * Class BladeViewFactory
 * @package NeoPHP\mvc\views
 */
class BladeViewFactory extends ViewFactory {

    public function createView($name, array $parameters = []): View {
        $templatesPath = $this->has("templatesPath")? $this->get("templatesPath") : getApp()->getResourcesPath() . DIRECTORY_SEPARATOR . "views";
        $compiledTemplatesPath = $this->has("compiledTemplatesPath")? $this->get("compiledTemplatesPath") : getApp()->getStoragePath() . DIRECTORY_SEPARATOR . "framework" . DIRECTORY_SEPARATOR . "views";
        return new BladeView($templatesPath, $compiledTemplatesPath, $name, $parameters);
    }
}