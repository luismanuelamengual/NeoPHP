<?php

if (!function_exists('create_app')) {
    /**
     * Creates an application
     * @return \NeoPHP\Application
     */
    function create_app($basePath=null) {
        return \NeoPHP\Application::create($basePath);
    }
}

if (!function_exists('get_app')) {
    /**
     * Return the current application instance
     * @return \NeoPHP\Application
     */
    function get_app() {
        return \NeoPHP\Application::get();
    }
}

if (!function_exists('get_host_url')) {
    /**
     * Returns the host url
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
     * Returns the base url
     * @param string|null $path
     * @return string
     */
    function get_url(?string $path=null) {
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
     * Returns the value of a property
     * @param string $key property name
     * @param null $defaultValue
     * @return mixed
     */
    function get_property(string $key, $defaultValue=null) {
        return \NeoPHP\Properties\Properties::get($key, $defaultValue);
    }
}

if (!function_exists('set_property')) {
    /**
     * Set a property value
     * @param string $key property name
     * @param mixed $value value of the property
     */
    function set_property(string $key, $value) {
        \NeoPHP\Properties\Properties::set($key, $value);
    }
}

if (!function_exists('get_logger')) {
    /**
     * Returns the application logger
     * @param string|null $loggerName
     * @return \Monolog\Logger
     */
    function get_logger(?string $loggerName=null) {
        return \NeoPHP\Log\Loggers::get($loggerName);
    }
}

if (!function_exists('get_request')) {
    /**
     * Returns http request data
     * @param string|null $parameterName
     * @param mixed|null $defaultValue
     * @return \NeoPHP\Http\Request
     */
    function get_request(?string $parameterName=null, $defaultValue=null) {
        $request = \NeoPHP\Http\Request::instance();
        return isset($parameterName)? $request->get($parameterName, $defaultValue) : $request;
    }
}

if (!function_exists('get_response')) {
    /**
     * Returns http response
     * @return \NeoPHP\Http\Response
     */
    function get_response() {
        return \NeoPHP\Http\Response::instance();
    }
}

if (!function_exists('get_session')) {
    /**
     * Returns http session data
     * @param string|null $parameterName
     * @param mixed|null $defaultValue
     * @return \NeoPHP\Http\Session
     */
    function get_session(?string $parameterName=null, $defaultValue=null) {
        $session = \NeoPHP\Http\Session::instance();
        return isset($parameterName)? $session->get($parameterName, $defaultValue) : $session;
    }
}

if (!function_exists('get_message')) {
    /**
     * Returns a message
     * @param string $key
     * @param array ...$replacements
     * @return string translated message
     */
    function get_message(string $key, ...$replacements) {
        return \NeoPHP\Messages\Messages::get($key, $replacements);
    }
}

if (!function_exists('create_view')) {
    /**
     * Returns a view from the application
     * @param string $name name of the view
     * @param array $parameters parameters for the view
     * @return \NeoPHP\Views\View
     */
    function create_view(string $name, array $parameters = []) {
        return NeoPHP\Views\Views::create($name, $parameters);
    }
}

if (!function_exists('register_event_listener')) {
    /**
     * Registers an event listener
     * @param string $event event to listen
     * @param mixed $action action to be executed
     */
    function register_event_listener(string $event, $action) {
        \NeoPHP\Events\Events::register($event, $action);
    }
}

if (!function_exists('fire_event')) {
    /**
     * Fires an event
     * @param string $event event  to fire
     * @param array $parameters parameters of the event
     * @throws Exception exception
     */
    function fire_event(string $event, array $parameters = []) {
        \NeoPHP\Events\Events::fire($event, $parameters);
    }
}

if (! function_exists('get_env')) {
    /**
     * Gets the value of an environment variable.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    function get_env($key, $default = null)
    {
        $value = getenv($key);
        if ($value === false) {
            return get_value($default);
        }
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return;
        }
        if (($valueLength = strlen($value)) > 1 && $value[0] === '"' && $value[$valueLength - 1] === '"') {
            return substr($value, 1, -1);
        }
        return $value;
    }
}

if (! function_exists('get_value')) {
    /**
     * Return the default value of the given value.
     *
     * @param  mixed  $value
     * @return mixed
     */
    function get_value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}