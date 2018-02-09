<?php

namespace NeoPHP\io;

class FilterInputStream extends InputStream
{
    protected $in;
    
    public function __construct(InputStream $in)
    {
        $this->in = $in;
    }
    
    public function availiable()
    {
        return $this->in->availiable();
    }

    public function close()
    {
        return $this->in->close();
    }

    public function read($length = 1)
    {
        return $this->in->read($length);
    }

    public function reset()
    {
        return $this->in->reset();
    }

    public function skip($length)
    {
        return $this->in->skip($length);
    }
}