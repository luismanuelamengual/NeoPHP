<?php

namespace NeoPHP\Console;

/**
 * Class Command
 * @package NeoPHP\Console
 */
abstract class Command {

    /**
     * Ejetua el comando
     * @param array $arguments
     * @return mixed
     */
    public abstract function handle (array $arguments = []);
}