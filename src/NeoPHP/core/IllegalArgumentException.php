<?php

namespace NeoPHP\core;

use Exception;

class IllegalArgumentException extends Exception
{
    function __construct($message, $code=0, $previous=null) 
    {
        parent::__construct($message, $code, $previous);
    }
}

?>