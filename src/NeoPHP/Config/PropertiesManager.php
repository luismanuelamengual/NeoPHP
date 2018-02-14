<?php

namespace NeoPHP\Config;

interface PropertiesManager {

    public function get($key, $defaultValue = null);

    public function set($key, $value);
}