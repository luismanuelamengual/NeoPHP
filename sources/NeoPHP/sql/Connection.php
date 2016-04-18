<?php

namespace NeoPHP\sql;

use Exception;
use NeoPHP\core\Object;
use NeoPHP\core\reflect\ReflectionAnnotatedClass;
use NeoPHP\util\logging\Logger;
use PDO;
use PDOStatement;

class Connection extends Object
{
    protected static $instances = array();
    private $connection;
    private $logger;
    private $loggingEnabled;
    private $ignoreUpdates;
    private $driver;
    private $driverOptions;
    private $host;
    private $port;
    private $database;
    private $username;
    private $password;
    
    public function __construct ($driver = "", $database="", $host="localhost", $port=null, $username=null, $password=null)
    {
        $this->connection = null;
        $this->logger = null;
        $this->loggingEnabled = false;
        $this->ignoreUpdates = false;
        $this->driver = $driver;
        $this->driverOptions = [];
        $this->host = $host;
        $this->port = $port;
        $this->database = $database;
        $this->username = $username;
        $this->password = $password;
    }
    
    /**
     * Obtiene una instancia unica de la base de datos
     * @return Connection
     */
    public static function getInstance ()
    {
        $calledClass = get_called_class();
        if (!isset(self::$instances[$calledClass]))
            self::$instances[$calledClass] = new $calledClass();
        return self::$instances[$calledClass];
    }
      
    /**
     * Obtiene una conexión con la base de datos
     * @return PDO objeto pdo de base de datos
     */
    private final function getConnection ()
    {
        if (empty($this->connection))
        {
            $this->connection = new PDO ($this->getDsn(), $this->getUsername(), $this->getPassword(), $this->getDriverOptions());
            $this->connection->setAttribute (PDO::ATTR_CASE, PDO::CASE_LOWER);
            $this->connection->dbtype = $this->getDriver();
        }
        return $this->connection;
    }
    
    protected function getDsn ()
    {
        $dsn = "{$this->getDriver()}:host={$this->getHost()};dbname={$this->getDatabase()}";
        $port = $this->getPort();
        if (!empty($port))
            $dsn .= ";port=$port";
        return $dsn;
    }
    
    public function getDriver() 
    {
        return $this->driver;
    }

    public function getDriverOptions() 
    {
        return $this->driverOptions;
    }

    public function getHost() 
    {
        return $this->host;
    }

    public function getPort() 
    {
        return $this->port;
    }

    public function getDatabase() 
    {
        return $this->database;
    }

    public function getUsername() 
    {
        return $this->username;
    }

    public function getPassword() 
    {
        return $this->password;
    }

    public function setDriver($driver) 
    {
        $this->driver = $driver;
    }

    public function setDriverOptions($driverOptions) 
    {
        $this->driverOptions = $driverOptions;
    }

    public function setHost($host) 
    {
        $this->host = $host;
    }

    public function setPort($port) 
    {
        $this->port = $port;
    }

    public function setDatabase($database) 
    {
        $this->database = $database;
    }

    public function setUsername($username) 
    {
        $this->username = $username;
    }

    public function setPassword($password) 
    {
        $this->password = $password;
    }

