<?php

namespace NeoPHP\io;

use Exception;

class FileInputStream extends InputStream
{
    private $resource;
    
    public function __construct($file)
    {
        $filename = ($file instanceof File)? $file->getFilename() : $file;
        $this->resource = fopen($filename, "r");
        if (!$this->resource)
            throw new IOException("Error opening inputStrem from file \"$filename\"");
    }
    
    public function read($length=1)
    {
        $buffer = fread($this->resource, $length);
        if ($buffer === false)
            throw new IOException("Error reading file inputStream");
        return $buffer;
    }

    public function availiable()
    {
        return !feof($this->resource);
    }
    
    public function skip($length)
    {
        fseek($this->resource, $length, SEEK_CUR); 
    }

    public function reset()
    {
        fseek($this->resource, 0);
    }
    
    public function close()
    {
        try { fclose($this->resource); } catch (Exception $ex) {}
        $this->resource = null;
    }
}

?>