<?php

namespace NeoPHP\Resources;

use NeoPHP\Module;
use NeoPHP\Routing\Routes;

class ResourcesWebModule extends Module {

    public function start() {
        Routes::any("*", new ResourcesRouteGenerator());
        $resourcesControllerClassName = ResourceController::class;
        Routes::post("resource", $resourcesControllerClassName . "@queryResources");
    }
}