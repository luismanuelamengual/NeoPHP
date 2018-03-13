<?php

namespace NeoPHP\Resources;

/**
 * Class Resources
 * @package NeoPHP\Resources
 */
abstract class Resources {

    private static $managers = [];
    private static $defaultManager;

    /**
     * @param $resourceName
     * @return ResourceManagerProxy
     */
    public static function get($resourceName): ResourceManagerProxy {

        if (!isset(self::$managers[$resourceName])) {
            $resourceManager = null;
            $resourceManagers = get_property("resources.managers");
            if (isset($resourceManagers[$resourceName])) {
                $resourceManagerClass = $resourceManagers[$resourceName];
                if (!class_exists($resourceManagerClass)) {
                    throw new \RuntimeException("Class \"$resourceManagerClass\" was not found !!");
                }
                if (!is_subclass_of($resourceManagerClass, ResourceManager::class)) {
                    throw new \RuntimeException("Class \"$resourceManagerClass\" is not a subclass of ResourceManager !!");
                }
                $resourceManager = new $resourceManagerClass;
            }
            else {
                if (!isset(self::$defaultManager)) {
                    self::$defaultManager = new DefaultResourceManager();
                }
                $resourceManager = self::$defaultManager;
            }

            self::$managers[$resourceName] = $resourceManager;
        }
        return new ResourceManagerProxy(self::$managers[$resourceName], $resourceName);
    }
}