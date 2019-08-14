<?php

namespace NeoPHP\Controllers;

use NeoPHP\Module;
use NeoPHP\Routing\Routes;

class ControllersWebModule extends Module {

    public function start() {
        $baseNamespaces = get_property("controllers.base_namespace");
        if (!empty($baseNamespaces)) {
            if (is_array($baseNamespaces)) {
                foreach ($baseNamespaces as $baseNamespace) {
                    Routes::controllers("*", $baseNamespace);
                }
            }
            else {
                Routes::controllers("*", $baseNamespaces);
            }
        }
    }
}