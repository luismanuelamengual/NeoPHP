<?php

namespace NeoPHP\console;

use Exception;
use NeoPHP\app\Application;
use NeoPHP\core\System;
use NeoPHP\io\InputStream;
use NeoPHP\io\OutputStream;

class ConsoleApplication extends Application
{
    private $running = false;
    private $prompt;
    private $commandExecutors;
    private $inputStream;
    private $outputStream; 
    
    public function __construct($basePath) 
    {
        parent::__construct($basePath);
        set_time_limit(0);
        $this->commandExecutors = [];
        $this->prompt = "$ ";
    }
    
    public function start() 
    {
        $this->running = true;
        $this->onStarted();
        while ($this->running)
        {
            $this->enterCommand();
        }
        $this->stop();
    }
    
    public function stop ()
    {
        $this->running = false;
        $this->onStopped();
    }
    
    public function setPrompt ($prompt)
    {
        $this->prompt = $prompt;
    }
    
    public function getPrompt ()
    {
        return $this->prompt;
    }
    
    private function displayPrompt ()
    {
        echo $this->getPrompt();
    }
    
    public function readLine ()
    {
        $line = "";
        while ($this->getInputStream()->availiable())
        {
            $read = $this->getInputStream()->read();
            if ($read == "\n")
                break;
            $line .= $read;
        }
        return $line;
    }
    
    private function enterCommand ()
    {
        $this->displayPrompt();
        $tokens = $this->parseCommand($this->readLine());
        if (sizeof($tokens) > 0)            
            $this->onCommandEntered($tokens[0], array_slice ($tokens, 1));
    }
    
    private function parseCommand ($str)
    {
        $tokens = array();
        $tokenStart = -1;
        $inCommaToken = false;
        $commaChar = null;
        for ($index = 0; $index < strlen($str); $index++)
        {
            $char = substr($str, $index, 1);
            if (!$inCommaToken)
            {
                if (ctype_space($char))
                {
                    if ($tokenStart != -1)
                    {
                        $tokens[] = trim(substr($str, $tokenStart, ($index-$tokenStart))); 
                        $tokenStart = -1;
                    }
                }
                else if ($char == '"' || $char == '\'')
                {
                    if ($tokenStart != -1)
                        $tokens[] = trim(substr($str, $tokenStart, ($index-$tokenStart))); 
                    $tokenStart = $index+1;
                    $inCommaToken = true;
                    $commaChar = $char;
                }
                else if ($tokenStart == -1)
                {
                    $tokenStart = $index;
                }
            }
            else
            {
                if ($char == $commaChar)
                {
                    $tokens[] = trim(substr($str, $tokenStart, ($index-$tokenStart))); 
                    $inCommaToken = false;
                    $tokenStart = -1;
                }
            }
        }
        if ($tokenStart != -1 && !$inCommaToken)
            $tokens[] = trim(substr($str, $tokenStart));
        return $tokens;
    } 
    
    public function registerCommandExecutor (ConsoleCommandExecutor $commandExecutors)
    {
        $this->commandExecutors[] = $commandExecutors;
    }
    
    public function unregisterCommandExecutor (ConsoleListener $commandExecutor)
    {
        $index = array_search ($commandExecutor, $this->commandExecutors);
        if ($index != false)
            unset($this->commandExecutors[$index]);
    }
    
    public function getInputStream ()
    {
        if ($this->inputStream == null) 
            $this->inputStream = System::in();
        return $this->inputStream;
    }

    public function getOutputStream ()
    {
        if ($this->outputStream == null) 
            $this->outputStream = System::out();
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

    protected function onStarted ()
    {
    }
    
    protected function onStopped ()
    {
    }
    
    protected function onCommandEntered ($command, array $parameters = [])
    {
        foreach ($this->commandExecutors as $commandExecutor)
        {
            try 
            { 
                $commandExecutor->onCommandEntered ($this, $command, $parameters);
            }
            catch (Exception $ex) {}
        }
    }
}