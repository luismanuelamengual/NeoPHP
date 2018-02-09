<?php

namespace NeoPHP\io;

class FilterOutputStream extends OutputStream
{
    protected $out;
    
    public function __construct(OutputStream $out)
    {
        $this->out = $out;
    }
    
    public function write($buffer)
    {
        $this->out->write($buffer);
    }
    
    public function flush()
    {
        $this->out->flush();
    }
    
    public function close()
    {
        $this->out->flush();
        $this->out->close();
    }
}