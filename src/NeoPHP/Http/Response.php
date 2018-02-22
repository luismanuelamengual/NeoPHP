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
    public static function getInstance() {
        if (!isset(self::$instance))
            self::$instance = new self;
        return self::$instance;
    }

    /**
     * @param $statusCode
     */
    public function setStatusCode($statusCode) {
        http_response_code($statusCode);
    }

    /**
     * @return int
     */
    public function getStatusCode() {
        return http_response_code();
    }

    /**
     * @return array
     */
    public function getHeaders() {
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
    public function isSent() {
        return headers_sent();
    }
}