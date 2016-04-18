<?php

namespace NeoPHP\core;

use JsonSerializable;

final class String extends Object implements JsonSerializable
{
    private $value;
    
    public function __construct ($value="") 
    {
        if (!is_string($value))
            throw new IllegalArgumentException ("Value \"$value\" is not a String");
        $this->value = $value;
    }
    
    public function jsonSerialize()
    {
        return $this->value;
    }
    
    public function equals (Object $string)
    { 
        return ($string instanceof String)? strcmp($this->toString(), $string->toString()) : false;
    }
    
    public static function valueOf ($var)
    {
        return strval($var);
    }
    
    public function clear ()
    {
        $this->value = "";
        return $this;
    }
    
    public function length ()
    {
        return strlen($this->value);
    }
    
    public function isEmpty ()
    {
        return empty($this->value);
    }
    
    public function charAt ($index)
    {
        return $this->value{$index};
    }
    
    public function concat ($string)
    {
        $this->value .= $string;
        return $this;
    }
    
    public function indexOf ($text, $offset=0)
    {
        return strpos($this->value, $text, $offset);
    }
    
    public function lastIndexOf ($text, $offset=0)
    {
        return strrpos($this->value, $text, $offset);
    }
    
    public function contains ($string)
    {
        return strstr($this->value, strval($string)) !== false;
    }
    
    public function matches ($regexp)
    {
        return preg_match($regexp, $this->value) === 1;
    }
    
    public function replaceAll ($regexp, $replacement)
    {
        return $this->replace($regexp, $replacement, -1);
    }
    
    public function replace ($regexp, $replacement, $limit=1)
    {
        return new String(preg_replace("/" . preg_quote($regexp, "/") . "/", $replacement, $this->value, $limit));
    }
    
    public function split ($regexp, $limit=-1)
    {
        $tokens = preg_split("/" . preg_quote($regexp, "/") . "/", $this->value, $limit);
        $stringTokens = array();
        foreach ($tokens as $token)
        {
            $stringTokens[] = new String($token);
        }
        return $stringTokens;
    }
    
    public static function join (array $strings, $delimiter="")
    {
        return new String(implode($delimiter, $strings));
    }
    
    public function substring ($beginIndex, $endIndex=null)
    {
        return new String(substr($this->value, $beginIndex, $endIndex != null? ($endIndex-$beginIndex) : $this->length()));
    }
        
    public function startsWith ($string)
    {
        return strpos($this->value, $string) === 0;
    }
    
    public function endsWith ($string)
    {
        return substr($this->value, -strlen($string)) === $string;
    }
    
    public function toLowerCase ()
    {
        return new String(strtolower($this->value));
    }
    
    public function toUpperCase ()
    {
        return new String(strtoupper($this->value));
    }
    
    public function trim ()
    {
        return new String(trim($this->value));
    }
    
    public function toString ()
    {
        return $this->value;
    }
}

?>