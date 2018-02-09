<?php

namespace NeoPHP\util\logging\handler;

use Exception;
use NeoPHP\util\logging\formatter\Formatter;
use NeoPHP\util\logging\formatter\SimpleFormatter;
use NeoPHP\util\logging\Level;
use NeoPHP\util\logging\LogRecord;

abstract class Handler
{
    private $formatter;
    private $levelValue;
    
    public function __construct (Formatter $formatter = null) 
    {
        $this->formatter = $formatter;
        $this->levelValue = Level::$ALL->getValue();
        if (empty($this->formatter))
            $this->formatter = new SimpleFormatter();
    }
    
    public function getLevelValue()
    {
        return $this->levelValue;
    }
    
    public function setLevelValue($level)
    {
        $this->levelValue = $level;
    }
    
    public function getFormatter()
    {
        return $this->formatter;
    }

    public function setFormatter($formatter)
    {
        $this->formatter = $formatter;
    }
    
    public function notify (LogRecord $record)
    {
        try 
        {
            if ($this->levelValue <= $record->getLevel()->getValue())
                $this->execute ($record);
        } 
        catch (Exception $ex) 
        {
            print ($ex);
        }
    }

    protected abstract function execute (LogRecord $record);
}

?>