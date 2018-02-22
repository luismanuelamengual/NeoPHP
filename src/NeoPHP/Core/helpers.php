<?php

if (!function_exists('createApp')) {
    /**
     * @param $basePath
     * @return \NeoPHP\Core\Application
     */
    function createApp($basePath) {
        return \NeoPHP\Core\Application::create($basePath);
    }
}

if (!function_exists('getApp')) {
    /**
     * @return \NeoPHP\Core\Application
     */
    function getApp() {
        return \NeoPHP\Core\Application::get();
    }
}

if (!function_exists('getController')) {
    /**
     * @param $controllerClass
     * @return mixed
     */
    function getController($controllerClass) {
        return \NeoPHP\Controllers\Controllers::get($controllerClass);
    }
}

if (!function_exists('getProperty')) {
    /**
     * @param $key
     * @param null $defaultValue
     * @return mixed
     */
    function getProperty($key, $defaultValue=null) {
        return \NeoPHP\Config\Properties::get($key, $defaultValue);
    }
}

if (!function_exists('getLogger')) {
    /**
     * @param null $loggerName
     * @return \Monolog\Logger
     */
    function getLogger($loggerName=null) {
        return \NeoPHP\Log\Loggers::get($loggerName);
    }
}

if (!function_exists('getResource')) {
    /**
     * @param $resourceName
     * @return \NeoPHP\Resources\ResourceManager
     */
    function getResource($resourceName) {
        return \NeoPHP\Resources\Resources::get($resourceName);
    }
}

if (!function_exists('createView')) {
    /**
     * @param $name
     * @return \NeoPHP\Views\View
     */
    function createView($name, array $parameters = []) {
        return NeoPHP\Views\Views::createView($name, $parameters);
    }
}

if (!function_exists('handleError')) {
    /**
     * @param $errno
     * @param $errstr
     * @param $errfile
     * @param $errline
     * @param $errcontext
     * @throws ErrorException
     */
    function handleError($errno, $errstr, $errfile, $errline, $errcontext) {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }
}

if (!function_exists('handleException')) {
    /**
     * @param $ex
     */
    function handleException($ex) {

        getLogger()->error($ex);

        if (php_sapi_name() == "cli") {
            $whoops = new \Whoops\Run;
            $whoops->pushHandler(new \Whoops\Handler\PlainTextHandler());
            $whoops->handleException($ex);
        }
        else {
            if (getProperty("app.debug")) {
                $whoops = new \Whoops\Run;
                $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
                $whoops->handleException($ex);
            }
            else {
                handleErrorCode(500);
            }
        }
    }
}

if (!function_exists('handleErrorCode')) {
    /**
     * @param $code
     */
    function handleErrorCode($code) {

        http_response_code($code);
        include __DIR__ . DIRECTORY_SEPARATOR . "errorPage.php";
    }
}
