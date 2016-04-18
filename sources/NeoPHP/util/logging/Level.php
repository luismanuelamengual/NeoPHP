<?php

namespace NeoPHP\util\logging;

final class Level
{
    public static $OFF;
    public static $FATAL;
    public static $SEVERE;
    public static $ERROR;
    public static $WARNING;
    public static $DEBUG;
    public static $NOTICE;
    public static $INFO;
    public static $FINE;
    public static $ALL;
    
    private $name;
    private $value;
    
    public static function initialize()
    {
        Level::$OFF = new Level("OFF", 10000);
        Level::$FATAL = new Level("FATAL", 900);
        Level::$SEVERE = new Level("SEVERE", 800);
        Level::$ERROR = new Level("ERROR", 700);
        Level::$WARNING = new Level("WARNING", 600);
        Level::$DEBUG = new Level("DEBUG", 500);
        Level::$NOTICE = new Level("NOTICE", 400);
        Level::$INFO = new Level("INFO", 300);
        Level::$FINE = new Level("FINE", 200);
        Level::$ALL = new Level("ALL", 0);
    }
    
    public function __construct ($name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }
    
    public function getName()
    {
        return $this->name;
    }

    public function getValue()
    {
        return $this->value;
    }
}

Level::initialize();

?>