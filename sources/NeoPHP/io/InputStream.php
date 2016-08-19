<?php

namespace NeoPHP\io;

abstract class InputStream 
{
    public abstract function read ($length=1);
    public abstract function skip ($length);
    public abstract function availiable(); 
    public abstract function reset();
    public abstract function close();
}