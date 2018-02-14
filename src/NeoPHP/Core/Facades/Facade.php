<?php

namespace NeoPHP\Core\Facades;

use NeoPHP\Core\Application;
use RuntimeException;

abstract class Facade {

    private static $instance;

    private static function getInstance () {
        if (!isset(self::$instance)) {
            self::$instance = self::createInstance();
        }
        return self::$instance;
    }

    protected static function getFacadeClass () {
        throw new RuntimeException('Facade does not implement getFacadeClass method.');
    }

    protected static function createDefaultFacadeImplementation (){
        throw new RuntimeException('Facade does not implement createDefaultFacadeImplementation method.');
    }

    private static function createInstance () {
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
        return $instance;
    }

    public static function __callStatic($method, $args) {
        return self::getInstance()->$method(...$args);
    }
}