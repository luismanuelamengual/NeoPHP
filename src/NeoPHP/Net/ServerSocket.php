<?php

namespace NeoPHP\Net;

use RuntimeException;

class ServerSocket extends AbstractSocket {

    public function __construct($port) {
        parent::__construct();
        $resource = socket_create_listen($port);
        if (is_resource($resource)) {
            $this->setResource($resource);
        }
        else {
            throw new RuntimeException("Server socket on port \"$port\" could not be created !!. " . socket_strerror(socket_last_error()));
        }
    }

    public function accept() {
        $resource = socket_accept($this->resource);
        return new Socket($resource);
    }
}