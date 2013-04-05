<?php

class ProductionConnection extends Connection
{
    public function getDsn ()
    {
        return "mysql:host=localhost;dbname=" . App::getInstance()->getSettings()->title;
    }
    
    public function getUsername ()
    {
        return "root";
    }
    
    public function getPassword ()
    {
        return "root";
    }
    
    public function getDriverOptions ()
    {
        return array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8");
    }
}

?>
