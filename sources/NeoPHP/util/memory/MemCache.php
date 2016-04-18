<?php

namespace NeoPHP\util\memory;

use Exception;
use Memcached;

final class MemCache 
{
    private $connection;
    private $host;
    private $port;
    
    public function __construct ()
    {
        $this->connection = null;
        $this->host = "localhost";
        $this->port = 11211;
    }
    
    public function __set ($key, $value)
    {
        $this->setValue($key, $value);
    }

    public function __get ($key)
    {
        return $this->getValue($key);
    }
    
    public function __isset ($key)
    {
        return $this->getValue($key) != false;
    }
   
    public function __unset ($key)
    {
        $this->deleteValue($key);
    }
    
    public function setHost ($host)
    {
        $this->host = $host;
    }
    
    public function setPort ($port)       
    {
        $this->port = $port;
    }
    
    private function getConnection ()
    {
        if (empty($this->connection))
        {
            $this->connection = new Memcached();
            $this->connection->addServer($this->host, $this->port);
            if (!$this->connection)
            {
                $this->connection = null;
                throw new Exception ("Connection to Memcached server could not be established !! [" . $this->host . ":" . $this->port . "]");
            }
        }
        return $this->connection;
    }
    
    public function setValue ($key, $value, $expire=0)
    {
        $this->getConnection()->set($key, serialize($value), $expire);        
    }
    
    public function getValue ($key)
    {
        $value = $this->getConnection()->get($key);
        if ($value != null)
            $value = unserialize($value);
        return $value;
    }
    
    public function deleteValue ($key)
    {
        $this->getConnection()->delete($key);
    }
}

?>
