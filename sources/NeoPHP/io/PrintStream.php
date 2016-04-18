<?php

namespace NeoPHP\io;

class PrintStream extends FilterOutputStream
{
    public function __construct($outputStream)
    {
        if (!($outputStream instanceof OutputStream))
        {
            if (($outputStream instanceof File) || is_file($outputStream))
            {
                $outputStream = new FileOutputStream($outputStream);
            }
            else
            {
                throw new Exception("OutputStream needed for a PrintStream");
            }
        }
        parent::__construct($outputStream);
    }
    
    public function printb ($buffer)
    {
        $this->out->write($buffer);
    }
    
    public function println ($buffer="")
    {
        $this->out->write($buffer . PHP_EOL);
    }
    
    public function printf ()
    {
        $arguments = func_get_args();
        $format = array_splice($arguments, 0, 1);
        $this->out->write(vsprintf($format, $arguments));
    }
}

?>