<?php

namespace NeoPHP\Resources;

use NeoPHP\Utils\StringUtils;
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
                    $resourceManagerClass = trim($resourcesNamespace, "\\");
                    $resourceTokens = explode(".", $resourceName);
                    for ($i = 0; $i < sizeof($resourceTokens); $i++) {
                        $resourceToken = $resourceTokens[$i];
                        $resourceManagerClass .= "\\";
                        $resourceManagerClass .= str_replace(' ', '', ucwords(str_replace('_', ' ', $resourceToken)));
                    }
                    $resourceManagerClass .= get_property("resources.resources_suffix", "Resource");
                    if (class_exists($resourceManagerClass) && is_subclass_of($resourceManagerClass, ResourceManager::class)) {
                        $resourceManager = new $resourceManagerClass;
                    }
                }

                if ($resourceManager == null) {
                    $resourcesRemoteUrl = get_property("resources.remote_url");
                    if (!empty($resourcesRemoteUrl)) {
                        $resourceRemoteUrl = $resourcesRemoteUrl;
                        if (!StringUtils::endsWith($resourceRemoteUrl, '/')) {
                            $resourceRemoteUrl .= '/';
                        }
                        $resourcesBaseContext = get_property("resources.base_context", "resources");
                        $resourceRemoteUrl .= $resourcesBaseContext . '/';
                        $resourceRemoteUrl .= str_replace(".", "/", $resourceName);
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