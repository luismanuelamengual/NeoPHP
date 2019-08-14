<?php

namespace NeoPHP\Nodes;

use NeoPHP\Resources\DefaultResourceManager;
use NeoPHP\Resources\ResourceManager;

class LocalNode extends Node {

    private $defaultManager = null;

    protected function createResourceManager(string $resourceName): ResourceManager {
        $resourcesNamespace = get_property("resources.base_namespace", "NeoPHP\Resources");
        $resourceManager = null;
        if (isset($resourcesNamespace)) {
            $resourceManagerClass = $resourcesNamespace;
            $resourceTokens = explode(".", $resourceName);
            for ($i = 0; $i < sizeof($resourceTokens); $i++) {
                $resourceToken = $resourceTokens[$i];
                $resourceManagerClass .= "\\";
                $resourceManagerClass .= str_replace(' ', '', ucwords(str_replace('_', ' ', $resourceToken)));
            }
            $resourceManagerClass .= "Resource";
            if (class_exists($resourceManagerClass) && is_subclass_of($resourceManagerClass, ResourceManager::class)) {
                $resourceManager = new $resourceManagerClass;
            }
        }

        if ($resourceManager == null) {
            if (!isset($this->defaultManager)) {
                $this->defaultManager = new DefaultResourceManager();
            }
            $resourceManager = &$this->defaultManager;
        }
        return $resourceManager;
    }
}