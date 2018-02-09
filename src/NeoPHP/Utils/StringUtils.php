<?php

namespace NeoPHP\Utils;

abstract class StringUtils {

    /**
     * @param $haystack string to search for
     * @param $needle string to search for
     * @return bool if haystack
     */
    public static function startsWith($haystack, $needle) {
        return strpos($haystack, $needle) === 0;
    }

    /**
     * @param $haystack string to search for
     * @param $needle string to search for
     * @return bool
     */
    public static function endsWith($haystack, $needle) {
        return substr($haystack, -strlen($needle)) === $needle;
    }
}