<?php

namespace NeoPHP\Messages;

class Messages {

    private static $language;
    private static $messages;
    private static $messagesPath;

    private static function init() {
        $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        if (empty($lang))
            $lang = "es";
        self::$language = $lang;
        self::$messages = [];
        self::$messagesPath = get_property("app.messagesPath", get_app()->resourcesPath() . DIRECTORY_SEPARATOR . "messages");
    }

    public static function setLanguage($language) {
        self::$language = $language;
    }

    public static function getLanguage() {
        return self::$language;
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
            $bundleKey = substr($key, $idx+1);
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
            $messageBundleFileName = self::$messagesPath . DIRECTORY_SEPARATOR . self::$language;
            foreach ($bundleNameTokens as $bundleNameToken) {
                $messageBundleFileName .= DIRECTORY_SEPARATOR . $bundleNameToken;
            }
            $messageBundleFileName .= ".php";
            if (file_exists($messageBundleFileName)) {
                $messageBundle = @include_once($messageBundleFileName);
                $missingBundle = false;
            }
            else {
                get_logger()->warning("Bundle file \"$messageBundleFileName\" was not found while trying to retrieve bundle key \"$key\" !!");
            }
        }

        $bundleKeyValue = null;
        if (!$missingBundle) {
            if (isset($messageBundle[$bundleKey])) {
                if (sizeof($replacements) > 0) {
                    $bundleKeyValue = call_user_func_array("sprintf", array_merge([$messageBundle[$bundleKey]], $replacements));
                }
                else {
                    $bundleKeyValue = $messageBundle[$bundleKey];
                }
            }
            else {
                get_logger()->warning("Bundle key \"$key\" was not found !!");
                $bundleKeyValue = "[$key]";
            }
        }
        else {
            $bundleKeyValue = "[$key]";
        }
        return $bundleKeyValue;
    }
}

$messagesClass = new \ReflectionClass(Messages::class);
$initMethod = $messagesClass->getMethod("init");
$initMethod->setAccessible(true);
$initMethod->invoke(null);
