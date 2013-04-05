<?php

final class Loader
{
    private $instancesCache;
    private $basePath;
    
    public function __construct ($basePath=null)
    {
        $this->instancesCache = array();
        $this->basePath = $basePath;
    }
    
    public function setBasePath ($basePath)
    {
        $this->basePath = $basePath;
    }
    
    public function getInstance ($name, $params=null, $basePath=null)
    {
        if (empty($basePath))
            $basePath = $this->basePath;
        $pathSeparator = "/";
        $pathSeparatorPosition = strrpos($name, $pathSeparator);
        $pathSeparatorPosition = ($pathSeparatorPosition != false)? ($pathSeparatorPosition+1) : 0;
        $className = ucfirst(substr($name,$pathSeparatorPosition,strlen($name)));        
        require_once ((!empty($basePath)? ($basePath . $pathSeparator): "") . substr($name,0,$pathSeparatorPosition) . $className . '.php');
        return $this->instantiateClass($className, $params);
    }
    
    public function getCategorizedInstance ($category, $name, $params=null, $basePath=null)
    {
        if (empty($basePath))
            $basePath = $this->basePath;
        $pathSeparator = "/";
        $categoryFolderName = $category . "s";
        $categoryInstanceName = $name . ucfirst($category);
        $categoryBasePath = (!empty($basePath)? ($basePath . $pathSeparator): "") . $categoryFolderName;
        return $this->getInstance($categoryInstanceName, $params, $categoryBasePath);
    }
    
    public function getCacheInstance ($name, $basePath=null)
    {
        $cacheHash = (!empty($basePath)? ($basePath . "_") : "") . $name;
        if (!isset($this->instancesCache[$cacheHash]))
            $this->instancesCache[$cacheHash] = $this->getInstance ($name, null, $basePath);
        return $this->instancesCache[$cacheHash];
    }
    
    public function getCategorizedCacheInstance ($category, $name, $basePath=null)
    {
        $cacheHash = (!empty($basePath)? ($basePath . "_") : "") . $category . "_" . $name;
        if (!isset($this->instancesCache[$cacheHash]))
            $this->instancesCache[$cacheHash] = $this->getCategorizedInstance ($category, $name, null, $basePath);
        return $this->instancesCache[$cacheHash];
    }
    
    private function instantiateClass ($className, $params=array())
    {
        $instance = null;
        if (!empty($params) && sizeof($params) > 0)
        {
            $reflectionObj = new ReflectionClass($className);
            $instance = $reflectionObj->newInstanceArgs($params); 
        }
        else
        {
            $instance = new $className;
        }
        return $instance;
    }
}

?>
