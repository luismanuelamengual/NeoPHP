<?php

namespace NeoPHP\Resources;

use NeoPHP\Module;
use NeoPHP\Routing\Routes;

class ResourcesWebModule extends Module {

    public function start() {
        $resourcesBaseContext = get_property("resources.base_context", "resources");
        Routes::any("$resourcesBaseContext/*", new ResourcesRouteGenerator());
    }
}