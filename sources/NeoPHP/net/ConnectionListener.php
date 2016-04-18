<?php

namespace NeoPHP\net;

interface ConnectionListener
{
    public function onConnectionAdded (Connection $connection);
    public function onConnectionRemoved (Connection $connection);
    public function onConnectionDataReceived (Connection $connection, $dataReceived);
    public function onConnectionDataSent (Connection $connection, $dataSent);
}

?>
