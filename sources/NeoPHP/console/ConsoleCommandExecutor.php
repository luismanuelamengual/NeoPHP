<?php

namespace NeoPHP\console;

abstract class ConsoleCommandExecutor
{
    public abstract function onCommandEntered (ConsoleApplication $application, $command, array $parameters = []);
}