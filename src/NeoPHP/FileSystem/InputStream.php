<?php

namespace NeoPHP\FileSystem;

abstract class InputStream {

    public abstract function read($length = 0);

    public abstract function skip($length);

    public abstract function availiable();

    public abstract function reset();

    public abstract function close();
}