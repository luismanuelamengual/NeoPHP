<?php

namespace NeoPHP\console;

class ConsoleManager implements ConsoleListener
{
    private $listeners;
    private $stream;
    private $prompt;
    
    public function __construct ($blocking = true) 
    {
        $this->listeners = array();
        $this->prompt = "$ ";
    }
    
    public function run ()
    {
        $this->running = true;
        while ($this->running)
            $this->enterCommand();
    }
    
    public function stop ()
    {
        $this->running = false;
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
    
    public function displayPrompt ()
    {
        echo $this->getPrompt();
    }
    
    public function enterCommand ()
    {
        $this->displayPrompt();
        $commandEntered = false;
        while (!$commandEntered)
            $commandEntered = $this->checkCommand();
    }
    
    public function checkCommand ()
    {
        $commandReceived = false;
        $line = fgets($this->getStream());
        if ($line != false)
        {
            $tokens = $this->parseCommand($line);
            if (sizeof($tokens) > 0)            
                $this->onCommand($tokens[0], array_slice ($tokens, 1));
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
    
    public function addConsoleListener (ConsoleListener $listener)
    {
        $this->listeners[] = $listener;
    }
    
    public function removeConsoleListener (ConsoleListener $listener)
    {
        $index = array_search ($listener, $this->listeners);
        if ($index != false)
            unset($this->listeners[$index]);
    }
    
    public function onCommand ($command, $parameters)
    {
        foreach ($this->listeners as $consoleListener)
        {
            try 
            { 
                $consoleListener->onCommand ($command, $parameters);
            }
            catch (Exception $ex) {}
        }
    }
}

?>
