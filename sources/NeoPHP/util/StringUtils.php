<?php

namespace NeoPHP\util;

abstract class StringUtils
{
    public static function startsWith ($haystack, $needle)
    {
        return strpos($haystack, $needle) === 0;
    }

    public static function endsWith ($haystack, $needle)
    {
        return substr($haystack, -strlen($needle)) === $needle;
    }
}

?>