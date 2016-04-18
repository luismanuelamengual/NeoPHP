<?php

namespace NeoPHP\util\logging;

use Exception;

final class LogRecord
{
    private $level;
    private $message;
    private $exception;
    private $timestamp;
    
    public function __construct(Level $level, $message, Exception $exception = null)
    {
        $this->timestamp = time();
        $this->level = $level;
        $this->message = $message;
        $this->exception = $exception;
    }
    
    public function getLevel()
    {
        return $this->level;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getException()
    {
        return $this->exception;
    }

    public function getTimestamp()
    {
        return $this->timestamp;
    }
}