<?php

namespace NeoPHP\Core\Facades;

/**
 * Class Facade
 * @package NeoPHP\Core\Facades
 */
abstract class Facade {

    private static $instance;

    /**
     * @return mixed|null|void
     */
    protected static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = self::createInstance();
        }
        return self::$instance;
    }

    /**
     * @return mixed|null|void
     */
    private static function createInstance() {
        $instance = null;
        $facadeName = static::getFacadeName();
        $facadeImplementation = app()->getFacadeImpl($facadeName);
        if ($facadeImplementation != null) {
            if (is_callable($facadeImplementation)) {
                $instance = call_user_func($facadeImplementation);
            }
            else {
                $instance = new $facadeImplementation;
            }
        }
        else {
            $instance = static::createDefaultFacadeImplementation();
        }
        return $instance;
    }

    /**
     * @param $method
     * @param $args
     * @return mixed
     */
    public static function __callStatic($method, $args) {
        return self::getInstance()->$method(...$args);
    }

    /**
     * Get the interface class of the facade
     */
    protected abstract static function getFacadeName();

    /**
     * Get the instance implementation of the facade
     */
    protected abstract static function createDefaultFacadeImplementation();
}