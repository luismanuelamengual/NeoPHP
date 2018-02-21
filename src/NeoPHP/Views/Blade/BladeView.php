<?php

namespace NeoPHP\Views\Blade;

use NeoPHP\Views\View;

class BladeView extends View {

    private $view;

    public function __construct(\Illuminate\View\View $view) {
        $this->view = $view;
    }

    public function set($name, $value) {
        $this->view->with($name, $value);
    }

    public function get($name) {
        $data = $this->view->getData();
        return $data[$name];
    }

    protected function renderContent() {
        echo $this->view->render();
    }
}