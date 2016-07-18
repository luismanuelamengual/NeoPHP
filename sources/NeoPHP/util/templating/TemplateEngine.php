<?php

namespace NeoPHP\util\templating;

use NeoPHP\core\Object;

abstract class TemplateEngine extends Object
{
    public abstract function compile ($content);
}