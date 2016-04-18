<?php

namespace NeoPHP\web;

use NeoPHP\io\File;
use NeoPHP\io\Files;

class WebAssetsManager
{
    private $published = [];
    private $assetsPath;
    private $assetsBaseUrl;
    
    public function __construct()
    {
        $properties = WebApplication::getInstance()->getProperties();
        $this->assetsPath = isset($properties->assetsPath)? $properties->assetsPath : (realpath("") . DIRECTORY_SEPARATOR . "res" . DIRECTORY_SEPARATOR . "assets");
        $this->assetsBaseUrl = isset($properties->assetsBaseUrl)? $properties->assetsBaseUrl : (WebApplication::getInstance()->getBaseUrl() . "res/assets");
    }

    public function getAssetsPath()
    {
        return $this->assetsPath;
    }

    public function setAssetsPath($assetsPath)
    {
        $this->assetsPath = $assetsPath;
    }
    
    public function getAssetsBaseUrl()
    {
        return $this->assetsBaseUrl;
    }

    public function setAssetsBaseUrl($assetsBaseUrl)
    {
        $this->assetsBaseUrl = $assetsBaseUrl;
    }
    
    public function publish ($path, $useSymLinks=false)
    {
        if (empty($this->published[$path]))
        {
            $destinationFileName = basename($path);
            $destinationFile = new File($this->assetsPath . DIRECTORY_SEPARATOR . $destinationFileName);
            if (!$destinationFile->exists())
            {
                $sourceFile = new File(realpath($path));
                if ($useSymLinks)
                {
                    Files::createSymbolicLink($sourceFile, $destinationFile);
                }
                else
                {
                    Files::copy($sourceFile, $destinationFile->getParentFile());
                }
            }
            $this->published[$path] = $this->assetsBaseUrl . "/" . $destinationFileName;
        }
        return $this->published[$path];
    }
}