<?php

namespace NeoPHP\web\http;

class RedirectResponse extends Response
{
    public function __construct ($url)
    {
        $this->addHeader("Location", $url);
    }
}