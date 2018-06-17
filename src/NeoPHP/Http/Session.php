<?php

namespace NeoPHP\Http;

use Exception;

/**
 * Class Session
 * @package NeoPHP\Http
 */
final class Session {

    private static $instance;

    /**
     * @return Session
     */
    public static function instance(): Session {
        if (!isset(self::$instance))
            self::$instance = new self;
        return self::$instance;
    }

    /**
     * @return string
     */
    public function id($id = null) {
        if (isset($id)) {
            session_id($id);
        }
        else {
            return session_id();
        }
    }

    /**
     * @return string
     */
    public function name($name = null) {
        if (isset($name)) {
            session_name($name);
        }
        else {
            return session_name();
        }
    }

    /**
     *
     */
    public function reset() {
        session_reset();
    }

    /**
     * @param bool $deleteOldSession
     */
    public function regenerateId($deleteOldSession = false) {
        session_regenerate_id($deleteOldSession);
    }

    /**
     *
     */
    public function start() {
        try {
            @session_start();
        }
        catch (Exception $ex) {
        }
    }

    /**
     *
     */
    public function clear () {
        $_SESSION = [];
    }

    /**
     *
     */
    public function closeWrite () {
        @session_write_close();
    }

    /**
     *
     */
    public function destroy() {
        $this->clear();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }

        try {
            @session_destroy();
        }
        catch (Exception $ex) {
        }
    }

    /**
     * @return int
     */
    public function status() {
        return session_status();
    }

    /**
     * @return bool
     */
    public function isStarted() {
        return ($this->status() == PHP_SESSION_ACTIVE);
    }

    /**
     * @param $name
     * @param $value
     */
    public function set($name, $value) {
        $_SESSION[$name] = $value;
    }

    /**
     * @param null $name
     * @return mixed
     */
    public function get($name = null, $defaultValue = null) {
        return $name == null ? $_SESSION : (isset($_SESSION[$name])? $_SESSION[$name] : $defaultValue);
    }

    /**
     * @param $name
     * @return bool
     */
    public function has($name) {
        return isset($_SESSION[$name]);
    }
}