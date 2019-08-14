<?php

namespace NeoPHP\Auth;

use NeoPHP\Auth\Authenticator\Authenticator;

abstract class Auth {

    private static $authenticator;

    public static function getAuthenticator() : Authenticator {
        return self::$authenticator;
    }

    public static function setAuthenticator(Authenticator $authenticator): void {
        self::$authenticator = $authenticator;
    }

    public static function isAuthenticated(): bool {
        return !is_null(self::$authenticator);
    }
}