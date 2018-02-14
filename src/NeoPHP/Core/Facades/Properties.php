<?php

namespace NeoPHP\Core\Facades;

use NeoPHP\Config\FilePropertiesManager;
use NeoPHP\Config\PropertiesManager;
use NeoPHP\Core\Application;

abstract class Properties extends Facade {

    protected static function getFacadeClass() {
        return PropertiesManager::class;
    }

    protected static function createDefaultFacadeImplementation() {
        return new FilePropertiesManager(Application::getBasePath() . DIRECTORY_SEPARATOR . "config");
    }
}