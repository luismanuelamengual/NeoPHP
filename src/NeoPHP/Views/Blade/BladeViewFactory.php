<?php

namespace NeoPHP\Views\Blade;

use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Engines\PhpEngine;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;
use NeoPHP\Views\View;
use NeoPHP\Views\ViewFactory;

/**
 * Class BladeViewFactory
 * @package NeoPHP\mvc\views
 */
class BladeViewFactory extends ViewFactory {

    private $viewFactory;

    public function __construct(array $config = []) {

        if (!class_exists("Illuminate\View\View")) {
            throw new \RuntimeException("Package \"illuminate/view\" is missing. Add package via \"composer require illuminate/view\" !!. Tested with version: 5.6.3");
        }

        $templatesPath = isset($config["templatesPath"]) ? $config["templatesPath"] : app()->resourcesPath() . DIRECTORY_SEPARATOR . "views";
        $compiledTemplatesPath = isset($config["compiledTemplatesPath"]) ? $config["compiledTemplatesPath"] : app()->storagePath() . DIRECTORY_SEPARATOR . "framework" . DIRECTORY_SEPARATOR . "views";
        $filesystem = new Filesystem;
        $eventDispatcher = new Dispatcher(new Container);
        $viewResolver = new EngineResolver;
        $bladeCompiler = new BladeCompiler($filesystem, $compiledTemplatesPath);
        $viewResolver->register('blade', function () use ($bladeCompiler, $filesystem) {
            return new CompilerEngine($bladeCompiler, $filesystem);
        });
        $viewResolver->register('php', function () {
            return new PhpEngine;
        });
        $viewFinder = new FileViewFinder($filesystem, [$templatesPath]);
        $this->viewFactory = new Factory($viewResolver, $viewFinder, $eventDispatcher);
    }

    public function createView($name, array $parameters = []): View {
        return new BladeView($this->viewFactory->make($name, $parameters));
    }
}