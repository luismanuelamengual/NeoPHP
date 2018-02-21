<?php

namespace NeoPHP\Views;

/**
 * Class View
 * @package NeoPHP\Views
 */
abstract class View {

    /**
     * @param $name
     * @param $value
     */
    public abstract function set($name, $value);

    /**
     * @param $name
     * @return mixed
     */
    public abstract function get($name);

    /**
     * @return string
     */
    public function __toString() {
        return $this->render(true);
    }

    /**
     * @param bool $return
     * @return string
     */
    public final function render($return = false) {
        if ($return == true) {
            ob_start();
            $this->renderContent();
            return ob_get_clean();
        }
        else {
            $this->renderContent();
        }
    }

    /**
     * @return mixed
     */
    protected abstract function renderContent();
}