<?php

if (!function_exists('get_app')) {
    /**
     * @return \NeoPHP\Core\Application
     */
    function get_app($basePath=null) {
        $app = null;
        if ($basePath != null) {
            $app = \NeoPHP\Core\Application::create($basePath);
        }
        else {
            $app = \NeoPHP\Core\Application::get();
        }
        return $app;
    }
}

if (!function_exists('get_property')) {
    /**
     * @param $key
     * @param null $defaultValue
     * @return mixed
     */
    function get_property($key, $defaultValue=null) {
        return \NeoPHP\Config\Properties::get($key, $defaultValue);
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

    function get_request($parameterName=null) {
        $request = \NeoPHP\Http\Request::getInstance();
        return isset($parameterName)? $request->get($parameterName) : $request;
    }
}

if (!function_exists('create_view')) {
    /**
     * @param $name
     * @return \NeoPHP\Views\View
     */
    function create_view($name, array $parameters = []) {
        return NeoPHP\Views\Views::create($name, $parameters);
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
            if (get_property("app.debug")) {
                $whoops = new \Whoops\Run;
                $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
                $whoops->handleException($ex);
            }
            else {
                handle_error_code(500);
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
    }
}
