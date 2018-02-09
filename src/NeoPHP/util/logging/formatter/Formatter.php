<?php

namespace NeoPHP\util\logging\formatter;

use NeoPHP\util\logging\LogRecord;

interface Formatter
{
    public function format (LogRecord $record);
}

?>