<?php

namespace NeoPHP\util\translation;

use Exception;
use NeoPHP\core\Object;

class Translator extends Object
{
    private $language;
    private $resourcesPath;
    private $dictionary;
    
    public function __construct() 
    {
        $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        if (empty($lang))
            $lang = "es";
        $this->language = $lang;
        $this->resourcesPath = dirname($_SERVER["SCRIPT_FILENAME"]) . DIRECTORY_SEPARATOR . "resources";
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
            $dictionaryFilename = $this->resourcesPath . DIRECTORY_SEPARATOR . str_replace(".", DIRECTORY_SEPARATOR, $dictionaryName) . ".lan";
            try { $dictionaryData = @parse_ini_file($dictionaryFilename, true); } catch (Exception $ex) { throw new Exception('Resource file "' . $dictionaryFilename . '" is missing or could not be parsed !!'); }
            if (!empty($dictionaryData) && !empty($dictionaryData[$language]))
                foreach ($dictionaryData[$language] as $newDictionaryKey=>$newDictionaryText)
                    $this->dictionary[$language][$dictionaryName . "." . $newDictionaryKey] = $newDictionaryText;
            if (empty($this->dictionary[$language][$key]))
                throw new Exception('Entry "' . $dictionaryKey . '" for language "' . $language . '" not found in resource file "' . $dictionaryFilename . '"');
        }
        return $this->dictionary[$language][$key];
    }
}

?>
