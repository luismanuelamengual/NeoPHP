<?php

namespace NeoPHP\net;

use NeoPHP\app\ProcessorApplication;
use NeoPHP\console\ConsoleManager;

class NetApplication extends ProcessorApplication
{
    private $running = false;
    private $consoleManager;
    private $connectionManager;
    private $consoleSupport;
   
    protected function initialize()
    {
        parent::initialize();
        $this->consoleSupport = false;
        $this->connectionManager = new ConnectionManager();
        $this->consoleManager = new ConsoleManager();
        $this->consoleManager->setBlocking(false);
    }
    
    public function getPort()
    {
        return $this->connectionManager->getPort();
    }

    public function setPort($port)
    {
        $this->connectionManager->setPort($port);
    }

    public function getConsoleSupport()
    {
        return $this->consoleSupport;
    }
    
    public function setConsoleSupport($consoleSupport)
    {
        $this->consoleSupport = $consoleSupport;
    }

        /**
     * Obtiene el manager de la consola
     * @return ConsoleManager
     */
    public function getConsoleManager()
    {
        return $this->consoleManager;
    }
    
    /**
     * Obtiene el manager de conexiones
     * @return ConnectionManager
     */
    public function getConnectionManager ()
    {
        return $this->connectionManager;
    }
    
    public function close ()
    {
        $this->running = false;
    }
    
    public function onStarted()
    {
        parent::onStarted();
        if ($this->consoleSupport)
            $this->consoleManager->displayPrompt();
        $this->running = true;
        while ($this->running)
            $this->onIdle();
        $this->stop();
    }
    
    public function onIdle() 
    {
        $this->connectionManager->checkConnections();
        if ($this->consoleSupport && $this->consoleManager->checkCommand())
            $this->consoleManager->displayPrompt();
    } 
}

?>