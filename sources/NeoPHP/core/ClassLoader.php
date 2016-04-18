<?php

namespace NeoPHP\core;

use Exception;

class ClassLoader 
{
    private static $instance;

    public static function getInstance()
    {
        if (!isset(self::$instance))
            self::$instance = new self;
        return self::$instance;
    }
    
    public function register ()
    {
        spl_autoload_register(array($this,'loadClass'));
    }
    
    public function unregister ()
    {
        spl_autoload_unregister(array($this,'loadClass'));
    }
    
    public function getIncludePaths ()
    {
        return explode(PATH_SEPARATOR, get_include_path());
    }
    
    public function setIncludePaths (array $paths = [])
    {
        set_include_path(implode(PATH_SEPARATOR, $paths));
    }
    
    public function addIncludePath ($path)
    {
        set_include_path(get_include_path() . PATH_SEPARATOR . $path);
    }
    
    public function restoreIncludePaths ()
    {
        restore_include_path();
    }
    
    public function loadClass ($className)
    {         
        if (!@include_once ($this->getClassFilename($className))) 
            throw new Exception ("Error loading class \"$className\"");
    }
    
    private function getClassFilename ($className)
    {
        return str_replace('\\', DIRECTORY_SEPARATOR, $className) . ".php";
    }
}

?>