<?php

namespace NeoPHP\core\reflect;

use NeoPHP\core\annotation\AnnotationParser;
use ReflectionMethod;

class ReflectionAnnotatedMethod extends ReflectionMethod 
{
    private $annotations;

    public function getAllAnnotations() 
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
        $annotations = $this->getAllAnnotations();
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
    
    public function getAnnotations ($name)
    {
        $annotations = [];
        foreach ($this->getAllAnnotations() as $searchAnnotation)
        {
            if ($searchAnnotation->getName() == $name)
                $annotations[] = $searchAnnotation;
        }
        return $annotations;
    }
    
    public function getDeclaringClass() 
    {
        return new ReflectionAnnotatedClass(parent::getDeclaringClass()->getName());
    }
}

?>
