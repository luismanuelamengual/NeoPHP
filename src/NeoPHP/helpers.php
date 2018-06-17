<?php

if (!function_exists('create_app')) {
    /**
     * Crea una aplicación NeoPHP
     * @return \NeoPHP\Application
     */
    function create_app($basePath=null) {
        return \NeoPHP\Application::create($basePath);
    }
}

if (!function_exists('get_app')) {
    /**
     * Obtiene una aplicación NeoPHP
     * @return \NeoPHP\Application
     */
    function get_app() {
        return \NeoPHP\Application::get();
    }
}

if (!function_exists('get_host_url')) {
    /**
     * Obtiene la url host del servidor
     * @return string
     */
    function get_host_url() {
        $request = get_request();
        $url = $request->scheme();
        $url .= "://";
        $url .= $request->host();
        return $url;
    }
}

if (!function_exists('get_url')) {
    /**
     * Obtiene la url base del sitio php
     * @param string|null $path
     * @return string
     */
    function get_url(string $path=null) {
        $request = get_request();
        $url = get_host_url();
        $url .= $request->baseContext();
        if ($path != null) {
            if ($path[0] != '/') {
                $url .= "/";
            }
            $url .= $path;
        }
        return $url;
    }
}

if (!function_exists('get_property')) {
    /**
     * @param $key
     * @param null $defaultValue
     * @return mixed
     */
    function get_property($key, $defaultValue=null) {
        return \NeoPHP\Properties\Properties::get($key, $defaultValue);
    }
}

if (!function_exists('set_property')) {
    /**
     * @param $key
     * @param $value
     */
    function set_property($key, $value) {
        \NeoPHP\Properties\Properties::set($key, $value);
    }
}

if (!function_exists('get_logger')) {
    /**
     * @param null $loggerName
     * @return \Monolog\Logger
     */
    function get_logger($loggerName=null) {
        return \NeoPHP\Log\Loggers::get($loggerName);
    }
}

if (!function_exists('get_request')) {
    /**
     * @param null $parameterName
     * @param null $defaultValue
     * @return \NeoPHP\Http\Request
     */
    function get_request($parameterName=null, $defaultValue=null) {
        $request = \NeoPHP\Http\Request::instance();
        return isset($parameterName)? $request->get($parameterName, $defaultValue) : $request;
    }
}

if (!function_exists('get_response')) {
    /**
     * @return \NeoPHP\Http\Response
     */
    function get_response() {
        return \NeoPHP\Http\Response::instance();
    }
}

if (!function_exists('get_session')) {
    /**
     * @param null $parameterName
     * @param null $defaultValue
     * @return \NeoPHP\Http\Session
     */
    function get_session($parameterName=null, $defaultValue=null) {
        $session = \NeoPHP\Http\Session::instance();
        return isset($parameterName)? $session->get($parameterName, $defaultValue) : $session;
    }
}

if (!function_exists('get_message')) {
    /**
     * @param $key
     * @param array ...$replacements
     * @return null
     */
    function get_message($key, ...$replacements) {
        return \NeoPHP\Messages\Messages::get($key, array_slice(func_get_args(), 1));
    }
}

if (!function_exists('create_view')) {
    /**
     * @param $name
     * @param array $parameters
     * @return \NeoPHP\Views\View
     */
    function create_view($name, array $parameters = []) {
        return NeoPHP\Views\Views::create($name, $parameters);
    }
}

if (!function_exists('register_event_listener')) {
    /**
     * @param $event
     * @param $callbackOrAction
     */
    function register_event_listener($event, $callbackOrAction) {
        \NeoPHP\Events\Events::register($event, $callbackOrAction);
    }
}

if (!function_exists('fire_event')) {
    /**
     * @param $event
     * @param array $parameters
     * @throws Exception
     */
    function fire_event($event, array $parameters = []) {
        \NeoPHP\Events\Events::fire($event, $parameters);
    }
}

if (!function_exists('handle_error')) {
    /**
     * @param $errno
     * @param $errstr
     * @param $errfile
     * @param $errline
     * @param $errcontext
     * @throws ErrorException
     */
    function handle_error($errno, $errstr, $errfile, $errline, $errcontext) {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }
}

if (!function_exists('handle_exception')) {
    /**
     * @param $ex
     */
    function handle_exception($ex) {

        get_logger()->error($ex);

        if (php_sapi_name() == "cli") {
            $whoops = new \Whoops\Run;
            $whoops->pushHandler(new \Whoops\Handler\PlainTextHandler());
            $whoops->handleException($ex);
        }
        else {
            if (get_property("app.debug") || get_request("debug")) {
                $request = get_request();
                if ($request->ajax()) {
                    $error = new stdClass();
                    $error->message = $ex->getMessage();
                    $error->trace = $ex->getTrace();
                    $response = get_response();
                    $response->statusCode(\NeoPHP\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
                    $response->content($error);
                    $response->send();
                }
                else {
                    $whoops = new \Whoops\Run;
                    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
                    $whoops->handleException($ex);
                }
            }
            else {
                handle_error_code(\NeoPHP\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }
}

if (!function_exists('handle_error_code')) {
    /**
     * @param $code
     */
    function handle_error_code($code) {

        http_response_code($code);
        include __DIR__ . DIRECTORY_SEPARATOR . "errorPage.php";
        exit;
    }
}
