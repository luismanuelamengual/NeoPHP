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
    
    public function close ()
    {
        $this->running = false;
    }
    
    public function start() 
    {
        $this->running = true;
        while ($this->running)
        {
            $this->enterCommand();
        }
        $this->stop();
    }
    
    protected function enterCommand() 
    {
        $this->consoleManager->enterCommand();
    } 
    
    public function addConsoleListener (ConsoleListener $listener)
    {
        $this->consoleManager->addConsoleListener($listener);
    }
}