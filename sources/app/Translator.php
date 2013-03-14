<?php

final class Translator
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
    }
    
    public function getLanguage ()
    {
        return $this->language;
    }
    
    public function getText ($key, $language = null)
    {
        if (empty($language))
            $language = $this->language;
        if (strpos($key, ".") === FALSE)
            $key = "general." . $key;
        if (empty($this->dictionary[$language][$key]))
        {
            $dictionaryName = "";
            $dictionaryKey = "";
            $dictionarySeparator = strrpos($key, ".");
            $dictionaryName = substr($key,0,$dictionarySeparator);
            $dictionaryKey = substr($key,$dictionarySeparator+1,strlen($key));
            $dictionaryFilename = "app/resources/" . str_replace(".", "/", $dictionaryName) . ".lan";
            try { $dictionaryData = @parse_ini_file($dictionaryFilename, true); } catch (Exception $ex) {}
            if (!empty($dictionaryData) && !empty($dictionaryData[$language]))
                foreach ($dictionaryData[$language] as $newDictionaryKey=>$newDictionaryText)
                    $this->dictionary[$language][$dictionaryName . "." . $newDictionaryKey] = $newDictionaryText;
            if (empty($this->dictionary[$language][$key]))
                $this->dictionary[$language][$key] = "{" . $key . "[" . $language . "]}";
        }
        return $this->dictionary[$language][$key];
    }
}

?>
