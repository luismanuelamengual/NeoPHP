<?php

namespace NeoPHP\Core;

use stdClass;

/**
 * Class System
 * @package NeoPHP\Core
 */
abstract class System {

    const PROPERTIES_FILENAME = "properties";

    private static $basePath;
    private static $properties;

    /**
     * @param $basePath
     */
    public static function init($basePath) {
        self::$basePath = $basePath;
    }

    /**
     * @return mixed
     */
    public static function getBasePath() {
        return self::$basePath;
    }

    /**
     * @param $name
     * @param $value
     */
    public static function setProperty($name, $value) {
        if (!isset(self::$properties)) {
            self::$properties = self::createProperties();
        }
        self::$properties[$name] = $value;
    }

    /**
     * @param $name
     * @return mixed
     */
    public static function getProperty($name) {
        if (!isset(self::$properties)) {
            self::$properties = self::createProperties();
        }
        return self::$properties[$name];
    }

    /**
     * Creates the system properties
     */
    private static function createProperties() {
        $properties = null;
        $filename = self::getBasePath() . DIRECTORY_SEPARATOR . self::PROPERTIES_FILENAME . ".php";
        if (file_exists($filename)) {
            $properties = include $filename;
        }
        else {
            $filename = self::getBasePath() . DIRECTORY_SEPARATOR . self::PROPERTIES_FILENAME . ".ini";
            if (file_exists($filename)) {
                $propertiesArray = @parse_ini_file($filename);
                $properties = new stdclass;
                $prev = null;
                foreach ($propertiesArray as $key => $value) {
                    $c = $properties;
                    foreach (explode(".", $key) as $key) {
                        if (!isset($c->$key))
                            $c->$key = new stdClass;
                        $prev = $c;
                        $c = $c->$key;
                    }
                    $prev->$key = $value;
                }
            }
            else {
                $filename = self::getBasePath() . DIRECTORY_SEPARATOR . self::PROPERTIES_FILENAME . ".json";
                if (file_exists($filename)) {
                    $properties = json_decode(@file_get_contents($filename));
                }
                else {
                    $properties = new stdClass();
                }
            }
        }
        return $properties;
    }
}