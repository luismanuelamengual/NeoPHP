<?php

namespace com\neophp;

use NeoPHP\web\WebApplication;

class NeoApplication extends WebApplication
{
    protected function initialize ()
    {
        parent::initialize();
        $this->setName ("Test Application");
        $this->setRestfull (true);
    }
}

?>