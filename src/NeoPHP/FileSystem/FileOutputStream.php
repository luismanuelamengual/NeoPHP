<?php

namespace NeoPHP\FileSystem;

use Exception;

class FileOutputStream extends OutputStream {

    protected $resource;

    public function __construct($file) {
        $filename = ($file instanceof File) ? $file->getFilename() : $file;
        $this->resource = fopen($filename, "w");
        if (!$this->resource)
            throw new IOException("Error opening outputStrem from file \"$filename\"");
    }

    public function write($buffer) {
        $bytesWritten = fwrite($this->resource, $buffer);
        if ($bytesWritten === false)
            throw new IOException("Error writing file outputStream");
        return $bytesWritten;
    }

    public function flush() {
        fflush($this->resource);
    }

    public function close() {
        try {
            fclose($this->resource);
        }
        catch (Exception $ex) {
        }
        $this->resource = null;
    }
}