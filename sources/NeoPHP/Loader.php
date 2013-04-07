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
            $relativePath = $pathSeparatorPosition == 0? "" : substr($resource, 0, $pathSeparatorPosition-1);
            $loaded = false;
            foreach ($this->paths as $path)
            {
                $filename = $path;
                if (!empty($relativePath))
                    $filename .= $pathSeparator . $relativePath;
                $filename .= $pathSeparator . $className . ".php";
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
    public function __construct($proxyClass) 
    {
        $this->proxyClass = $proxyClass;
    }
    
    public function __call($method, $params) 
    {
        return call_user_func_array($this->proxyClass . "::$method", $params);
    }
}

?>
