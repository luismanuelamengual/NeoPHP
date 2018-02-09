<?php

namespace NeoPHP\net;

class Socket extends AbstractSocket
{   
    public function connect ($host, $port)
    {    
        $this->resource = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        return socket_connect($this->resource, $host, $port);
    }
    
    public function send ($data)
    {
        return socket_write ($this->resource, $data);
    }

    public function read ($length=null)
    {
        return socket_read ($this->resource, 1024, PHP_NORMAL_READ);
    }
}

?>