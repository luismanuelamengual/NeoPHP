<?php

namespace NeoPHP\Messages;

use Exception;

class Messages {

    private $language;
    private $resourcePaths;
    private $dictionary;

    public function __construct() {

        $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        if (empty($lang))
            $lang = "es";
        $this->language = $lang;
        $this->resourcePaths = [];
    }

    public function setLanguage($language) {
        $this->language = $language;
    }

    public function getLanguage() {
        return $this->language;
    }

    public function addResourcePath($resourcePath) {
        $this->resourcePaths[] = $resourcePath;
    }

    public function getResourcePaths() {
        return $this->resourcePaths;
    }

    /**
     * @param $key
     * @param null $language
     * @return mixed
     * @throws Exception
     */
    public function getMessage($key, $language = null) {

        if (empty($language)) {
            $language = $this->language;
        }
        if (strpos($key, ".") === FALSE) {
            $key = "general." . $key;
        }
        if (empty($this->dictionary[$language][$key])) {
            $dictionarySeparator = strrpos($key, ".");
            $dictionaryName = substr($key, 0, $dictionarySeparator);
            $dictionaryKey = substr($key, $dictionarySeparator + 1, strlen($key));
            $dictionaryRelativeFilename = str_replace(".", DIRECTORY_SEPARATOR, $dictionaryName) . ".lan";
            $dictionaryFileFound = false;

            foreach ($this->resourcePaths as $resourcePath) {
                $dictionaryFilename = $resourcePath . DIRECTORY_SEPARATOR . $dictionaryRelativeFilename;
                if (file_exists($dictionaryFilename)) {
                    try {
                        $dictionaryData = @parse_ini_file($dictionaryFilename, true);
                        if (!empty($dictionaryData) && !empty($dictionaryData[$language]))
                            foreach ($dictionaryData[$language] as $newDictionaryKey => $newDictionaryText)
                                $this->dictionary[$language][$dictionaryName . "." . $newDictionaryKey] = $newDictionaryText;
                    }
                    catch (Exception $ex) {
                        throw new Exception("Resource file \"$dictionaryFilename\" could not be parsed");
                    }
                    $dictionaryFileFound = true;
                    break;
                }
            }

            if (!$dictionaryFileFound) {
                throw new Exception ("Resource file \"$dictionaryRelativeFilename\" not found in the translations resource paths");
            }

            if (empty($this->dictionary[$language][$key])) {
                throw new Exception('Entry "' . $dictionaryKey . '" for language "' . $language . '" not found in the resource files');
            }
        }
        return $this->dictionary[$language][$key];
    }
}
