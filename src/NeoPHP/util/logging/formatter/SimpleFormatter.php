<?php

namespace NeoPHP\util\logging\formatter;

use NeoPHP\util\logging\LogRecord;

class SimpleFormatter implements Formatter
{
    public function format(LogRecord $record)
    {
        $formattedMessage = "";
        $formattedMessage .= "[" . date("Y-m-d H:i:s", $record->getTimestamp()) . "] ";
        $formattedMessage .= $record->getLevel()->getName();
        $formattedMessage .= ": ";
        $formattedMessage .= $record->getMessage();
        if ($record->getException() != null)
        {
            $formattedMessage .= " Exception: ";
            $formattedMessage .= $record->getException();
        }
        return $formattedMessage;
    }
}

?>