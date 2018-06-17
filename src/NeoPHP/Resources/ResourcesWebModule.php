<?php

namespace NeoPHP\Resources;

use NeoPHP\Module;
use NeoPHP\Routing\Routes;

class ResourcesWebModule extends Module {

    public function start() {
        Routes::get("resources/:resourceName", "Sitrack\Resources\ResourceController@findResources");
        Routes::put("resources/:resourceName", "Sitrack\Resources\ResourceController@insertResource");
        Routes::post("resources/:resourceName", "Sitrack\Resources\ResourceController@updateResource");
        Routes::delete("resources/:resourceName", "Sitrack\Resources\ResourceController@deleteResource");
    }
}