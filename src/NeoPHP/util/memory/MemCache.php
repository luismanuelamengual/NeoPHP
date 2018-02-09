<?php

namespace NeoPHP\util\memory;

use Exception;
use Memcached;

final class MemCache 
{
    const DEFAULT_HOST = "localhost";
    const DEFAULT_PORT = 11211;
    
    private $connection;
    private $host;
    private $port;
    
    public function __construct ($host=self::DEFAULT_HOST, $port=self::DEFAULT_PORT)
    {
        $this->host = $host;
        $this->port = $port;
        $this->connection = null;
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
    
    public function __call($name, $arguments) 
    {
        return call_user_func_array([$this->getConnection(),$name], $arguments);
    }
    
    public function getHost() 
    {
        return $this->host;
    }

    public function getPort() 
    {
        return $this->port;
    }

    /**
     * Obtiene la conexion con el server de cache
     * @return Memcached Cache de memoria
     * @throws Exception
     */
    private function getConnection ()
    {
        if (empty($this->connection))
        {
            $this->connection = new Memcached();
            $this->connection->addServer($this->host, $this->port);
        }
        return $this->connection;
    }
    
    public function setValue ($key, $value, $expire=0)
    {
        $this->getConnection()->set($key, serialize($value), $expire);        
    }
    
    public function getValue ($key, callable $callable = null)
    {
        $value = $this->getConnection()->get($key, $callable);
        if ($value != null)
            $value = unserialize($value);
        return $value;
    }
    
    public function deleteValue ($key, $time=0)
    {
        $this->getConnection()->delete($key, $time);
    }
}