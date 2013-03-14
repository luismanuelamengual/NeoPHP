<?php

final class Logger
{
    const LEVEL_ERROR = 1;
    const LEVEL_WARNING = 2;
    const LEVEL_NOTICE = 4;
    const LEVEL_INFO = 8;
    const LEVEL_FINE = 16;
    
    private static $instance;
    private $logsFilePath;
    private $logsFileMask;
    private $logsPrintMask;
    
    private function __construct() 
    {
        $this->setLogsFilePath(App::getInstance()->getBasePath() . DIRECTORY_SEPARATOR . "logs" . DIRECTORY_SEPARATOR);
        $this->setLogsFileMask(Logger::LEVEL_ERROR|Logger::LEVEL_WARNING);
        $this->setLogsPrintMask(Logger::LEVEL_ERROR);
    }

    public function setLogsFilePath ($logsFilePath)
    {
        $this->logsFilePath = $logsFilePath;
    }
    
    public function setLogsFileMask ($logsFileMask)
    {
        $this->logsFileMask = $logsFileMask;
    }
    
    public function setLogsPrintMask ($logsPrintMask)
    {
        $this->logsPrintMask = $logsPrintMask;
    }
    
    public static function getInstance()
    {
        if (!isset(self::$instance))
            self::$instance = new self;
        return self::$instance;
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
    
    public function log($level, $message)
    {
        $messageContent = "[" . date("d.m.Y h:i:s", mktime()) . "] " . $this->getLevelString($level) . ": " . $message ."\n";
        if (($level & $this->logsPrintMask) > 0)
        {
            print($messageContent);
        }
        if (($level & $this->logsFileMask) > 0)
        {
            if (is_writable($this->logsFilePath))
                file_put_contents ($this->logsFilePath . date("d.m.Y") . ".txt", $messageContent, FILE_APPEND);
            else
                throw new Exception ('La carpeta de logs "' . $this->logsFilePath . '" no existe o no tiene permisos de escritura.');
        }
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
