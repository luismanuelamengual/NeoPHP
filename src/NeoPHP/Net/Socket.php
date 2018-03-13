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

    public function read($length = 1024) {
        return socket_read($this->resource, $length, PHP_NORMAL_READ);
    }
}