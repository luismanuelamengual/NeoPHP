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
     * @param $callbackOrAction
     */
    public static function register ($event, $callbackOrAction) {
        if (!array_key_exists($event, self::$listeners)) {
            self::$listeners[$event] = [];
        }
        self::$listeners[$event][] = $callbackOrAction;
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