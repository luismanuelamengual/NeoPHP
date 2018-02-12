<?php

namespace NeoPHP\Core;

use ErrorException;
use NeoPHP\Routing\RouteNotFoundException;

/**
 * Class Application
 * @package NeoPHP\Core
 */
abstract class Application {

    private static $basePath;
    private static $configPath;

    /**
     * @param $basePath
     */
    public static function init($basePath) {
        self::$basePath = $basePath;
        self::$configPath = self::$basePath . DIRECTORY_SEPARATOR . "config";
        $whoops = new \Whoops\Run;
        $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
        $whoops->register();
    }

    /**
     * @return mixed
     */
    public static function getBasePath() {
        return self::$basePath;
    }

    /**
     * Returns the configurations path
     */
    public static function getConfigPath() {
        return self::$configPath;
    }

    /**
     * @param $errno
     * @param $errstr
     * @param $errfile
     * @param $errline
     * @param $errcontext
     * @throws ErrorException
     */
    public static function handleError($errno , $errstr, $errfile, $errline, $errcontext) {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    /**
     * @param $ex
     */
    public static function handleException($ex) {
        if (php_sapi_name() === 'cli') {
            echo "ERROR: " . $ex->getMessage();
        }
        else {
            if ($ex instanceof RouteNotFoundException) {
                http_response_code(404);
            }
            else {
                http_response_code(500);
            }
            echo "ERROR: " . $ex->getMessage();
            echo "<pre>";
            echo "## " . $ex->getFile() . "(" . $ex->getLine() . ")<br>";
            echo print_r($ex->getTraceAsString(), true);
            echo "</pre>";
        }
    }
}