<?php

namespace NeoPHP\Core;

/**
 * Class Application - Main application class
 * @package NeoPHP\app
 * @author Luis Manuel Amengual <luismanuelamengual@gmail.com>
 */
abstract class Application {

    private $basePath;

    /**
     * Application constructor.
     * @param $basePath string path for the application
     */
    public function __construct($basePath) {

        $this->basePath = $basePath;
    }

    /**
     * Returns the base path of the application
     */
    public function getBasePath() {
        return $this->basePath;
    }
}