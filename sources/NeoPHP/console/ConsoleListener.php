<?php

namespace NeoPHP\console;

interface ConsoleListener
{
    public function onCommand ($command, $parameters);
}

?>
