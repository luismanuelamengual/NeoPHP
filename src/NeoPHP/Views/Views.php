<?php

namespace NeoPHP\Views;

abstract class Views {

    private static $factories = [];

    public static function factory($factoryName=null): ViewFactory {
        if (empty($factoryName)) {
            $factoryName = get_property("views.default", "main");
        }
        if (!isset(self::$factories[$factoryName])) {
            $factoriesConfig = get_property("views.factories", []);
            if (!isset($factoriesConfig[$factoryName])) {
                throw new \RuntimeException("View factory \"$factoryName\" was not configured !!");
            }
            $factoryConfig = $factoriesConfig[$factoryName];
            $factoryClassName = $factoryConfig["class"];
            self::$factories[$factoryName] = new $factoryClassName($factoryConfig);
        }
        return self::$factories[$factoryName];
    }

    public static function create($viewName, array $parameters = []) {
        $output = get_request(get_property("views.view_type_parameter_name"));
        if (!empty($output)) {
            $viewName .= '_' . $output;
        }
        return self::factory()->create($viewName, $parameters);
    }
}