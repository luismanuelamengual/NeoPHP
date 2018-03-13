<?php

namespace NeoPHP\Messages;

/**
 * Class Messages
 * @package NeoPHP\Messages
 */
class Messages {

    private static $language;
    private static $messages;
    private static $messagesPath;

    /**
     * Messages initialization
     */
    private static function init() {
        $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        if (empty($lang))
            $lang = "es";
        self::$language = $lang;
        self::$messages = [];
        self::$messagesPath = get_property("app.messagesPath", get_app()->resourcesPath() . DIRECTORY_SEPARATOR . "messages");
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
            if (isset($messageBundle[$bundleKey])) {
                if (sizeof($replacements) > 0) {
                    $bundleKeyValue = call_user_func_array("sprintf", array_merge([$messageBundle[$bundleKey]], $replacements));
                }
                else {
                    $bundleKeyValue = $messageBundle[$bundleKey];
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