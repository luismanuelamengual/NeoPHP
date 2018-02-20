<?php

namespace NeoPHP\Core\Resources;

/**
 * Class Resources
 * @package NeoPHP\Core\Resources
 */
abstract class Resources {

    /**
     * @param $resourceName
     * @return ResourceManager
     */
    public static function get($resourceName): ResourceManager {
        $resourceManager = null;
        $resourceManagers = getProperty("resources.managers");
        if (isset($resourceManagers[$resourceName])) {
            $resourceManagerClass = $resourceManagers[$resourceName];
            if (!is_subclass_of($resourceManagerClass, ResourceManager::class)) {
                throw new \RuntimeException("Class \"$resourceManagerClass\" is not a subclass of ResourceManager !!");
            }
            $resourceManager = new $resourceManagerClass;
        }
        else {
            $resourceManager = new DefaultResourceManager($resourceName);
        }
        return $resourceManager;
    }
}