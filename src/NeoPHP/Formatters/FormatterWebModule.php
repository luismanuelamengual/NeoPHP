<?php

namespace NeoPHP\Formatters;

use NeoPHP\Module;
use NeoPHP\Routing\Routes;

class FormatterWebModule extends Module {

    public function start() {
        Routes::afterGet("*", "NeoPHP\Formatters\OutputController@formatOutput");
    }
}