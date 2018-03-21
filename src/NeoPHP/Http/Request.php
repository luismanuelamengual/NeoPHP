<?php

namespace NeoPHP\Http;

/**
 * Class Request
 * @package NeoPHP\Http
 */
final class Request {

    const PATH_SEPARATOR = "/";

    const METHOD_GET = "GET";
    const METHOD_PUT = "PUT";
    const METHOD_POST = "POST";
    const METHOD_DELETE = "DELETE";
    const METHOD_HEAD = "HEAD";
    const METHOD_OPTIONS = "OPTIONS";
    const METHOD_PATCH = "PATCH";

    private static $instance;

    /**
     * Request constructor.
     */
    private function __construct() {
    }

    /**
     * @return Request
     */
    public static function instance(): Request {
        if (!isset(self::$instance))
            self::$instance = new self;
        return self::$instance;
    }

    /**
     * @return mixed
     */
    public function host() {
        return $_SERVER["HTTP_HOST"];
    }

    /**
     * @return mixed
     */
    public function uri() {
        return $_SERVER["REQUEST_URI"];
    }

    /**
     * @return mixed
     */
    public function query() {
        return isset($_SERVER["QUERY_STRING"])? $_SERVER["QUERY_STRING"] : null;
    }

    /**
     * @param bool $trustForwardedHeader
     * @return mixed
     */
    public function clientAddress($trustForwardedHeader = false) {
        return $trustForwardedHeader === true ? $_SERVER["HTTP_X_FORWARDED_FOR"] : $_SERVER["REMOTE_ADDR"];
    }

    /**
     * @return mixed
     */
    public function userAgent() {
        return $_SERVER["HTTP_USER_AGENT"];
    }

    /**
     * Returns the base context
     */
    public function baseContext() {
        return !empty($_SERVER["CONTEXT_PREFIX"])? $_SERVER["CONTEXT_PREFIX"] : "";
    }

    /**
     * @return string
     */
    public function path() {
        if (!isset($this->path)) {
            $this->path = "";
            if (!empty($_SERVER["REQUEST_URI"])) {
                $this->path = $_SERVER["REQUEST_URI"];

                $queryPos = strpos($this->path, "?");
                if ($queryPos !== false) {
                    $this->path = substr($this->path, 0, $queryPos);
                }

                if (!empty($_SERVER["CONTEXT_PREFIX"])) {
                    $this->path = substr($this->path, strlen($_SERVER["CONTEXT_PREFIX"]));
                }
            }
            $this->path = trim($this->path, PATH_SEPARATOR);
        }
        return $this->path;
    }

    /**
     * @return array
     */
    public function pathParts() {
        if (!isset($this->pathParts)) {
            $path = $this->path();
            $this->pathParts = !empty($path)? explode(PATH_SEPARATOR, $this->path()) : [];
        }
        return $this->pathParts;
    }

    /**
     * @return mixed
     */
    public function params() {
        return $_REQUEST;
    }

    /**
     * @param $name
     * @return null
     */
    public function get($name) {
        return isset($_REQUEST[$name]) ? $_REQUEST[$name] : null;
    }

    /**
     * @param $name
     * @param $value
     */
    public function set($name, $value) {
        $_REQUEST[$name] = $value;
    }

    /**
     * @param $name
     * @return bool
     */
    public function has($name) {
        return isset($_REQUEST[$name]);
    }

    /**
     * @return array|false
     */
    public function headers() {
        return getallheaders();
    }

    /**
     * @param $name
     * @return mixed
     */
    public function header($name) {
        $headers = $this->headers();
        return $headers[$name];
    }

    /**
     * @return mixed
     */
    public function cookies() {
        return $_COOKIE;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function cookie($name) {
        return $_COOKIE[$name];
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasCookie($name) {
        return isset($_COOKIE[$name]);
    }

    /**
     * @return Session
     */
    public function session() {
        return Session::instance();
    }

    /**
     * @return mixed
     */
    public function files() {
        return $_FILES;
    }

    /**
     * @return bool|string
     */
    public function content() {
        return @file_get_contents("php://input");
    }

    /**
     * @return mixed
     */
    public function method() {
        return $_SERVER["REQUEST_METHOD"];
    }

    /**
     * @return string
     */
    public function scheme() {
        return isset($_SERVER["REQUEST_SCHEME"])? $_SERVER["REQUEST_SCHEME"] : ($this->isSecureRequest() ? "https" : "http");
    }

    /**
     * @return bool
     */
    public function ajax() {
        return isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && ($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest");
    }

    /**
     * @return bool
     */
    public function isSecureRequest() {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
    }
}