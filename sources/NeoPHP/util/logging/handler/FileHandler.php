<?php

namespace NeoPHP\util\logging\handler;

use NeoPHP\io\File;
use NeoPHP\util\logging\LogRecord;

class FileHandler extends Handler
{
    private $filename;
    
    public function __construct($filename)
    {
        parent::__construct();
        $this->filename = $filename;
    }

    public function getFilename ()
    {
        return $this->filename;
    }
    
    public function setFilename ($filename)
    {
        $this->filename = $filename;
    }
    
    public function execute(LogRecord $record)
    {
        $message = $this->getFormatter()->format($record) . "\n";
        $filename = preg_replace_callback('/{([^}]*)}/', function ($match) { return date(substr($match[0], 1, strlen($match[0])-2)); }, $this->filename);
        
        $logFile = new File($filename);
        $logFileParent = $logFile->getParentFile();
        if (!$logFileParent->exists())
            $logFileParent->mkdirs();
        $logFile->appendContent($message);
    }
}

?>