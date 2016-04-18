<?php

namespace NeoPHP\util\eventhandling;

use Exception;

trait EventDispatcherTrait
{
    private $listeners = [];
    
    public function getListeners ($eventName)
    {
        return $this->listeners[$eventName];
    }

    public function hasListeners ($eventName)
    {
        return isset($this->listeners[$eventName]);
    }
    
    public function addListener ($eventName, $listener)
    {
        
        $this->listeners[$eventName][] = $listener;
    }

    public function removeListener ($eventName, $listener)
    {
        if (!isset($this->listeners[$eventName])) 
            return;
        $key = array_search($listener, $this->listeners[$eventName], true);
        if ($key !== false)
            unset($this->listeners[$eventName][$key]);
    }
    
    public function fireEvent ($eventName, array $eventParameters=[])
    {
        if (isset($this->listeners[$eventName]))
        {
            foreach ($this->listeners[$eventName] as $listener)
            {
                try
                {
                    $response = call_user_func_array($listener, $eventParameters);
                    if ($response === false)
                        break;
                }
                catch (Exception $ex) {}
            }
        }
    }
}