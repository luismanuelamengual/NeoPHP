<?php

namespace NeoPHP\Core\Facades;

use NeoPHP\Config\FilePropertiesManager;

/**
 * Class Properties
 * @package NeoPHP\Core\Facades
 */
abstract class Properties extends Facade {

    /**
     * @return string|void
     */
    protected static function getFacadeName() {
        return "properties";
    }

    /**
     * @return FilePropertiesManager|void
     */
    protected static function createDefaultFacadeImplementation() {
        return new FilePropertiesManager(app()->getBasePath() . DIRECTORY_SEPARATOR . "config");
    }
}