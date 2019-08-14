<?php

namespace NeoPHP\Nodes;

use NeoPHP\Resources\ResourceManager;
use NeoPHP\Resources\ResourceManagerProxy;

abstract class Node {

    private $resourceManagers = [];

    public function get(string $resourceName) : ResourceManagerProxy {
        if (!isset($this->resourceManagers[$resourceName])) {
            $this->resourceManagers[$resourceName] = $this->createResourceManager($resourceName);
        }
        return new ResourceManagerProxy($this->resourceManagers[$resourceName], $resourceName);
    }

    protected abstract function createResourceManager(string $resourceName): ResourceManager;
}