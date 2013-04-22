<?php

final class Loader
{
    public function __construct ($paths=array())
    {
        $this->paths = $paths;
        if (sizeof($this->paths) == 0)
            $this->paths[] = "";
    }
    
    public function createInstance ($resource, $params=array())
    {
        $reflectionClass = new ReflectionClass($this->getClass($resource));
        return $reflectionClass->newInstanceArgs($params); 
    }
    
    public function getCacheInstance ($resource)
    {
        if (empty($this->instancesCache[$resource]))
            $this->instancesCache[$resource] = $this->createInstance($resource);
        return $this->instancesCache[$resource];
    }
    
    public function getStaticInstance ($resource)
    {
        if (empty($this->instancesCache[$resource]))
        {
            $reflectionClass = new ReflectionClass($this->getClass($resource));
            if ($reflectionClass->isAbstract())
                $this->instancesCache[$resource] = new StaticProxyClass($className);
            else if ($reflectionClass->hasMethod("getInstance"))
                $this->instancesCache[$resource] = $className::getInstance();
            else
                throw new Exception ("No se puede obtener una instancia estática del recurso \"" . $resource . "\"");
        }
        return $this->instancesCache[$resource];
    }
    
    private function getClass ($resource)
    {
        $pathSeparator = "/";
        $pathSeparatorPosition = strrpos($resource, $pathSeparator);
        $pathSeparatorPosition = ($pathSeparatorPosition != false)? ($pathSeparatorPosition+1) : 0;
        $className = ucfirst(substr($resource, $pathSeparatorPosition));
        if (!class_exists($className))
        {
            $relativeFilename = ($pathSeparatorPosition == 0? "" : (substr($resource, 0, $pathSeparatorPosition-1) . $pathSeparator)) . $className . ".php";
            $loaded = false;
            foreach ($this->paths as $path)
            {
                $filename = $path . $pathSeparator . $relativeFilename;
                try
                {
                    include($filename);
                    $loaded = true;
                    break;
                }
                catch (Exception $ex) {}
            }
            if (!$loaded)
                throw new Exception ("No se pudo obtener el recurso: " . $resource); 
        }
        return $className;
    }
}

class StaticProxyClass
{
    public function __construct ($proxyClassName) 
    {
        $this->className = $proxyClassName;
        $this->classData = new ReflectionClass($this->className);
    }
    
    public function __call ($method, $params) 
    {
        if (!$this->classData->hasMethod($method))
            throw new Exception ("No se encuentra el método \"" . $method . "\" en la clase \"" . $this->className . "\"");
        return call_user_func_array($this->className . "::" . $method, $params);   
    }
}

?>
