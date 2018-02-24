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
    public static function getInstance() {
        if (!isset(self::$instance))
            self::$instance = new self;
        return self::$instance;
    }

    /**
     * @return mixed
     */
    public function getHost() {
        return $_SERVER["HTTP_HOST"];
    }

    /**
     * @return mixed
     */
    public function getUri() {
        return $_SERVER["REQUEST_URI"];
    }

    /**
     * @return mixed
     */
    public function getQuery() {
        return $_SERVER["REDIRECT_QUERY_STRING"];
    }

    /**
     * @param bool $trustForwardedHeader
     * @return mixed
     */
    public function getClientAddress($trustForwardedHeader = false) {
        return $trustForwardedHeader === true ? $_SERVER["HTTP_X_FORWARDED_FOR"] : $_SERVER["REMOTE_ADDR"];
    }

    /**
     * @return mixed
     */
    public function getUserAgent() {
        return $_SERVER["HTTP_USER_AGENT"];
    }

    /**
     * @return string
     */
    public function getPath() {
        if (!isset($this->path)) {
            $this->path = "";
            if (!empty($_SERVER["REDIRECT_URL"])) {
                $this->path = $_SERVER["REDIRECT_URL"];
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
    public function getPathParts() {
        if (!isset($this->pathParts)) {
            $path = $this->getPath();
            $this->pathParts = !empty($path)? explode(PATH_SEPARATOR, $this->getPath()) : [];
        }
        return $this->pathParts;
    }

    /**
     * @return mixed
     */
    public function getParameters() {
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
    public function getHeaders() {
        return apache_request_headers();
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getHeader($name) {
        $headers = $this->getHeaders();
        return $headers[$name];
    }

    /**
     * @return Cookies
     */
    public function getCookies() {
        return $_COOKIE;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getCookie($name) {
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
    public function getSession() {
        return Session::getInstance();
    }

    /**
     * @return mixed
     */
    public function getFiles() {
        return $_FILES;
    }

    /**
     * @return bool|string
     */
    public function getContent() {
        return @file_get_contents("php://input");
    }

    /**
     * @return mixed
     */
    public function getMethod() {
        return $_SERVER["REQUEST_METHOD"];
    }

    /**
     * @param $method
     * @return bool
     */
    public function isMethod($method) {
        return is_array($method) ? in_array($this->getMethod(), $method) : $this->getMethod() == $method;
    }

    /**
     * @return bool
     */
    public function isGet() {
        return $this->isMethod(self::METHOD_GET);
    }

    /**
     * @return bool
     */
    public function isPost() {
        return $this->isMethod(self::METHOD_POST);
    }

    /**
     * @return bool
     */
    public function isPut() {
        return $this->isMethod(self::METHOD_PUT);
    }

    /**
     * @return bool
     */
    public function isDelete() {
        return $this->isMethod(self::METHOD_DELETE);
    }

    /**
     * @return string
     */
    public function getScheme() {
        return $this->isSecureRequest() ? "https" : "http";
    }

    /**
     * @return bool
     */
    public function isAjax() {
        return $_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest";
    }

    /**
     * @return bool
     */
    public function isSecureRequest() {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
    }
}