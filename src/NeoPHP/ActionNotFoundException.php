<?php

namespace NeoPHP;

use Exception;

class ActionNotFoundException extends Exception {
    public function __construct(string $message = "", int $code = Response::HTTP_NOT_FOUND, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}