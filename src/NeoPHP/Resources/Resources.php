<?php

namespace NeoPHP\Resources;

use NeoPHP\Utils\Strings;
use RuntimeException;

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
            if (isset($resourceManagers) && isset($resourceManagers[$resourceName])) {
                $resourceManagerConfig = $resourceManagers[$resourceName];
                if (is_array($resourceManagerConfig)) {
                    $resourceManagerClass = $resourceManagerConfig["class"];
                    unset($resourceManagerConfig["class"]);
                }
                else {
                    $resourceManagerClass = $resourceManagerConfig;
                    $resourceManagerConfig = [];
                }
                if (!class_exists($resourceManagerClass)) {
                    throw new RuntimeException("Class \"$resourceManagerClass\" was not found !!");
                }
                if (!is_subclass_of($resourceManagerClass, ResourceManager::class)) {
                    throw new RuntimeException("Class \"$resourceManagerClass\" is not a subclass of ResourceManager !!");
                }
                $resourceManager = new $resourceManagerClass;
                foreach ($resourceManagerConfig as $property => $value) {
                    $propertySetterMethod = "set" . ucfirst($property);
                    $resourceManager->$propertySetterMethod($value);
                }
            }
            else {
                $resourcesNamespace = get_property("resources.base_namespace");
                if (isset($resourcesNamespace)) {
                    $resourceManagerClass = $resourcesNamespace . "\\" .  ucfirst($resourceName) . "Resource";
                    if (class_exists($resourceManagerClass) && is_subclass_of($resourceManagerClass, ResourceManager::class)) {
                        $resourceManager = new $resourceManagerClass;
                    }
                }

                if ($resourceManager == null) {
                    $resourcesRemoteUrl = get_property("resources.remoteUrl");
                    if (!empty($resourcesRemoteUrl)) {
                        $resourceRemoteUrl = $resourcesRemoteUrl;
                        if (!Strings::endsWith($resourceRemoteUrl, '/')) {
                            $resourceRemoteUrl .= '/';
                        }
                        $resourceRemoteUrl .= 'resources/';
                        $resourceRemoteUrl .= $resourceName;
                        $resourceManager = new RemoteResourceManager();
                        $resourceManager->setRemoteUrl($resourceRemoteUrl);
                    }
                    else {
                        if (!isset(self::$defaultManager)) {
                            self::$defaultManager = new DefaultResourceManager();
                        }
                        $resourceManager = &self::$defaultManager;
                    }
                }
            }

            self::$managers[$resourceName] = $resourceManager;
        }
        return new ResourceManagerProxy(self::$managers[$resourceName], $resourceName);
    }
}