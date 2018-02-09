<?php

namespace NeoPHP\io;

final class RandomAccessFile extends Object
{
    private $resource;
    
    public function __construct ($filename, $mode="r+")
    {
        $filename = ($file instanceof File)? $file->getFilename() : $file;
        $this->resource = fopen($filename, $mode);
        if (!$this->resource)
            throw new IOException("Error opening from file \"$filename\"");
    }
    
    public function write ($string, $length=null)
    {
        return fwrite($this->resource, $string, $length);
    }
    
    public function read ($length)
    {
        return fread($this->resource, $length);
    }
    
    public function seek ($position=0)
    {
        return fseek($this->resource, $position);
    }
    
    public function skipBytes ($count=0)
    {
        return fseek($this->resource, $count, SEEK_CUR); 
    }
    
    public function close ()
    {
        return fclose($this->resource);
    }
}

?>