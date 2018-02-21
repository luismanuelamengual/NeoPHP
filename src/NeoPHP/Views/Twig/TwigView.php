<?php

namespace NeoPHP\Views\Twig;

use NeoPHP\Views\View;

class TwigView extends View {

    private $twigTemplate;
    private $context = [];

    public function __construct(\Twig_TemplateWrapper $twigTemplate, array $context = []) {
        $this->twigTemplate = $twigTemplate;
        $this->context = $context;
    }

    public function set($name, $value) {
        $this->context[$name] = $value;
    }

    public function get($name) {
        return $this->context[$name];
    }

    protected function renderContent() {
        echo $this->twigTemplate->render($this->context);
    }
}