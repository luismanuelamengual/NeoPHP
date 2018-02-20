<?php

namespace NeoPHP\Views\Blade;

use NeoPHP\Views\View;

class BladeView extends View {

    private $viewFactory;

    public function __construct($viewFactory, $name, array $parameters = []) {
        parent::__construct($name, $parameters);
        $this->viewFactory = $viewFactory;
    }

    protected function renderContent() {
        $this->viewFactory->make($this->name, $this->parameters)->render();
    }
}