<?php

require_once ("/home/luis/git/NeoPHP2/sources/bootstrap.php");
NeoPHP\ClassLoader::getInstance()->addIncludePath(__DIR__."/src");
com\neophp\NeoApplication::getInstance()->start();

?>