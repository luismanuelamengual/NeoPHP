<?php

namespace NeoPHP\Exceptions;

use Exception;
use ReflectionException;
use Throwable;

/**
 * Class SerializableException
 * @package Sitrack
 */
class SerializableException extends Exception {

    /**
     * SerializableException constructor.
     * @param string $message Mensaje de error
     * @param int $code Código de error
     * @param Throwable|null $previous Error previo
     * @throws ReflectionException
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
        Exceptions::flatten($this);
    }
}