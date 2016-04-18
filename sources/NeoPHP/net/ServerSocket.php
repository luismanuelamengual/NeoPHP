<?php

namespace NeoPHP\net;

class ServerSocket extends AbstractSocket
{
    public function __construct ($port)
    {
        parent::__construct(socket_create_listen($port));
    }
    
    public function accept ()
    {
        $resource = socket_accept($this->resource);
        return new Socket($resource);
    }
}

?>