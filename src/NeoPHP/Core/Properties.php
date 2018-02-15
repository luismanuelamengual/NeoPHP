<?php

namespace NeoPHP\Core;

use NeoPHP\Config\FilePropertiesManager;

/**
 * Class Properties
 * @package NeoPHP\Core\Facades
 */
abstract class Properties {

    private static $instance;

    /**
     * @return FilePropertiesManager
     */
    private static function getInstance () {
        if (!isset(self::$instance)) {
            self::$instance = new FilePropertiesManager(app()->getBasePath() . DIRECTORY_SEPARATOR . "config");
        }
        return self::$instance;
    }

    /**
     * @param $key
     * @param null $defaultValue
     * @return array|mixed|null
     */
    public static function get($key, $defaultValue = null) {
        return self::getInstance()->get($key, $defaultValue);
    }

    /**
     * @param $key
     * @param $value
     */
    public static function set($key, $value) {
        self::getInstance()->set($key, $value);
    }
}