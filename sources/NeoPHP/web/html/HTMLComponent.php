<?php

namespace NeoPHP\web\html;

use NeoPHP\web\html\HTMLPage;
use NeoPHP\web\html\HTMLTag;

abstract class HTMLComponent
{
    public abstract function build (HTMLPage $page, HTMLTag $parent);
}
