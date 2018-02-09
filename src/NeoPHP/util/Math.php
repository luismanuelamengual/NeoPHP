<?php

namespace NeoPHP\util;

abstract class Math
{
    public static function abs($value)
    {
        return abs($value);
    }
    
    public static function max($value1, $value2)
    {
        return max(array($value1, $value2));
    }
    
    public static function min($value1, $value2)
    {
        return min(array($value1, $value2));
    }
    
    public static function pow($base, $exp)
    {
        return pow($base, $exp);
    }
    
    public static function random($min, $max)
    {
        return rand($min, $max);
    }
    
    public static function ceil($value)
    {
        return ceil($value);
    }
    
    public static function floor($value)
    {
        return floor($value);
    }
    
    public static function round($value)
    {
        return round($value);
    } 
    
    public static function exp($value)
    {
        return exp($value);
    }
    
    public static function extm1($value)
    {
        return expm1($value);
    }
    
    public static function hypot($x, $y)
    {
        return hypot($x, $y);
    }
    
    public static function log($value)
    {
        return log($value);
    }
    
    public static function log10($value)
    {
        return log10($value);
    }
    
    public static function log1p($value)
    {
        return log1p($value);
    }
    
    public static function toDegrees ($radians)
    {
        return rad2deg($radians);
    }
    
    public static function toRadians ($degrees)
    {
        return deg2rad($degrees);
    }
    
    public static function sin($value)
    {
        return sin($value);
    }
    
    public static function sinh($value)
    {
        return sinh($value);
    }
    
    public static function cos($value)
    {
        return cos($value);
    }
    
    public static function cosh($value)
    {
        return cosh($value);
    }
    
    public static function tan($value)
    {
        return tan($value);
    }
    
    public static function tanh($value)
    {
        return tanh($value);
    }
    
    public static function acos($value)
    {
        return acos($value);
    }
    
    public static function asin($value)
    {
        return asin($value);
    }
    
    public static function atan($value)
    {
        return atan($value);
    }
    
    public static function atan2($dividend, $divisor)
    {
        return atan2($dividend, $divisor);
    }
}