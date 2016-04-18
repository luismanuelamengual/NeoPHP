<?php

namespace NeoPHP\mvc;

use NeoPHP\core\Object;

abstract class TemplateEngine extends Object
{
    public abstract function compile ($content);
}