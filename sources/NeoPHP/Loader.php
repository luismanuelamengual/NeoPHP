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
    
    public function getInstance ($resource, $params=null, $basePath=null)
    {
        $resourceData = $this->getResourceData ($resource, $basePath);
        require_once ($resourceData["filename"]);
        return $this->instantiateClass($resourceData["classname"], $params);
    }
    
    public function getCacheInstance ($resource, $basePath=null)
    {
        if (!isset($this->instancesCache[$resource]))
            $this->instancesCache[$resource] = $this->getInstance ($resource, null, $basePath);
        return $this->instancesCache[$resource];
    }
    
    public function getSingletonInstance ($resource, $basePath=null)
    {
        if (!isset($this->instancesCache[$resource]))
        {
            $resourceData = $this->getResourceData ($resource, $basePath);
            require_once ($resourceData["filename"]);
            $this->instancesCache[$resource] = $resourceData["classname"]::getInstance();
        }
        return $this->instancesCache[$resource];
    }
    
    private function getResourceData ($resource, $basePath=null)
    {
        if (empty($basePath))
            $basePath = $this->basePath;
        $pathSeparator = "/";
        $pathSeparatorPosition = strrpos($resource, $pathSeparator);
        $pathParts = array();
        if (!empty($basePath))
            $pathParts[] = $basePath;
        if ($pathSeparatorPosition != false)
            $pathParts[] = substr($resource, 0, $pathSeparatorPosition);
        $className = ucfirst(substr($resource, ($pathSeparatorPosition != false)? ($pathSeparatorPosition+1) : 0));
        $pathParts[] = $className . ".php";
        return array("classname"=>$className, "filename"=>implode($pathSeparator, $pathParts));
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
