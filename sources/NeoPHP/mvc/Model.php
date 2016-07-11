<?php

namespace NeoPHP\mvc;

use NeoPHP\core\Object;

abstract class Model extends Object
{
    public function __construct($id=null) 
    {
        $this->setId($id);
    }
    
    public abstract function getId ();
    public abstract function setId ($id);
}