<?php

namespace NeoPHP\Views\Twig;

use NeoPHP\Views\View;
use NeoPHP\Views\ViewFactory;

class TwigViewFactory extends ViewFactory {

    private $twig;
    private $filesExtension;

    public function __construct(array $config = []) {

        if (!class_exists("Twig_Loader_Filesystem")) {
            throw new \RuntimeException("Package \"twig/twig\" is missing. Add package via \"composer require twig/twig\" !!");
        }

        $templatesPath = isset($config["templatesPath"]) ? $config["templatesPath"] : getApp()->getResourcesPath() . DIRECTORY_SEPARATOR . "views";
        $loader = new \Twig_Loader_Filesystem($templatesPath);
        $this->twig = new \Twig_Environment($loader, isset($config["environment"])? $config["environment"] : []);
        $this->filesExtension = isset($config["filesExtension"])? $config["filesExtension"] : "php";
    }

    public function createView($name, array $parameters = []): View {
        $filename = str_replace(".", DIRECTORY_SEPARATOR, $name);
        $filename .= "." . $this->filesExtension;
        return new TwigView($this->twig->load($filename), $parameters);
    }
}