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
    /**
     * @param null $parameterName
     * @return \NeoPHP\Http\Request|null
     */
    function get_request($parameterName=null) {
        $request = \NeoPHP\Http\Request::instance();
        return isset($parameterName)? $request->get($parameterName) : $request;
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
     * @return \NeoPHP\Http\Session
     */
    function get_session() {
        return \NeoPHP\Http\Session::instance();
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

if (!function_exists('create_model')) {
    /**
     * @param $model
     * @param array $options
     * @return mixed
     */
    function create_model($model, array $options = []) {
        return \NeoPHP\Models\Models::createModel($model, $options);
    }
}

if (!function_exists('update_model')) {
    /**
     * @param $model
     * @param array $options
     * @return mixed
     */
    function update_model($model, array $options = []) {
        return \NeoPHP\Models\Models::updateModel($model, $options);
    }
}

if (!function_exists('delete_model')) {
    /**
     * @param $model
     * @param array $options
     * @return mixed
     */
    function delete_model($model, array $options = []) {
        return \NeoPHP\Models\Models::deleteModel($model, $options);
    }
}

if (!function_exists('retrieve_model_by_id')) {
    /**
     * @param $modelClass
     * @param $modelId
     * @param array $options
     * @return mixed
     */
    function retrieve_model_by_id($modelClass, $modelId, array $options = []) {
        return \NeoPHP\Models\Models::retrieveModelById($modelClass, $modelId, $options);
    }
}

if (!function_exists('retrieve_models')) {
    /**
     * @param $modelClass
     * @param array $options
     * @return mixed
     */
    function retrieve_models($modelClass, array $options = []) {
        return \NeoPHP\Models\Models::retrieveModels($modelClass, $options);
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
