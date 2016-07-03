<?php

namespace NeoPHP\mvc\templateengine;

use NeoPHP\core\Object;

abstract class TemplateEngine extends Object
{
    public abstract function compile ($content);
}