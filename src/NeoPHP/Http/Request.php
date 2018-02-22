<?php

namespace NeoPHP\Http;

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

    public static function getInstance() {
        if (!isset(self::$instance))
            self::$instance = new self;
        return self::$instance;
    }

    public function getParameters() {
        return $_REQUEST;
    }

    public function get($name) {
        return isset($_REQUEST[$name]) ? $_REQUEST[$name] : null;
    }

    public function set($name, $value) {
        $_REQUEST[$name] = $value;
    }

    public function has($name) {
        return isset($_REQUEST[$name]);
    }

    public function getHeaders() {
        return apache_request_headers();
    }

    public function getHeader($name) {
        $headers = $this->getHeaders();
        return $headers[$name];
    }

    public function getCookies() {
        return Cookies::getInstance();
    }

    public function getSession() {
        return Session::getInstance();
    }

    public function getServer() {
        return Server::getInstance();
    }

    public function getFiles() {
        return $_FILES;
    }

    public function getContent() {
        return @file_get_contents("php://input");
    }

    public function getMethod() {
        return $_SERVER["REQUEST_METHOD"];
    }

    public function isMethod($method) {
        return is_array($method) ? in_array($this->getMethod(), $method) : $this->getMethod() == $method;
    }

    public function isGet() {
        return $this->isMethod(self::METHOD_GET);
    }

    public function isPost() {
        return $this->isMethod(self::METHOD_POST);
    }

    public function isPut() {
        return $this->isMethod(self::METHOD_PUT);
    }

    public function isDelete() {
        return $this->isMethod(self::METHOD_DELETE);
    }

    public function getScheme() {
        return $this->isSecureRequest() ? "https" : "http";
    }

    public function isAjax() {
        return $_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest";
    }

    public function isSecureRequest() {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
    }

    public function getServerAddress() {
        return $_SERVER["SERVER_ADDR"];
    }

    public function getServerName() {
        return $_SERVER["SERVER_NAME"];
    }

    public function getHttpHost() {
        return $_SERVER["HTTP_HOST"];
    }

    public function getClientAddress($trustForwardedHeader = false) {
        return $trustForwardedHeader === true ? $_SERVER["HTTP_X_FORWARDED_FOR"] : $_SERVER["REMOTE_ADDR"];
    }

    public function getUri() {
        return $_SERVER["REQUEST_URI"];
    }

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

    public function getPathParts() {
        if (!isset($this->pathParts)) {
            $this->pathParts = explode(PATH_SEPARATOR, $this->getPath());
        }
        return $this->pathParts;
    }

    public function getUserAgent() {
        return $_SERVER["HTTP_USER_AGENT"];
    }
}