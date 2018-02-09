<?php

namespace NeoPHP\cli;

use \Throwable;
use NeoPHP\io\InputStream;
use NeoPHP\io\OutputStream;

abstract class Command
{
    private $name;
    private $description;
    private $helpText;
    
    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getHelpText()
    {
        return $this->helpText;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function setHelpText($helpText)
    {
        $this->helpText = $helpText;
    }
    
    public function process (CLIApplication $application, array $parameters = [])
    {
        try
        {
            $in = $application->getInputStream();
            $out = $application->getOutputStream();
            $options = $parameters;
            $this->onBeforeExecution($application, $in, $out, $options);
            $this->execute($application, $in, $out, $options);
            $this->onAfterExecution($application, $in, $out, $options);
        }
        catch (Throwable $exeption)
        {
            $this->onError($exeption);
        }
    }
    
    protected function onError (Throwable $error)
    {
        throw $error;
    }
    
    protected function onBeforeExecution (CLIApplication $application, InputStream $in, OutputStream $out, array $options = []) {}
    protected function onAfterExecution (CLIApplication $application, InputStream $in, OutputStream $out, array $options = []) {}
    protected abstract function execute (CLIApplication $application, InputStream $in, OutputStream $out, array $options = []);
}