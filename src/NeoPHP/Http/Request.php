<?php

namespace NeoPHP\Http;

/**
 * Class Request
 * @package Sitrack\Http
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
        $baseContext = get_property("web.base_context", "");
        if (empty($baseContext)) {
            $baseContext = !empty($_SERVER["CONTEXT_PREFIX"])? $_SERVER["CONTEXT_PREFIX"] : "";
        }
        return $baseContext;
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

                $baseContext = $this->baseContext();
                if (!empty($baseContext)) {
                    $baseContextLength = strlen($baseContext);
                    if (substr($this->path, 0, $baseContextLength) == $baseContext) {
                        $this->path = substr($this->path, $baseContextLength);
                    }
                }
            }
        }
        return $this->path;
    }

    /**
     * @return array
     */
    public function pathParts() {
        if (!isset($this->pathParts)) {
            $path = $this->path();
            if (!empty($path)) {
                $this->pathParts = explode(self::PATH_SEPARATOR, $path);
                if (!empty($this->pathParts) && empty($this->pathParts[0])) {
                    $this->pathParts = array_slice($this->pathParts, 1);
                }
            }
            else {
                $this->pathParts = [];
            }
        }
        return $this->pathParts;
    }

    /**
     * @return mixed
     */
    public function &params() {
        if (!isset($this->parameters)) {
            $this->parameters = &$_REQUEST;
            if ($this->method() != self::METHOD_POST) {
                $contentTypeHeader = $this->header('Content-Type');
                if (!empty($contentTypeHeader) && strpos($contentTypeHeader, 'application/x-www-form-urlencoded') !== false) {
                    $content = $this->content();
                    if (!empty($content)) {
                        $contentTokens = explode('&', $content);
                        foreach ($contentTokens as $contentToken) {
                            $contentTokenParts = explode('=', $contentToken);
                            $key = $contentTokenParts[0];
                            $value = urldecode($contentTokenParts[1]);
                            $this->parameters[$key] = $value;
                        }
                    }
                }
            }
        }
        return $this->parameters;
    }

    /**
     * @param $name
     * @return null
     */
    public function get($name, $defaultValue=null) {
        $params = &$this->params();
        return isset($params[$name]) ? $params[$name] : $defaultValue;
    }

    /**
     * @param $name
     * @param $value
     */
    public function set($name, $value) {
        $params = &$this->params();
        $params[$name] = $value;
    }

    /**
     * @param $name
     * @return bool
     */
    public function has($name) {
        $params = &$this->params();
        return isset($params[$name]);
    }

    /**
     * @return array|false
     */
    public function &headers() {
        if (!isset($this->headers)) {
            if (function_exists('getallheaders')) {
                $this->headers = getallheaders();
            }
            else {
                $this->headers = [];
                if (is_array($_SERVER)) {
                    foreach ($_SERVER as $name => $value) {
                        if (substr($name, 0, 5) == 'HTTP_') {
                            $this->headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                        }
                    }
                }
            }
        }
        return $this->headers;
    }

    /**
     * @param $name
     * @param string $defaultValue
     * @return mixed
     */
    public function header($name, $defaultValue='') {
        $headers = &$this->headers();
        return isset($headers[$name])? $headers[$name] : $defaultValue;
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
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            $scheme = $_SERVER['HTTP_X_FORWARDED_PROTO'];
        }
        else if (isset($_SERVER["REQUEST_SCHEME"])) {
            $scheme = $_SERVER["REQUEST_SCHEME"];
        }
        else {
            $scheme = $this->isSecureRequest() ? "https" : "http";
        }
        return $scheme;
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