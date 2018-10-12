<?php

namespace NeoPHP\Messages;

/**
 * Class Messages
 * @package NeoPHP\Messages
 */
class Messages {

    const CAPITALIZATION_NONE = 1;
    const CAPITALIZATION_FIRST_CHAR = 2;
    const CAPITALIZATION_ALL = 3;

    private static $language;
    private static $messages;
    private static $messagesPath;

    /**
     * Messages initialization
     */
    private static function init() {
        $lang = isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])? substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : null;
        if (empty($lang))
            $lang = "es";
        self::$language = $lang;
        self::$messages = [];
        self::$messagesPath = get_property("app.messages_path", get_app()->resourcesPath() . DIRECTORY_SEPARATOR . "messages");
    }

    /**
     * @param $language
     */
    public static function language ($language=null) {
        if ($language != null) {
            self::$language = $language;
        }
        else {
            return self::$language;
        }
    }

    /**
     * @param $bundleName
     * @return string
     */
    private static function getBundleFilename ($bundleName) {
        $bundleNameTokens = explode(".", $bundleName);
        $messageBundleFileName = self::$messagesPath . DIRECTORY_SEPARATOR . self::$language;
        foreach ($bundleNameTokens as $bundleNameToken) {
            $messageBundleFileName .= DIRECTORY_SEPARATOR . $bundleNameToken;
        }
        $messageBundleFileName .= ".php";
        return $messageBundleFileName;
    }

    /**
     * @param $key
     * @param array $replacements
     * @return null
     */
    public static function get($key, ...$replacements) {
        $idx = strrpos($key, ".");
        $bundleName = null;
        $bundleKey = null;
        if ($idx === FALSE) {
            $bundleName = get_property("messages.default", "main");
            $bundleKey = $key;
        }
        else {
            $bundleName = substr($key, 0, $idx);
            $bundleKey = substr($key, $idx + 1);
        }

        $bundleNameTokens = explode(".", $bundleName);
        $messageBundle = &self::$messages[self::$language];
        $missingBundle = false;
        foreach ($bundleNameTokens as $bundleNameToken) {
            if (!isset($messageBundle[$bundleNameToken])) {
                $messageBundle[$bundleNameToken] = [];
                $missingBundle = true;
            }
            $messageBundle = &$messageBundle[$bundleNameToken];
        }

        if ($missingBundle) {
            $messageBundleFileName = self::getBundleFilename($bundleName);
            if (file_exists($messageBundleFileName)) {
                $messageBundle = @include_once($messageBundleFileName);
                $missingBundle = false;
            }
        }

        $bundleKeyValue = null;
        if (!$missingBundle) {

            $capitalization = self::CAPITALIZATION_NONE;
            if (ctype_upper($bundleKey{0})) {
                if ($bundleKey == strtoupper($bundleKey)) {
                    $capitalization = self::CAPITALIZATION_ALL;
                }
                else {
                    $capitalization = self::CAPITALIZATION_FIRST_CHAR;
                }
                $bundleKey = strtolower($bundleKey);
            }

            if (isset($messageBundle[$bundleKey])) {
                if (!empty($replacements)) {
                    $bundleKeyValue = call_user_func_array("sprintf", array_merge([$messageBundle[$bundleKey]], $replacements));
                }
                else {
                    $bundleKeyValue = $messageBundle[$bundleKey];
                }
                switch ($capitalization) {
                    case self::CAPITALIZATION_FIRST_CHAR:
                        $bundleKeyValue = ucfirst($bundleKeyValue);
                        break;
                    case self::CAPITALIZATION_ALL:
                        $bundleKeyValue = strtoupper($bundleKeyValue);
                        break;
                }
            }
            else {
                get_logger()->warning("Bundle key \"" . $bundleKey . "\" was not found in bundle file \"" . self::getBundleFilename($bundleName) . "\" !!");
                $bundleKeyValue = "[$key]";
            }
        }
        else {
            get_logger()->warning("Bundle file \"" . self::getBundleFilename($bundleName) . "\" was not found !!");
            $bundleKeyValue = "[$key]";
        }
        return $bundleKeyValue;
    }
}

$messagesClass = new \ReflectionClass(Messages::class);
$initMethod = $messagesClass->getMethod("init");
$initMethod->setAccessible(true);
$initMethod->invoke(null);