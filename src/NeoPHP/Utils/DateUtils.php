<?php

namespace Sitrack\Utils;

use DateTime;
use DateTimeZone;

abstract class DateUtils {

    private static $gmtTimeZone;

    public static function getGMTTimeZone () {
        if (empty(self::$gmtTimeZone)) {
            self::$gmtTimeZone = new DateTimeZone("GMT");
        }
        return self::$gmtTimeZone;
    }

    public static function getTimeZoneByOffset ($offset) {
        return empty($offset)? self::getGMTTimeZone() : DateTime::createFromFormat('O', $offset . ':00')->getTimezone();
    }
}