    /**
     * Retorna el Logger asociado a la base de datos
     * @return Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }
    
    public function setLoggingEnabled ($logginEnabled)
    {
        $this->loggingEnabled = $logginEnabled;
    }
    
    public function isLoggingEnabled ()
    {
        return $this->loggingEnabled;
    }
    
    public function setIgnoreUpdates ($ignoreUpdates)
    {
        $this->ignoreUpdates = $ignoreUpdates;
    }
    
    public function isIgnoreUpdates ()
    {
        return $this->ignoreUpdates;
    }
    
    public final function beginTransaction ()
    {
        return $this->getConnection()->beginTransaction();
    }
    
    public final function commitTransaction ()
    {
        return $this->getConnection()->commit();
    }
    
    public final function rollbackTransaction ()
    {
        return $this->getConnection()->rollBack();
    }
    
    public final function getLastInsertedId ($sequenceName=null)
    {
        return $this->getConnection()->lastInsertId($sequenceName);
    }
    
    /**
     * Obtiene un Statement con los resultados de la búsqueda
     * @param type $sql
     * @param array $bindings
     * @return PDOStatement
     * @throws Exception
     */
    public final function query ($sql, array $bindings = array())
    {   
        $sqlSentence = $sql . (!empty($bindings)? "   [" . implode(",", $bindings) . "]" : "");
        if ($this->loggingEnabled && $this->logger != null)
            $this->logger->info("SQL: " . $sqlSentence);
        
        $queryStatement = false;
        $sqlExecuted = false;
        if (empty($bindings))
        {
            $queryStatement = $this->getConnection()->query($sql);
            $sqlExecuted = $queryStatement;
        }
        else
        {
            $queryStatement = $this->getConnection()->prepare($sql);
            if ($queryStatement != false)
                $sqlExecuted = $queryStatement->execute ($bindings);
        }
        
        if ($sqlExecuted == false)
        {
            $errorData = $this->getConnection()->errorInfo();            
            throw new Exception ("Unable to execute sql \"" . $sqlSentence . "\" " . $errorData[2]);
        }
        return $queryStatement;
    }
    
    public final function exec ($sql, array $bindings = [])
    {
        $sqlSentence = $sql . (!empty($bindings)? "   [" . implode(",", $bindings) . "]" : "");
        if ($this->loggingEnabled && $this->logger != null)
            $this->logger->info("SQL: " . $sqlSentence);
        
        $affectedRows = false;
        $sqlExecuted = false;
        if (!$this->ignoreUpdates)
        {    
            if (empty($bindings))
            {
                $affectedRows = $this->getConnection()->exec($sql);
                $sqlExecuted = $affectedRows;
            }
            else
            {
                $preparedStatement = $this->getConnection()->prepare($sql);
                if ($preparedStatement != false)
                {
                    $sqlExecuted = $preparedStatement->execute($bindings);
                    $affectedRows = $preparedStatement->rowCount();
                }
            }
            
            if ($sqlExecuted == false)
            {
                $errorData = $this->getConnection()->errorInfo();
                throw new Exception ("Unable to execute sql \"" . $sqlSentence . "\" " . $errorData[2]);
            }
        }
        return $affectedRows;
    }
    
    public final function quote ($parameter, $parameterType = PDO::PARAM_STR)
    {
        return $this->getConnection()->quote($string, $parameterType);
    }
    
    /**
     * Obtiene la tabla con la que va a trabajar para hacer consultas
     * @param $tableName Nombre de la tabla
     * @return SQLDataTable Objeto de SQL para hacer la consulta
     */
    public function getTable ($tableName)
    {
        return new SQLDataTable($this, $tableName);
    }
    
    /**
     * Obtiene el manejador de entidades asociado a la conexión
     * @return EntityManager Manejador de entidades de la conexión
     */
    public function getEntityManager ()
    {
        if (!isset($this->entityManager))
            $this->entityManager = new EntityManager ($this);
        return $this->entityManager;
    }
    
    public function insertEntity (Object $entity)
    {
        return $this->getEntityManager()->insert($entity);
    }
    
    public function updateEntity (Object $entity)
    {
        return $this->getEntityManager()->update($entity);
    }
    
    public function deleteEntity (Object $entity)
    {
        return $this->getEntityManager()->delete($entity);
    }
    
    public function persistEntity (Object $entity)
    {
        return $this->getEntityManager()->persist($entity);
    }
    
    public function completeEntity (Object $entity)
    {
        return $this->getEntityManager()->complete($entity);
    }
    
    public function getEntity (ReflectionAnnotatedClass $entityClass, $id)
    {
        return $this->getEntityManager()->find($entityClass, $id);
    }
    
    public function getEntitiesBy (ReflectionAnnotatedClass $entityClass, EntityFilterGroup $filters)
    {
        return $this->getEntityManager()->findBy($entityClass, $filters);
    }
    
    public function getEntities (ReflectionAnnotatedClass $entityClass)
    {
        return $this->getEntityManager()->findAll($entityClass);
    }
}

?>