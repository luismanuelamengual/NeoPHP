<?php

$sourcesDir = __DIR__ . DIRECTORY_SEPARATOR . "sources";
require_once ($sourcesDir . DIRECTORY_SEPARATOR . "NeoPHP" . DIRECTORY_SEPARATOR . "core" . DIRECTORY_SEPARATOR . "ClassLoader.php");
$classLoader = NeoPHP\core\ClassLoader::getInstance();
$classLoader->register();
if (!in_array($sourcesDir, $classLoader->getIncludePaths()))
    $classLoader->addIncludePath ($sourcesDir);

?>