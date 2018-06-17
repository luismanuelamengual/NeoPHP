<?php

namespace NeoPHP\Http;

use NeoPHP\Views\View;
use stdClass;

/**
 * Class Response
 * @package Sitrack\Http
 */
final class Response {

    const HTTP_CONTINUE = 100;
    const HTTP_SWITCHING_PROTOCOLS = 101;
    const HTTP_PROCESSING = 102;            // RFC2518
    const HTTP_OK = 200;
    const HTTP_CREATED = 201;
    const HTTP_ACCEPTED = 202;
    const HTTP_NON_AUTHORITATIVE_INFORMATION = 203;
    const HTTP_NO_CONTENT = 204;
    const HTTP_RESET_CONTENT = 205;
    const HTTP_PARTIAL_CONTENT = 206;
    const HTTP_MULTI_STATUS = 207;          // RFC4918
    const HTTP_ALREADY_REPORTED = 208;      // RFC5842
    const HTTP_IM_USED = 226;               // RFC3229
    const HTTP_MULTIPLE_CHOICES = 300;
    const HTTP_MOVED_PERMANENTLY = 301;
    const HTTP_FOUND = 302;
    const HTTP_SEE_OTHER = 303;
    const HTTP_NOT_MODIFIED = 304;
    const HTTP_USE_PROXY = 305;
    const HTTP_RESERVED = 306;
    const HTTP_TEMPORARY_REDIRECT = 307;
    const HTTP_PERMANENTLY_REDIRECT = 308;  // RFC7238
    const HTTP_BAD_REQUEST = 400;
    const HTTP_UNAUTHORIZED = 401;
    const HTTP_PAYMENT_REQUIRED = 402;
    const HTTP_FORBIDDEN = 403;
    const HTTP_NOT_FOUND = 404;
    const HTTP_METHOD_NOT_ALLOWED = 405;
    const HTTP_NOT_ACCEPTABLE = 406;
    const HTTP_PROXY_AUTHENTICATION_REQUIRED = 407;
    const HTTP_REQUEST_TIMEOUT = 408;
    const HTTP_CONFLICT = 409;
    const HTTP_GONE = 410;
    const HTTP_LENGTH_REQUIRED = 411;
    const HTTP_PRECONDITION_FAILED = 412;
    const HTTP_REQUEST_ENTITY_TOO_LARGE = 413;
    const HTTP_REQUEST_URI_TOO_LONG = 414;
    const HTTP_UNSUPPORTED_MEDIA_TYPE = 415;
    const HTTP_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    const HTTP_EXPECTATION_FAILED = 417;
    const HTTP_I_AM_A_TEAPOT = 418;                                               // RFC2324
    const HTTP_MISDIRECTED_REQUEST = 421;                                         // RFC7540
    const HTTP_UNPROCESSABLE_ENTITY = 422;                                        // RFC4918
    const HTTP_LOCKED = 423;                                                      // RFC4918
    const HTTP_FAILED_DEPENDENCY = 424;                                           // RFC4918
    const HTTP_RESERVED_FOR_WEBDAV_ADVANCED_COLLECTIONS_EXPIRED_PROPOSAL = 425;   // RFC2817
    const HTTP_UPGRADE_REQUIRED = 426;                                            // RFC2817
    const HTTP_PRECONDITION_REQUIRED = 428;                                       // RFC6585
    const HTTP_TOO_MANY_REQUESTS = 429;                                           // RFC6585
    const HTTP_REQUEST_HEADER_FIELDS_TOO_LARGE = 431;                             // RFC6585
    const HTTP_UNAVAILABLE_FOR_LEGAL_REASONS = 451;
    const HTTP_INTERNAL_SERVER_ERROR = 500;
    const HTTP_NOT_IMPLEMENTED = 501;
    const HTTP_BAD_GATEWAY = 502;
    const HTTP_SERVICE_UNAVAILABLE = 503;
    const HTTP_GATEWAY_TIMEOUT = 504;
    const HTTP_VERSION_NOT_SUPPORTED = 505;
    const HTTP_VARIANT_ALSO_NEGOTIATES_EXPERIMENTAL = 506;                        // RFC2295
    const HTTP_INSUFFICIENT_STORAGE = 507;                                        // RFC4918
    const HTTP_LOOP_DETECTED = 508;                                               // RFC5842
    const HTTP_NOT_EXTENDED = 510;                                                // RFC2774
    const HTTP_NETWORK_AUTHENTICATION_REQUIRED = 511;                             // RFC6585

