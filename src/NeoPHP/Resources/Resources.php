<?php

namespace NeoPHP\Resources;

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
        $resourceManagers = get_property("resources.managers");
        if (isset($resourceManagers[$resourceName])) {
            $resourceManagerConfig = $resourceManagers[$resourceName];
            if (!is_array($resourceManagerConfig)) {
                $resourceManagerConfig = ["class"=>$resourceManagerConfig];
            }
            if (!isset($resourceManagerConfig["class"])) {
                $resourceManagerConfig["class"] = ConnectionResourceManager::class;
            }
            $resourceManagerClass = $resourceManagerConfig["class"];
            if (!class_exists($resourceManagerClass)) {
                throw new \RuntimeException("Class \"$resourceManagerClass\" was not found !!");
            }
            if (!is_subclass_of($resourceManagerClass, ResourceManager::class)) {
                throw new \RuntimeException("Class \"$resourceManagerClass\" is not a subclass of ResourceManager !!");
            }
            $resourceManager = new $resourceManagerClass(array_merge(["resourceName"=>$resourceName], $resourceManagerConfig));
        }
        else {
            $resourceManager = new ConnectionResourceManager(["resourceName"=>$resourceName]);
        }
        return $resourceManager;
    }
}