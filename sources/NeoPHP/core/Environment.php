<?php

namespace NeoPHP\core;

abstract class Environment
{
    public static function getBaseDir ()
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $path = $backtrace[0]["file"];
        $callingClass = $backtrace[1]["class"];
        $callingClassFolderLevel = substr_count($callingClass, "\\");
        for ($i = 0; $i <= $callingClassFolderLevel; $i++)
            $path = dirname($path);
        return $path;
    }
}