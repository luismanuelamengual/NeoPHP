<?php

class Logger
{
    const LEVEL_ERROR = 1;
    const LEVEL_WARNING = 2;
    const LEVEL_NOTICE = 4;
    const LEVEL_INFO = 8;
    const LEVEL_FINE = 16;
    
    private static $instance;
    private $logsPath;
    private $logsMask;
    
    private function __construct() 
    {
        $this->setLogsPath(App::getInstance()->getBasePath() . DIRECTORY_SEPARATOR . "logs" . DIRECTORY_SEPARATOR);
        $this->setLogsMask(Logger::LEVEL_ERROR|Logger::LEVEL_WARNING);
    }

    public function setLogsPath ($logsPath)
    {
        $this->logsPath = $logsPath;
    }
    
    public function setLogsMask ($logsMask)
    {
        $this->logsMask = $logsMask;
    }
    
    public static function getInstance()
    {
        if (!isset(self::$instance))
            self::$instance = new self;
        return self::$instance;
    }
    
    public function log($level, $message)
    {
        if (($level & $this->logsMask) > 0)
        {
            $today = date("d.m.Y");
            if (is_writable($this->logsPath))
            {   
                $filename = $this->logsPath . "$today.txt";
                $content = "[" . date("d.m.Y h:i:s", mktime()) . "] " . $this->getLevelString($level) . ": " . $message ."\n";
                file_put_contents ($filename, $content ,FILE_APPEND);
            }
            else
            {
                throw new Exception ("Logs folder \"" . $this->logsPath . "\" not writable. Set permissions to the folder");
            }
        }
    }
    
    public function error ($message)
    {
        $this->log(Logger::LEVEL_ERROR, $message);
    }
    
    public function warning ($message)
    {
        $this->log(Logger::LEVEL_WARNING, $message);
    }
    
    public function notice ($message)
    {
        $this->log(Logger::LEVEL_NOTICE, $message);
    }
    
    public function info ($message)
    {
        $this->log(Logger::LEVEL_INFO, $message);
    }
    
    public function fine ($message)
    {
        $this->log(Logger::LEVEL_FINE, $message);
    }
    
    private function getLevelString ($level)
    {
        $levelString = "";
        switch ($level)
        {
            case Logger::LEVEL_ERROR: $levelString = "ERROR"; break;
            case Logger::LEVEL_WARNING: $levelString = "WARNING"; break;
            case Logger::LEVEL_NOTICE: $levelString = "NOTICE"; break;
            case Logger::LEVEL_INFO: $levelString = "INFO"; break;
            case Logger::LEVEL_FINE: $levelString = "FINE"; break;
        }
        return $levelString;
    }
}

?>
