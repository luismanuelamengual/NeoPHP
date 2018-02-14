<?php

if (!function_exists('config')) {
    /**
     * @param $key
     * @param null $defaultValue
     * @return mixed
     */
    function config($key, $defaultValue=null) {
        return \NeoPHP\Core\Facades\Properties::get($key, $defaultValue);
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
        if (php_sapi_name() === 'cli') {
            echo "ERROR: " . $ex->getMessage();
        }
        else {
            if ($ex instanceof RouteNotFoundException) {
                http_response_code(404);
            }
            else {
                http_response_code(500);
            }
            echo "ERROR: " . $ex->getMessage();
            echo "<pre>";
            echo "## " . $ex->getFile() . "(" . $ex->getLine() . ")<br>";
            echo print_r($ex->getTraceAsString(), true);
            echo "</pre>";
        }
    }
}

