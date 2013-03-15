<?php

abstract class Connection
{
    private $connection;
    
    public final function __construct ()
    {
        $this->connection = null;
    }
        
    private final function connect ()
    {
        if (empty($this->connection))
        {
            try 
            {
                $dsn = $this->getDsn();
                $username = $this->getUsername();
                $password = $this->getPassword();
                $options = $this->getDriverOptions();
                $this->connection = new PDO ($dsn, $username, $password, $options);
                $this->connection->setAttribute (PDO::ATTR_CASE, PDO::CASE_LOWER);
                $this->connection->dbtype = substr ($dsn, 0, strpos ($dsn, ":"));
            }
            catch (PDOException $e)
            {
                $this->connection = null;
                throw new Exception ("No se pudo establecer conexión con la base de datos: " . $e->getMessage());
            }
        }
    }
    
    public final function getDataObject($tableName)
    {
        require_once ('app/DataObject.php');
        return new DataObject($this, $tableName);
    }
    
    public final function query ($sql)
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
    
    public final function exec ($sql)
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
    
    public abstract function getDsn ();
    public abstract function getUsername ();
    public abstract function getPassword ();
    public abstract function getDriverOptions ();
}

?>
