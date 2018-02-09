<?php

namespace NeoPHP\Http;

class RedirectResponse extends Response
{
    public function __construct ($url)
    {
        $this->addHeader("Location", $url);
    }
}