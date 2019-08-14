<?php

namespace NeoPHP\Nodes;

use NeoPHP\Resources\RemoteResourceManager;
use NeoPHP\Resources\ResourceManager;
use NeoPHP\Utils\Strings;

class RemoteNode extends Node {

    private $endpoint;

    public function __construct($endpoint) {
        if (!Strings::endsWith($endpoint,"/")) {
            $endpoint .= "/";
        }
        $this->endpoint = $endpoint;
    }

    protected function createResourceManager($resourceName): ResourceManager {
        $remoteUrl = $this->endpoint;
        if (!Strings::endsWith($remoteUrl, '/')) {
            $remoteUrl .= '/';
        }
        $remoteUrl .= str_replace(".", "/", $resourceName);
        $resourceManager = new RemoteResourceManager();
        $resourceManager->setRemoteUrl($remoteUrl);
        return $resourceManager;
    }
}