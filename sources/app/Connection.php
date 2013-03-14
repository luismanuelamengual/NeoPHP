<?php

abstract class Connection
{
    private $dsn;
    private $username;
    private $password;
    private $driverOptions;
    private $connection;
    
    public function __construct ($dsn = "", $username = "", $password = "", $driverOptions = array())
    {
        $this->dsn = $dsn;
        $this->username = $username;
        $this->password = $password;
        $this->driverOptions = $driverOptions;
    }
    
    public function getDsn ()
    {
        return $this->dsn;
    }
    
    public function getUsername ()
    {
        return $this->username;
    }
    
    public function getPassword ()
    {
        return $this->password;
    }
    
    public function getDriverOptions ()
    {
        return $this->driverOptions;
    }
    
    public function isConnected ()
    {
        return !empty($this->connection);
    }
    
    private function connect ()
    {
        if (!$this->isConnected())
        {
            try 
            {
                $this->connection = new PDO ($this->dsn, $this->username, $this->password, $this->driverOptions);
                $this->connection->setAttribute (PDO::ATTR_CASE, PDO::CASE_LOWER);
                $this->connection->dbtype = substr ($this->dsn, 0, strpos ($this->dsn, ":"));
            }
            catch (PDOException $e)
            {
                $this->connection = null;
                throw new Exception ("No se pudo establecer conexión con la base de datos: " . $e->getMessage());
            }
        }
    }
    
    public function getDataObject($tableName)
    {
        require_once ('app/DataObject.php');
        return new DataObject($this, $tableName);
    }
    
    public function query ($sql)
    {
        $this->connect();
        $statement = $this->connection->query($sql);
        $result = new stdClass();
        if ($statement)
        {
            $result->success = true;
            $result->resultSet = $statement->fetchAll(PDO::FETCH_OBJ);
        }
        else
        {
            $errorData = $this->connection->errorInfo();
            $result->success = false;
            $result->error = new stdClass();
            $result->error->code = $errorData[0];
            $result->error->driverErrorCode = $errorData[1];
            $result->error->driverErrorMessage = $errorData[2];
        }
        return $result;
    }
    
    public function exec ($sql)
    {
        $this->connect();
        $afectedRows = $this->connection->exec($sql);
        $result = new stdClass();
        if ($afectedRows === FALSE)
        {
            $errorData = $this->connection->errorInfo();
            $result->success = false;
            $result->error = new stdClass();
            $result->error->code = $errorData[0];
            $result->error->driverErrorCode = $errorData[1];
            $result->error->driverErrorMessage = $errorData[2];
        }
        else
        {
            $result->success = true;
            $result->afectedRows = $afectedRows;
        }
        return $result;
    }
}

?>
