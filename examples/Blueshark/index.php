<?php
require_once ("NeoPHP/App.php");
App::getInstance()->getSettings()->title = "Blueshark";
App::getInstance()->handleRequest();
?>
