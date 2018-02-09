<?php

namespace NeoPHP\mvc;

use Exception;
use NeoPHP\app\Application;
use NeoPHP\core\IllegalArgumentException;
use NeoPHP\mvc\controllers\ControllersProvider;
use NeoPHP\mvc\models\ModelManagersProvider;
use NeoPHP\mvc\views\ViewFactoriesProvider;

/**
 * Class MVCApplication
 * @package NeoPHP\mvc
 * @author Luis Manuel Amengual <luismanuelamengual@gmail.com>
 */
abstract class MVCApplication extends Application {

    private $controllersProvider;
    private $modelManagersProvider;
    private $viewFactoriesProvider;

    /**
     * MVCApplication constructor.
     * @param $basePath string base path of the application
     */
    public function __construct($basePath) {
        parent::__construct($basePath);
    }

    /**
     * @return ControllersProvider
     */
    public function getControllersProvider() : ControllersProvider {
        if (!isset($this->controllersProvider)) {
            $this->controllersProvider = new ControllersProvider($this);
        }
        return $this->controllersProvider;
    }

    /**
     * @return ModelManagersProvider
     * @throws Exception
     * @throws IllegalArgumentException
     */
    public function getModelManagersProvider() : ModelManagersProvider {
        if (!isset($this->modelManagersProvider)) {
            $modelManagersProviderClassName = $this->getProperties()->modelManagersProviderClassName;
            if (!isset($modelManagersProviderClassName)) {
                throw new Exception("Property \"modelManagersProviderClassName\" not found !!");
            }
            if (!class_exists($modelManagersProviderClassName)) {
                throw new IllegalArgumentException("Model manager provider class \"$modelManagersProviderClassName\" not found !!.");
            }
            if (!is_subclass_of($controllerClass, ModelManagersProvider::class)) {
                throw new IllegalArgumentException("Invalid model manager provider class \"$modelManagersProviderClassName\". Make sure this class extends ModelManagersProvider");
            }
            $this->modelManagersProvider = new $modelManagersProviderClassName($this->application);
        }
        return $this->modelManagersProvider;
    }

    /**
     * @return ViewFactoriesProvider
     * @throws Exception
     * @throws IllegalArgumentException
     */
    public function getViewFactoriesProvider() : ViewFactoriesProvider {
        if (!isset($this->viewFactoriesProvider)) {
            $viewFactoriesProviderClassName = $this->getProperties()->viewFactoriesProviderClassName;
            if (!isset($viewFactoriesProviderClassName)) {
                throw new Exception("Property \"viewFactoriesProviderClassName\" not found !!");
            }
            if (!class_exists($viewFactoriesProviderClassName)) {
                throw new IllegalArgumentException("View factory provider class \"$viewFactoriesProviderClassName\" not found !!.");
            }
            if (!is_subclass_of($controllerClass, ModelManagersProvider::class)) {
                throw new IllegalArgumentException("Invalid view factory provider class \"$viewFactoriesProviderClassName\". Make sure this class extends ModelManagersProvider");
            }
            $this->viewFactoriesProvider = new $viewFactoriesProviderClassName($this->application);
        }
        return $this->viewFactoriesProvider;
    }
}