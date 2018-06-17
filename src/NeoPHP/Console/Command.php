<?php

namespace NeoPHP\Console;

abstract class Command {

    public abstract function handle (array $arguments = []);
}