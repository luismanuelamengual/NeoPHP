<?php

namespace NeoPHP\util\logging;

use Exception;
use NeoPHP\core\Object;
use NeoPHP\util\logging\handler\Handler;

class Logger extends Object
{
    private $name;
    private $levelValue;
    private $handlers;
    
    public function __construct($name="") 
    {
        $this->name = $name;
        $this->levelValue = Level::$ALL->getValue();
        $this->handlers = array();
    }
    
    public function getName()
    {
        return $this->name;
    }

    public function getLevelValue()
    {
        return $this->levelValue;
    }

    public function getHandlers()
    {
        return $this->handlers;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setLevelValue($level)
    {
        $this->levelValue = $level;
    }

    public function addHandler (Handler $handler)
    {
        $this->handlers[] = $handler;
    }

    public function fatal ($message, Exception $exception = null)
    {
        $this->log(Level::$FATAL, $message, $exception);
    }
    
    public function severe ($message, Exception $exception = null)
    {
        $this->log(Level::$SEVERE, $message, $exception);
    }
    
    public function error ($message, Exception $exception = null)
    {
        $this->log(Level::$ERROR, $message, $exception);
    }
    
    public function warning ($message, Exception $exception = null)
    {
        $this->log(Level::$WARNING, $message, $exception);
    }
    
    public function notice ($message, Exception $exception = null)
    {
        $this->log(Level::$NOTICE, $message, $exception);
    }
    
    public function info ($message)
    {
        $this->log(Level::$INFO, $message);
    }
    
    public function fine ($message)
    {
        $this->log(Level::$FINE, $message);
    }
    
    public function log(Level $level, $message, Exception $exception = null)
    {
        if ($this->levelValue <= $level->getValue())
        {
            $record = new LogRecord($level, $message, $exception);
            foreach ($this->handlers as $handler)
            {
                $handler->notify($record);
            }
        }
    }
}

?>
