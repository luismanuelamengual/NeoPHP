<?php

namespace NeoPHP\Exceptions;

use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use Throwable;

abstract class Exceptions {

    /**
     * @param Throwable $exception
     * @throws ReflectionException
     */
    public static function flatten (Throwable $exception) {
        $traceProperty = (new ReflectionClass('Exception'))->getProperty('trace');
        $traceProperty->setAccessible(true);
        do {
            $trace = $traceProperty->getValue($exception);
            foreach($trace as &$call) {
                array_walk_recursive($call['args'], [Exceptions::class, 'flattenArgument']);
            }
            $traceProperty->setValue($exception, $trace);
        } while($exception = $exception->getPrevious());
        $traceProperty->setAccessible(false);
    }

    /**
     * Limpia de Clousures un argumento para que pueda ser serializable
     * @param mixed $value Valor del argumento
     * @param mixed $key Llave del argumento
     * @throws ReflectionException
     */
    private static function flattenArgument (&$value, $key) {
        if ($value instanceof Closure) {
            $closureReflection = new ReflectionFunction($value);
            $value = sprintf('(Closure at %s:%s)', $closureReflection->getFileName(), $closureReflection->getStartLine());
        } elseif (is_object($value)) {
            $value = sprintf('object(%s)', get_class($value));
        } elseif (is_resource($value)) {
            $value = sprintf('resource(%s)', get_resource_type($value));
        }
    }
}