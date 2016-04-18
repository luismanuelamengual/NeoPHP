<?php

namespace NeoPHP\app;

class ProcessorApplication extends Application
{
    use \NeoPHP\util\eventhandling\EventDispatcherTrait;
    
    private $processors;
    
    protected function configure ()
    {
        parent::configure();
        set_time_limit(0);
    }
    
    protected function initialize()
    {
        parent::initialize();
        $this->processors = array();
    }
    
    public function addProcessor (Processor $processor)
    {   
        $this->processors[] = $processor;
    }
    
    public function start ()
    {
        $this->onStarted();
    }
    
    public function stop ()
    {
        $this->onStopped(); 
        exit(0);
    }
    
    protected function onStarted ()
    {
        if (!empty($this->processors))
        {
            foreach ($this->processors as $processor)
                $processor->start();
        }
    }
    
    protected function onStopped()
    {
        if (!empty($this->processors))
        {
            foreach ($this->processors as $processor)
                $processor->stop();
        }
    }
}

?>