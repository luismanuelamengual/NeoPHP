<?php

namespace NeoPHP\Utils;

use NeoPHP\FileSystem\FileInputStream;
use NeoPHP\FileSystem\FileOutputStream;
use NeoPHP\FileSystem\PrintStream;

/**
 * Class System
 * @package NeoPHP\Core
 */
abstract class SystemUtils {

    private static $in;
    private static $out;
    private static $err;

    /**
     * @return FileInputStream
     * @throws \NeoPHP\FileSystem\IOException
     */
    public static function in() {
        if (empty(self::$in)) {
            self::$in = new FileInputStream('php://stdin');
        }
        return self::$in;
    }

    /**
     * @return PrintStream
     * @throws \NeoPHP\FileSystem\IOException
     */
    public static function out() {
        if (empty(self::$out)) {
            self::$out = new PrintStream(new FileOutputStream('php://stdout'));
        }
        return self::$out;
    }

    /**
     * @return PrintStream
     * @throws \NeoPHP\FileSystem\IOException
     */
    public static function err() {
        if (empty(self::$err)) {
            self::$err = new PrintStream(new FileOutputStream('php://stderr'));
        }
        return self::$err;
    }

    /**
     * @return int
     */
    public static function currentTime() {
        return time();
    }

    /**
     * @return int
     */
    public static function currentTimeInMillis() {
        list($usec, $sec) = explode(' ', microtime());
        return (int)((int)$sec * 1000 + ((float)$usec * 1000));
    }

    /**
     * @param $priority
     * @param $message
     * @return bool
     */
    public static function log($priority, $message) {
        return syslog($priority, $message);
    }

    /**
     * @param $command
     * @param null $return
     * @return bool|string
     */
    public static function execute($command, &$return = null) {
        return system($command, $return);
    }

    /**
     * @return string
     */
    public static function getTempDir() {
        return sys_get_temp_dir();
    }
}