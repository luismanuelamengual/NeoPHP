<?php

namespace NeoPHP\core\reflect;

use NeoPHP\core\annotation\AnnotationParser;
use ReflectionClass;

class ReflectionAnnotatedClass extends ReflectionClass
{
    private $annotations;
    private $annotatedProperties;
    private $annotatedMethods;

    public function getAnnotations() 
    {
        if ($this->annotations == null)
            $this->annotations = AnnotationParser::getAnnotations ($this->getDocComment());
        return $this->annotations;
    }
    
    public function hasAnnotation ($name) 
    {
        return $this->getAnnotation($name) != null;
    }

    public function getAnnotation ($name) 
    {
        $annotation = null;
        $annotations = $this->getAnnotations();
        foreach ($annotations as $searchAnnotation)
        {
            if ($searchAnnotation->getName() == $name)
            {
                $annotation = $searchAnnotation;
                break;
            }
        }
        return $annotation;
    }
    
    public function getConstructor () 
    {
        return $this->createReflectionAnnotatedMethod(parent::getConstructor());
    }
    
    public function getMethod ($name) 
    {
        $methods = $this->getMethods();
        return $methods[$name];
    }

    public function getMethods ($filter = -1) 
    {
        if (!isset($this->annotatedMethods))
        {
            $this->annotatedMethods = array();
            foreach(parent::getMethods($filter) as $method) 
                $this->annotatedMethods[$method->getName()] = $this->createReflectionAnnotatedMethod($method);
        }
        return $this->annotatedMethods;
    }

    public function getProperty ($name) 
    {
        $properties = $this->getProperties();
        return $properties[$name];
    }

    public function getProperties ($filter = -1) 
    {
        if (!isset($this->annotatedProperties))
        {
            $this->annotatedProperties = array();
            foreach(parent::getProperties($filter) as $property) 
                $this->annotatedProperties[$property->getName()] = $this->createReflectionAnnotatedProperty($property);
        }
        return $this->annotatedProperties;
    }

    public function getInterfaces () 
    {
        $result = array();
        foreach(parent::getInterfaces() as $interface) 
            $result[] = $this->createReflectionAnnotatedClass($interface);
        return $result;
    }

    public function getParentClass () 
    {
        return $this->createReflectionAnnotatedClass(parent::getParentClass());
    }
    
    private function createReflectionAnnotatedClass ($class) 
    {
        return ($class !== false) ? new ReflectionAnnotatedClass($class->getName()) : false;
    }

    private function createReflectionAnnotatedMethod ($method) 
    {
        return ($method !== null) ? new ReflectionAnnotatedMethod($this->getName(), $method->getName()) : null;
    }

    private function createReflectionAnnotatedProperty ($property) 
    {
        return ($property !== null) ? new ReflectionAnnotatedProperty($this->getName(), $property->getName()) : null;
    }
}

?>
