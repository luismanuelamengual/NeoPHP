<?php

namespace NeoPHP\Cache;

final class SharedMemory {

    private $key;
    private $identifier;

    public function __construct($key) {
        $this->key = $key;
        $this->identifier = null;
    }

    public function isOpen() {
        return $this->identifier != null;
    }

    public function open($mode = "c", $flags = 0644, $size = 1000) {
        $identifier = shmop_open($this->key, $mode, $flags, $size);
        if (!$identifier)
            throw new Exception ("Shared memory segment could not be opened");
        $this->identifier = $identifier;
    }

    public function close() {
        shmop_close($this->identifier);
        $this->identifier = null;
    }

    public function getSize() {
        return shmop_size($this->identifier);
    }

    public function write($data, $offset = 0) {
        $bytesWritten = shmop_write($this->identifier, $data, $offset);
        if ($bytesWritten != strlen($data))
            throw new Exception ("Data could not be saved in shared memory");
    }

    public function read($start = 0, $count = 0) {
        if ($count == 0)
            $count = $this->getSize() - $start;
        $data = shmop_read($this->identifier, $start, $count);
        if (!$data)
            throw new Exception ("Data could not be read from shared memory");
        return $data;
    }

    public function delete() {
        if (!shmop_delete($this->identifier))
            throw new Exception ("Shared memory could not be marked for deletion");
    }
}