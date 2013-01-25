<?php
require_once ("app/App.php");
App::getInstance()->executeAction((!empty($_REQUEST['action'])? $_REQUEST['action'] : null));
?>
