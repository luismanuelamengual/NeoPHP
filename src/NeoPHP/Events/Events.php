<?php

namespace NeoPHP\Events;

/**
 * Class Events
 * @package NeoPHP\Events
 */
abstract class Events {

    private static $listeners = [];

    /**
     * @param $event
     * @param $action
     */
    public static function register ($event, $action) {
        if (!array_key_exists($event, self::$listeners)) {
            self::$listeners[$event] = [];
        }
        self::$listeners[$event][] = $action;
    }

    /**
     * @param $event
     * @param array $parameters
     * @throws \Exception
     */
    public static function fire ($event, array $parameters = []) {
        if (array_key_exists($event, self::$listeners)) {
            foreach (self::$listeners[$event] as $callbackOrAction) {
                get_app()->execute($callbackOrAction, $parameters);
            }
        }
    }
}