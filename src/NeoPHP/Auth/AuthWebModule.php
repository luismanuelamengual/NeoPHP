<?php

namespace NeoPHP\Auth;

use NeoPHP\Module;
use NeoPHP\Routing\Routes;

class AuthWebModule extends Module {

    public function start() {
        Routes::before("*", get_property("auth.action", "NeoPHP\Auth\AuthController@checkAction"));
    }
}