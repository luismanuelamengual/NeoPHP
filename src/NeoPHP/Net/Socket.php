<?php

namespace NeoPHP\Net;

class Socket extends AbstractSocket {

    public function connect($host, $port) {
        $this->resource = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        return socket_connect($this->resource, $host, $port);
    }

    public function send($data) {
        return socket_write($this->resource, $data);
    }

    public function read($length = null) {
        if ($length === null) {
            $length = 1024;
        }
        return stream_get_contents($this->resource, $length);
    }
}