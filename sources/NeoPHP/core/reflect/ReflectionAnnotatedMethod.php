<?php

namespace NeoPHP\core\reflect;

use NeoPHP\core\annotation\AnnotationParser;
use ReflectionMethod;

class ReflectionAnnotatedMethod extends ReflectionMethod 
{
    private $annotations;

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
    
    public function getDeclaringClass() 
    {
        return new ReflectionAnnotatedClass(parent::getDeclaringClass()->getName());
    }
}

?>
