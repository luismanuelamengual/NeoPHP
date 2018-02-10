<?php

namespace NeoPHP\Http;

use Exception;

final class Session {

    private static $instance;

    public static function getInstance() {
        if (!isset(self::$instance))
            self::$instance = new self;
        return self::$instance;
    }

    public function getId() {
        return session_id();
    }

    public function setId($id) {
        session_id($id);
    }

    public function getName() {
        return session_name();
    }

    public function setName($name) {
        session_name($name);
    }

    public function reset() {
        session_reset();
    }

    public function regenerateId($deleteOldSession = false) {
        session_regenerate_id($deleteOldSession);
    }

    public function start() {
        try {
            @session_start();
        }
        catch (Exception $ex) {
        }
    }

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

    public function getStatus() {
        return session_status();
    }

    public function isStarted() {
        return ($this->getStatus() == PHP_SESSION_ACTIVE);
    }

    public function set($name, $value) {
        $_SESSION[$name] = $value;
    }

    public function get($name = null) {
        return $name == null ? $_SESSION : $_SESSION[$name];
    }

    public function has($name) {
        return isset($_SESSION[$name]);
    }

    public function __set($name, $value) {
        $this->set($name, $value);
    }

    public function __get($name) {
        return $this->get($name);
    }

    public function __isset($name) {
        return $this->has($name);
    }
}