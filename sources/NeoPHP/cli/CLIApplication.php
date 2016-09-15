<?php

namespace NeoPHP\cli;

use \Throwable;
use NeoPHP\app\Application;
use NeoPHP\core\System;
use NeoPHP\io\InputStream;
use NeoPHP\io\OutputStream;

class CLIApplication extends Application
{  
    private $inputStream;
    private $outputStream; 
    private $commands;
    
    public function __construct($basePath)
    {
        parent::__construct($basePath);
        $this->commands = [];
        $this->inputStream = System::in();
        $this->outputStream = System::out();
    }
    
    public function getInputStream ()
    {
        return $this->inputStream;
    }

    public function getOutputStream ()
    {
        return $this->outputStream;
    }

    public function setInputStream (InputStream $inputStream)
    {
        $this->inputStream = $inputStream;
    }

    public function setOutputStream (OutputStream $outputStream)
    {
        $this->outputStream = $outputStream;
    }
    
    public function registerCommand (Command $command)
    {
        $this->commands[$command->getName()] = $command;
    }
    
    public function unregisterCommand (Command $command)
    {
        unset($this->commands[$command->getName()]);
    }
    
    public function handleCommand () 
    {   
        $arguments = $GLOBALS["argv"];
        $commandName = !empty($arguments[1])? $arguments[1] : "main";
        $commandParameters = array_slice($arguments, 2);
        $this->processCommand($commandName, $commandParameters);
    }
    
    protected function processCommand ($commandName, array $commandParameters = [])
    {
        if (isset($this->commands[$commandName]))
        {
            $command = $this->commands[$commandName];
            try
            {
                $command->process($this, $commandParameters);
            }
            catch (Throwable $exception)
            {
                $this->onCommandError($command, $exception);
            }
        }
        else
        {
            $this->onCommandNotFound($commandName);
        }
    }
    
    protected function onCommandError (Command $command, Throwable $ex)
    {
        $this->getOutputStream()->println("Error: " . $ex->getMessage());
    }
    
    protected function onCommandNotFound ($commandName)
    {
        $this->getOutputStream()->println("Command \"$commandName\" not found !!");
    }
}