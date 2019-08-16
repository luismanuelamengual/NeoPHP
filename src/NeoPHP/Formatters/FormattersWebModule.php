<?php

namespace NeoPHP\Formatters;

use NeoPHP\Module;
use NeoPHP\Routing\Routes;

class FormattersWebModule extends Module {

    public function start() {
        Routes::afterGet("*", "NeoPHP\Formatters\FormattersController@formatOutput");
    }
}