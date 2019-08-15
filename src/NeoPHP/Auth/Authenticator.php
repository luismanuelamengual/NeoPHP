<?php

namespace NeoPHP\Auth\Authenticator;

use stdClass;

abstract class Authenticator {

    public abstract function authenticate() : bool;

    public abstract function getTokenId();

    public abstract function getData() : stdClass;
}