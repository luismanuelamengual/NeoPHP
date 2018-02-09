<?php

namespace NeoPHP\net;

abstract class AbstractSocket
{
    protected $resource;
    protected $streamContextOptions;
    
    public function __construct ($resource = null) 
    {
        $this->resource = $resource;
    }
    
    public function setResource ($socket)
    {
        $this->resource = $socket;
    }
    
    public function getResource ()
    {
        return $this->resource;
    }
    
    public function close ()
    {
        if ($this->resource) 
        {
            socket_close($this->resource);
            $this->resource = null;
        }
    }
}

?>