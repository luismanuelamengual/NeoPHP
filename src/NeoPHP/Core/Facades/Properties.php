<?php

namespace NeoPHP\Core\Facades;

use NeoPHP\Config\FilePropertiesManager;
use NeoPHP\Config\PropertiesManager;
use NeoPHP\Core\Application;

/**
 * Class Properties
 * @package NeoPHP\Core\Facades
 */
abstract class Properties extends Facade {

    /**
     * @return string|void
     */
    protected static function getFacadeClass() {
        return PropertiesManager::class;
    }

    /**
     * @return FilePropertiesManager|void
     */
    protected static function createDefaultFacadeImplementation() {
        return new FilePropertiesManager(Application::getBasePath() . DIRECTORY_SEPARATOR . "config");
    }
}