<?php

final class Logger
{
    const ACTION_PRINT = 1;
    const ACTION_FILE = 2;
    const LEVEL_ERROR = 1;
    const LEVEL_WARNING = 2;
    const LEVEL_NOTICE = 4;
    const LEVEL_INFO = 8;
    const LEVEL_FINE = 16;
    
    private $actions = array();
    
    public function __construct() 
    {
        $printAction = new stdClass();
        $printAction->type = Logger::ACTION_PRINT;
        $printAction->mask = Logger::LEVEL_ERROR;
        $this->addAction($printAction);        
        $fileAction = new stdClass();
        $fileAction->type = Logger::ACTION_FILE;
        $fileAction->mask = Logger::LEVEL_ERROR|Logger::LEVEL_WARNING;
        $fileAction->filename = dirname($_SERVER["SCRIPT_FILENAME"]) . DIRECTORY_SEPARATOR . "logs" . DIRECTORY_SEPARATOR . "{dateFormat}.txt";
        $fileAction->filenameDateFormat = "Y-m-d";
        $this->addAction($fileAction);
    }
    
    public function clearActions ()
    {
        $this->actions = array();
    }
    
    public function addAction ($action)
    {
        array_push($this->actions, $action);
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
        $messageContent = "[" . date("Y-m-d h:i:s", mktime()) . "] " . $this->getLevelString($level) . ": " . $message ."\n";
        foreach ($this->actions as $action)
        {
            if (empty($action->mask) || (($level & $action->mask) > 0))
            {
                switch ($action->type)
                {
                    case Logger::ACTION_PRINT:
                        print($messageContent);
                        break;
                    case Logger::ACTION_FILE:
                        $filename = $action->filename;
                        $logsPath = dirname($filename);
                        if (is_writable($logsPath))
                        {
                            if (!empty($action->filenameDateFormat))
                                $filename = str_replace("{dateFormat}", date($action->filenameDateFormat), $filename);
                            file_put_contents ($filename, $messageContent, FILE_APPEND);
                        }
                        else
                        {
                            throw new Exception ('La carpeta de logs "' . $logsPath . '" no existe o no tiene permisos de escritura.');
                        }
                        break;
                }
            }
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
