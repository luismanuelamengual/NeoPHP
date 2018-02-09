<?php

namespace NeoPHP\core;

use NeoPHP\io\FileInputStream;
use NeoPHP\io\FileOutputStream;
use NeoPHP\io\PrintStream;

abstract class System
{
    private static $in;
    private static $out;
    private static $err;
    
    /**
     * Obtiene un stream de entrada
     * @return FileInputStream stream de entrada
     */
    public static function in()
    {
        if (empty(self::$in))
        {
            self::$in = new FileInputStream('php://stdin');
        }
        return self::$in;
    }
    
    /**
     * Obtiene un stream de salida
     * @return PrintStream stream de salida
     */
    public static function out()
    {
        if (empty(self::$out))
        {
            self::$out = new PrintStream(new FileOutputStream('php://stdout'));
        }
        return self::$out;
    }
    
    /**
     * Obtiene un stream de error
     * @return PrintStream stream de error
     */
    public static function err()
    {
        if (empty(self::$err))
        {
            self::$err = new PrintStream(new FileOutputStream('php://stderr'));
        }
        return self::$err;
    }
    
    public static function currentTime()
    {
        return time();
    }
    
    public static function currentTimeInMillis()
    {
        list($usec, $sec) = explode(' ', microtime());
        return (int) ((int) $sec * 1000 + ((float) $usec * 1000));
    }
    
    public static function log($prority, $message)
    {
        return syslog($priority, $message);
    }
    
    public static function execute ($command, &$return=null)
    {
        return system($command, $return);
    }
    
    public static function getTempDir ()
    {
        return sys_get_temp_dir();
    }
}