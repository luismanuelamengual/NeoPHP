<?php

namespace NeoPHP\io;

class DataInputStream extends FilterInputStream
{
    public function readLine()
    {
        $line = "";
        while ($this->getInputStream()->availiable())
        {
            $read = $this->getInputStream()->read();
            if ($read == "\n")
                break;
            $line .= $read;
        }
        return $line;
    }
}