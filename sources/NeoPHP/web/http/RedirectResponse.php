<?php

namespace NeoPHP\web\http;

use NeoPHP\web\WebApplication;

class RedirectResponse extends Response
{
    public function __construct ($action, $params = array())
    {
        $this->addHeader("Location", WebApplication::getInstance()->getUrl($action, $params));
    }
}