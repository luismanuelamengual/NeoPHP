<?php

namespace NeoPHP\Log;

use Cascade\Cascade;
use Monolog\Logger;

/**
 * Class Logs
 * @package NeoPHP\Log
 */
abstract class Logs {

    private static $loggers = [];
    private static $initialized = false;

    /**
     * @param null $loggerName
     * @return Logger
     */
    public static function getLogger($loggerName=null): Logger {
        if (!self::$initialized) {
            $loggingConfig = config("logging");
            Cascade::fileConfig($loggingConfig);
            self::$initialized = true;
        }
        if ($loggerName == null) {
            $loggerName = config("logging.default") ?: "main";
        }
        if (isset(self::$loggers[$loggerName])) {
            self::$loggers[$loggerName] = Cascade::getLogger($loggerName);
        }
        return self::$loggers[$loggerName];
    }
}