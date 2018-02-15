<?php

if (!function_exists('app')) {
    /**
     * @return \NeoPHP\Core\Application
     */
    function app() {
        return \NeoPHP\Core\Application::getInstance();
    }
}

if (!function_exists('controller')) {
    /**
     * @param $controllerClass
     * @return mixed
     */
    function controller($controllerClass) {
        return \NeoPHP\Core\Controllers::get($controllerClass);
    }
}

if (!function_exists('config')) {
    /**
     * @param $key
     * @param null $defaultValue
     * @return mixed
     */
    function config($key, $defaultValue=null) {
        return \NeoPHP\Core\Properties::get($key, $defaultValue);
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

        $whoops = new \Whoops\Run;
        if (config("app.debug")) {
            $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
        }
        else {
            $whoops->pushHandler(new \Whoops\Handler\PlainTextHandler());
        }
        $whoops->handleException($ex);
    }
}

