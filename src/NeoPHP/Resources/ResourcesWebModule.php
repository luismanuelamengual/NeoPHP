<?php

namespace NeoPHP\Resources;

use NeoPHP\Module;
use NeoPHP\Routing\Routes;

class ResourcesWebModule extends Module {

    public function start() {
        Routes::get("resources/:resourceName", "NeoPHP\Resources\ResourceController@findResources");
        Routes::put("resources/:resourceName", "NeoPHP\Resources\ResourceController@insertResource");
        Routes::post("resources/:resourceName", "NeoPHP\Resources\ResourceController@updateResource");
        Routes::delete("resources/:resourceName", "NeoPHP\Resources\ResourceController@deleteResource");
    }
}