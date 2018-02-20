<?php

namespace NeoPHP\Config;

/**
 * Class Properties
 * @package NeoPHP\Config
 */
abstract class Properties {

    private static $properties = [];

    /**
     * @param $key
     * @param null $defaultValue
     * @return array|mixed|null
     */
    public static function get($key, $defaultValue = null) {
        $keyTokens = explode(".", $key);
        if (!isset(self::$properties[$keyTokens[0]])) {
            self::loadPropertiesModule($keyTokens[0]);
        }
        $propertyValue = self::$properties;
        foreach ($keyTokens as $keyToken) {
            if (isset($propertyValue[$keyToken])) {
                $propertyValue = $propertyValue[$keyToken];
            }
            else {
                $propertyValue = null;
                break;
            }
        }
        return $propertyValue == null? $defaultValue : $propertyValue;
    }

    /**
     * @param $key
     * @param $value
     */
    public static function set($key, $value) {
        $keyTokens = explode(".", $key);
        $propertyKey = &self::$properties;
        foreach ($keyTokens as $keyToken) {
            if (!isset($propertyKey[$keyToken])) {
                $propertyKey[$keyToken] = [];
            }
            $propertyKey = &$propertyKey[$keyToken];
        }
        $propertyKey = $value;
    }

    /**
     * @param $moduleName
     */
    private static function loadPropertiesModule($moduleName) {
        $moduleFileName = getApp()->getBasePath() . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . $moduleName . ".php";
        if (file_exists($moduleFileName)) {
            self::$properties[$moduleName] = @include_once($moduleFileName);
        }
    }
}