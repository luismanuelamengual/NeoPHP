<?php

class Session
{
    private static $instance;

    public static function getInstance()
    {
        if (!isset(self::$instance))
            self::$instance = new self;
        return self::$instance;
    }
    
    public function getName ()
    {
        return session_name();
    }
    
    public function getId ()
    {
        return session_id();
    }
    
    public function __set($name, $value)
    {
        $_SESSION[$name] = $value;
    }

    public function __get($name)
    {
        return (isset($_SESSION[$name]))? $_SESSION[$name] : false;
    }
    
    public function __isset($name)
    {
        return isset($_SESSION[$name]);
    }
   
    public function __unset($name)
    {
        unset($_SESSION[$name] );
    }

    public function start ()
    {
        try { session_start(); } catch (Exception $ex) {}
    }
    
    public function destroy()
    {
        session_destroy();
    }

    public function isStarted ()
    {
        return sizeof($_SESSION) > 0;
    }
    
    public function getSessionVars ()
    {
        return $_SESSION;
    }
}

?>
