<?php

namespace NeoPHP\console;

use NeoPHP\app\ProcessorApplication;

class ConsoleApplication extends ProcessorApplication
{
    private $running = false;
    private $consoleManager;
    
    public function __construct() 
    {
        parent::__construct();
        $this->consoleManager = new ConsoleManager();
    }
    
    protected function configure ()
    {
        parent::configure();
        set_time_limit(0);
    }
    
    public function getConsoleManager()
    {
        return $this->consoleManager;
    }
    
    public function close ()
    {
        $this->running = false;
    }
    
    public function onStarted() 
    {
        parent::onStarted();
        $this->running = true;
        while ($this->running)
            $this->onIdle();
        $this->stop();
    }
    
    public function onIdle() 
    {
        $this->consoleManager->enterCommand();
    } 
}

?>
