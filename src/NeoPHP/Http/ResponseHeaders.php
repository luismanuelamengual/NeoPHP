<?php

namespace NeoPHP\Http;

class ResponseHeaders {

    private static $instance;
    private $statusCode;
    private $headers;
    private $cookies;

    public static function getInstance() {
        if (!isset(self::$instance))
            self::$instance = new self;
        return self::$instance;
    }

    private function __construct() {
        $this->statusCode = 200;
        $this->headers = array();
        $this->cookies = array();
    }

    public function addHeader($name, $value) {
        $this->addRawHeader($name . ": " . $value);
        return $this;
    }

    public function addRawHeader($header) {
        $this->headers[] = $header;
        return $this;
    }

    public function setStatusCodeHeader($statusCode) {
        $this->statusCode = $statusCode;
        return $this;
    }

    public function addCookieHeader($name, $value = null, $expire = null, $path = null, $domain = null, $secure = false, $httponly = false) {
        $this->cookies[] = array("name" => $name, "value" => $value, "expire" => $expire, "path" => $path, "domain" => $domain, "secure" => $secure, "httponly" => $httponly);
        return $this;
    }

    public function send() {
        if (!headers_sent()) {
            http_response_code($this->statusCode);
            foreach ($this->headers as $rawHeader)
                header($rawHeader);
            foreach ($this->cookies as $cookie)
                call_user_func_array("setcookie", $cookie);
        }
        return $this;
    }
}