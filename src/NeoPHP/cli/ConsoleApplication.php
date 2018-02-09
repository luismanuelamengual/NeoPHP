<?php

namespace NeoPHP\cli;

use NeoPHP\cli\CLIApplication;

class ConsoleApplication extends CLIApplication
{
    private $running = false;
    private $prompt;
    
    public function __construct($basePath) 
    {
        parent::__construct($basePath);
        set_time_limit(0);
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
        $this->onStopped();
    }
    
    public function stop ()
    {
        $this->running = false;
    }
    
    public function setPrompt ($prompt)
    {
        $this->prompt = $prompt;
    }
    
    public function getPrompt ()
    {
        return $this->prompt;
    }
    
    private function enterCommand ()
    {
        $this->getOutputStream()->printb($this->getPrompt());
        $tokens = $this->parseCommand($this->getInputStream()->read(1000));
        if (sizeof($tokens) > 0)  
            $this->processCommand($tokens[0], array_slice ($tokens, 1));
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
    

    protected function onStarted () {}
    protected function onStopped () {}
}