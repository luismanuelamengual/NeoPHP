<?php

class Translator
{
    private $language;
    private $dictionary;
    private static $instance;
    
    private function __construct() 
    {
        $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        if (empty($lang))
            $lang = "es";
        $this->setLanguage($lang);
    }

    public static function getInstance()
    {
        if (!isset(self::$instance))
            self::$instance = new self;
        return self::$instance;
    }
    
    public function setLanguage ($language)
    {
        $this->language = $language;
        $this->dictionary = array();
    }
    
    public function getLanguage ()
    {
        return $this->language;
    }
    
    public function getText ($key)
    {
        if (strpos($key, ".") === FALSE)
            $key = "default." . $key;
        if (empty($this->dictionary[$key]))
        {
            $dictionaryName = "";
            $dictionaryKey = "";
            $dictionarySeparator = strrpos($key, ".");
            $dictionaryName = substr($key,0,$dictionarySeparator);
            $dictionaryKey = substr($key,$dictionarySeparator+1,strlen($key));
            $dictionaryFilename = "app/resources/" . str_replace(".", "/", $dictionaryName) . ".ini";
            $dictionaryData = @parse_ini_file($dictionaryFilename, true);
            if ($dictionaryData != FALSE)
            {
                foreach ($dictionaryData as $dictionaryLanguage=>$dictionaryTexts)
                {
                    if ($dictionaryLanguage == $this->language)
                    {
                        foreach ($dictionaryTexts as $newDictionaryKey=>$newDictionaryText)
                            $this->dictionary[$dictionaryName . "." . $newDictionaryKey] = $newDictionaryText;
                    }
                }
            }
            if (empty($this->dictionary[$key]))
                $this->dictionary[$key] = "{" . $key . "}";
        }
        return $this->dictionary[$key];
    }
}

?>
