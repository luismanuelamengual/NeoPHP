<?php

class Session
{
    private static $instance;
   
    private function __construct()
    {
        $this->startSession();
    }

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
    
    public function startSession()
    {
        try { session_start(); } catch (Exception $ex) {}
    }

    public function __set($name, $value)
    {
        $_SESSION[$name] = $value;
    }

    public function __get($name)
    {
        if ( isset($_SESSION[$name]))
            return $_SESSION[$name];
    }
    
    public function __isset($name)
    {
        return isset($_SESSION[$name]);
    }
   
    public function __unset($name)
    {
        unset( $_SESSION[$name] );
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
