<?php

final class Translator
{
    private $language;
    private $resourcesPath;
    private $dictionary;
    
    public function __construct() 
    {
        $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        if (empty($lang))
            $lang = "es";
        $this->setLanguage($lang);
        $this->setResourcesPath("resources");
    }

    public function setLanguage ($language)
    {
        $this->language = $language;
    }
    
    public function getLanguage ()
    {
        return $this->language;
    }
    
    public function setResourcesPath ($resourcesPath)
    {
        $this->resourcesPath = $resourcesPath;
    }
    
    public function getResourcesPath ()
    {
        return $this->resourcesPath;
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
            $dictionaryFilename = $this->getResourcesPath() . DIRECTORY_SEPARATOR . str_replace(".", DIRECTORY_SEPARATOR, $dictionaryName) . ".lan";
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
