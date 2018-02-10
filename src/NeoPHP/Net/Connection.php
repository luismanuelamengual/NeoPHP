<?php

namespace NeoPHP\Net;

use Exception;

class Connection {

    protected $id;
    protected $identifier;
    protected $name;
    protected $manager;
    protected $socket;
    protected $lastActivityTimestamp;

    public function __construct(ConnectionManager $manager, Socket $socket) {
        static $idCounter = 1;
        $this->id = $idCounter++;
        $this->identifier = null;
        $this->manager = $manager;
        $this->socket = $socket;
        $this->name = $this->socket->getName();
        $this->lastActivityTimestamp = microtime(true);
    }

    public function getId() {
        return $this->id;
    }

    public function getIdentifier() {
        return $this->identifier;
    }

    public function setIdentifier($identifier) {
        if (!empty($identifier) && $identifier !== $this->identifier) {
            $this->manager->closeConnectionByIdentifier($identifier);
            $this->identifier = $identifier;
        }
    }

    public function getManager() {
        return $this->manager;
    }

    public function getSocket() {
        return $this->socket;
    }

    public function getIp() {
        return $this->socket->getIp();
    }

    public function getPort() {
        return $this->socket->getPort();
    }

    public function getLastActivityTimestamp() {
        return $this->lastActivityTimestamp;
    }

    public function process() {
        try {
            $data = $this->socket->receive();
            if ($data == false || strlen($data) == 0)
                throw new Exception ("Socket connection closed");
            $this->lastActivityTimestamp = microtime(true);
            $this->manager->onConnectionDataReceived($this, $data);
        }
        catch (Exception $ex) {
            $this->close();
            throw $ex;
        }
    }

    public function send($data) {
        try {
            $written = $this->socket->send($data);
            if ($written == false)
                throw new Exception ("Socket connection closed");
            $this->lastActivityTimestamp = microtime(true);
            $this->manager->onConnectionDataSent($this, $data);
        }
        catch (Exception $ex) {
            $this->close();
            throw $ex;
        }
    }

    public function close() {
        $this->socket->close();
        $this->manager->removeConnection($this);
    }

    public function __toString() {
        return "[" . str_pad($this->id, 4, "0", STR_PAD_LEFT) . "] " . (!empty($this->identifier) ? str_pad($this->identifier, 5, "0", STR_PAD_LEFT) : "?????") . "@" . $this->name;
    }
}