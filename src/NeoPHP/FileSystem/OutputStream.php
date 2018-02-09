<?php

namespace NeoPHP\FileSystem;

abstract class OutputStream {

    public abstract function write($buffer);

    public abstract function flush();

    public abstract function close();
}