    private static $instance;

    private $statusCode = self::HTTP_OK;
    private $headers = [];
    private $cookies = [];
    private $content;
    private $sent = false;

    /**
     * Request constructor.
     */
    private function __construct() {
    }

    /**
     * @return Response
     */
    public static function instance(): Response {
        if (!isset(self::$instance))
            self::$instance = new self;
        return self::$instance;
    }

    /**
     * Clears the response
     */
    public function clear() {
        $this->statusCode = self::HTTP_OK;
        $this->headers = [];
        $this->cookies = [];
        $this->content = null;
        $this->sent = false;
    }

    /**
     * @param null $statusCode
     * @return Response|null
     */
    public function statusCode($statusCode = null) {
        $response = null;
        if ($statusCode != null) {
            $this->statusCode = $statusCode;
            $response = $this;
        }
        else {
            $response = $this->statusCode;
        }
        return $response;
    }

    /**
     * @return array
     */
    public function headers() {
        return $this->headers;
    }

    /**
     * @param $header
     * @param null $value
     * @return $this
     */
    public function header($header, $value=null) {
        if (isset($value)) {
            $header .= ": " . $value;
        }

        $this->headers[] = $header;
        return $this;
    }

    /**
     * Returns the response cookies
     * @return array
     */
    public function cookies() {
        return $this->cookies;
    }

    /**
     * @param $name
     * @param null $value
     * @param null $expire
     * @param null $path
     * @param null $domain
     * @param bool $secure
     * @param bool $httponly
     * @return Response
     */
    public function addCookie($name, $value = null, $expire = null, $path = null, $domain = null, $secure = false, $httponly = false) {
        $this->cookies[] = compact("name", "value", "expire", "path", "domain", "secure", "httponly");
        return $this;
    }

    /**
     * Response content type
     * @param $contentType
     */
    public function contentType($contentType) {
        $this->header("Content-Type", $contentType);
    }

    /**
     * @param $route
     * @param array $parameters
     * @param bool $permanent
     */
    public function redirectRoute ($route, array $parameters = [], $permanent=false) {
        $this->redirect(get_url($route), $parameters, $permanent);
    }

    /**
     * @param $url
     * @param array $parameters
     * @param bool $permanent
     */
    public function redirect ($url, array $parameters = [], $permanent=false) {
        if (!empty($parameters)) {
            $queryParameters = [];
            foreach ($parameters as $key=>$value) {
                $queryParameters[] = $key . "=" . urlencode($value);
            }
            $url .= strpos($url, "?") === false? "?" : "&";
            $url .= implode("&", $queryParameters);
        }
        $this->clear();
        $this->header("Location", $url);
        $this->statusCode($permanent? self::HTTP_PERMANENTLY_REDIRECT : self::HTTP_TEMPORARY_REDIRECT);
        $this->send();
    }

    /**
     * @param null $content
     * @return Response|null
     */
    public function content($content=null) {
        $response = null;
        if ($content !== null) {
            $this->content = $content;
            $response = $this;
        }
        else {
            $response = $this->content;
        }
        return $response;
    }

    /**
     * Sends the response headers
     */
    private function sendHeaders() {
        if (!headers_sent()) {
            http_response_code($this->statusCode);
            foreach ($this->headers as $header) {
                header($header);
            }
            foreach ($this->cookies as $cookie) {
                call_user_func_array("setcookie", $cookie);
            }
        }
    }

    /**
     * Configures the response before sending
     */
    private function sendConfig() {
        if ($this->content !== null) {
            if (($this->content instanceof stdClass) || is_array($this->content)) {
                $content = ob_get_contents();
                if (empty($content)) {
                    $this->contentType("application/json");
                    $this->content = json_encode($this->content);
                }
            }
        }
    }

    /**
     * Sends the content of the response
     */
    private function sendContent() {
        if ($this->content !== null) {
            if ($this->content instanceof View) {
                $this->content->render();
            }
            else if (($this->content instanceof stdClass) || is_array($this->content)) {
                echo "<pre>";
                print_r ($this->content);
                echo "</pre>";
            }
            else {
                echo $this->content;
            }
        }
    }

    /**
     * Send the response
     */
    public function send() {
        if (!$this->sent) {
            $this->sendConfig();
            $this->sendHeaders();
            $this->sendContent();
            $this->sent = true;
        }
    }

    /**
     * @return bool
     */
    public function sent(): bool {
        return $this->sent;
    }
}