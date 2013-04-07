<?php

final class Loader
{
    public function __construct ($paths=array())
    {
        $this->paths = $paths;
        if (sizeof($this->paths) == 0)
            $this->paths[] = "";
    }
    
    public function getInstance ($resource, $params=array())
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
        $resourceInstance = null;
        $reflectionClass = new ReflectionClass($className);
        if ($reflectionClass->isInstantiable())
        {
            $resourceInstance = $reflectionClass->newInstanceArgs($params); 
        }
        else
        {
            if ($reflectionClass->isAbstract())
            {
                $resourceInstance = new StaticProxyClass($className);
            }
            else if ($reflectionClass->hasMethod("getInstance"))
            {
                $resourceInstance = $className::getInstance();
            }
        }
        return $resourceInstance;
    }
}

class StaticProxyClass
{
    public function __construct($proxyClassName) 
    {
        $this->className = $proxyClassName;
        $this->classData = new ReflectionClass($this->className);
    }
    
    public function __call($method, $params) 
    {
        if (!$this->classData->hasMethod($method))
            throw new Exception ("No se encuentra el método \"" . $method . "\" en la clase \"" . $this->className . "\"");
        return call_user_func_array($this->className . "::" . $method, $params);   
    }
}

?>
