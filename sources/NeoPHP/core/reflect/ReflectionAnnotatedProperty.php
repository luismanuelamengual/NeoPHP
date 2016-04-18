<?php

namespace NeoPHP\core\reflect;

use NeoPHP\core\annotation\AnnotationParser;
use ReflectionProperty;

class ReflectionAnnotatedProperty extends ReflectionProperty 
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
