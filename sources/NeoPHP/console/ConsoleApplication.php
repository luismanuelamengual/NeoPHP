<?php

namespace NeoPHP\console;

use Exception;
use NeoPHP\app\Application;

class ConsoleApplication extends Application
{
    private $running = false;
    private $stream;
    private $prompt;
    private $commandExecutors;
    
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
    
    public function setBlocking ($blocking)
    {
        stream_set_blocking($this->getStream(), $blocking);
    }
    
    public function setPrompt ($prompt)
    {
        $this->prompt = $prompt;
    }
    
    public function getPrompt ()
    {
        return $this->prompt;
    }
    
    private function getStream ()
    {
        if ($this->stream == null)
            $this->stream = fopen('php://stdin', 'r');
        return $this->stream;
    }
    
    private function displayPrompt ()
    {
        echo $this->getPrompt();
    }
    
    private function enterCommand ()
    {
        $this->displayPrompt();
        $commandEntered = false;
        while (!$commandEntered)
            $commandEntered = $this->checkCommand();
    }
    
    private function checkCommand ()
    {
        $commandReceived = false;
        $line = fgets($this->getStream());
        if ($line != false)
        {
            $tokens = $this->parseCommand($line);
            if (sizeof($tokens) > 0)            
                $this->onCommandEntered($tokens[0], array_slice ($tokens, 1));
            $commandReceived = true;
        }
        return $commandReceived;
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