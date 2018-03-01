<?php

namespace NeoPHP\Http;

/**
 * Class Response
 * @package NeoPHP\Http
 */
final class Response {

    private static $instance;

    /**
     * Request constructor.
     */
    private function __construct() {
    }

    /**
     * @return Response
     */
    public static function instance() {
        if (!isset(self::$instance))
            self::$instance = new self;
        return self::$instance;
    }

    /**
     * @param null $statusCode
     * @return int
     */
    public function statusCode($statusCode = null) {
        if ($statusCode != null) {
            http_response_code($statusCode);
        }
        else {
            return http_response_code();
        }
    }

    /**
     * @return array
     */
    public function headers() {
        return headers_list();
    }

    /**
     * @param $name
     * @param null $value
     */
    public function addHeader($name, $value=null) {
        if (isset($value)) {
            header($name . ": " . $value);
        }
        else {
            header($name);
        }
    }

    /**
     * @param $name
     * @param null $value
     * @param null $expire
     * @param null $path
     * @param null $domain
     * @param bool $secure
     * @param bool $httponly
     */
    public function addCookie($name, $value = null, $expire = null, $path = null, $domain = null, $secure = false, $httponly = false) {
        setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
    }

    /**
     * @return bool
     */
    public function sent() {
        return headers_sent();
    }
}