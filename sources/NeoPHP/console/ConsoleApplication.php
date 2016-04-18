<?php

namespace NeoPHP\console;

use NeoPHP\app\Application;

class ConsoleApplication extends Application
{
    private $running = false;
    private $consoleManager;
    
    public function __construct($basePath) 
    {
        parent::__construct($basePath);
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
    
    public function start() 
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