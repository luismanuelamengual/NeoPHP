<?php

namespace NeoPHP\Core\Facades;

use NeoPHP\Core\Application;
use RuntimeException;

/**
 * Class Facade
 * @package NeoPHP\Core\Facades
 */
abstract class Facade {

    private static $instance;

    /**
     * @return mixed|null|void
     */
    private static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = self::createInstance();
        }
        return self::$instance;
    }

    /**
     * Get the interface class of the facade
     */
    protected static function getFacadeClass() {
        throw new RuntimeException('Facade does not implement getFacadeClass method.');
    }

    /**
     * Get the instance implementation of the facade
     */
    protected static function createDefaultFacadeImplementation() {
        throw new RuntimeException('Facade does not implement createDefaultFacadeImplementation method.');
    }

    /**
     * @return mixed|null|void
     */
    private static function createInstance() {
        $instance = null;
        $facadeClass = static::getFacadeClass();
        $facadeImplementation = Application::getFacadeImpl($facadeClass);
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
        if (!is_a($instance, $facadeClass)) {
            throw new RuntimeException("Facade instance must be subclass of $facadeClass !!");
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
}