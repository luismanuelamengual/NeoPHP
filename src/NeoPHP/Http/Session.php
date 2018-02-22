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
    public static function getInstance() {
        if (!isset(self::$instance))
            self::$instance = new self;
        return self::$instance;
    }

    /**
     * @return string
     */
    public function getId() {
        return session_id();
    }

    /**
     * @param $id
     */
    public function setId($id) {
        session_id($id);
    }

    /**
     * @return string
     */
    public function getName() {
        return session_name();
    }

    /**
     * @param $name
     */
    public function setName($name) {
        session_name($name);
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
    public function destroy() {
        $_SESSION = [];

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
    public function getStatus() {
        return session_status();
    }

    /**
     * @return bool
     */
    public function isStarted() {
        return ($this->getStatus() == PHP_SESSION_ACTIVE);
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
    public function get($name = null) {
        return $name == null ? $_SESSION : $_SESSION[$name];
    }

    /**
     * @param $name
     * @return bool
     */
    public function has($name) {
        return isset($_SESSION[$name]);
    }
}