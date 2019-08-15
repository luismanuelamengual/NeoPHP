<?php

namespace NeoPHP\Auth;

use NeoPHP\Auth\Authenticator\Authenticator;

abstract class Auth {

    private static $registeredAuthenticators = [];
    private static $activeAuthenticator;

    public static function registerAuthenticator (Authenticator $authenticator) {
        self::$registeredAuthenticators[] = $authenticator;
    }

    public static function unregisterAuthenticator (Authenticator $authenticator) {
        $authIndex = array_search($authenticator, self::$registeredAuthenticators);
        if ($authIndex) {
            array_splice(self::$registeredAuthenticators, $authIndex, 1);
        }
    }

    public static function getRegisteredAuthenticators() : array {
        return self::$registeredAuthenticators;
    }

    public static function getActiveAuthenticator() : Authenticator {
        return self::$activeAuthenticator;
    }

    public static function setActiveAuthenticator(Authenticator $activeAuthenticator): void {
        self::$activeAuthenticator = $activeAuthenticator;
    }

    public static function isAuthenticated() : bool {
        return self::$activeAuthenticator != null;
    }
}