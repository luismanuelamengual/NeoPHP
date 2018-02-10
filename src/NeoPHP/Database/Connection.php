<?php

namespace NeoPHP\Database;

use Closure;
use Exception;
use NeoPHP\core\Object;
use NeoPHP\util\logging\Logger;
use PDO;
use PDOStatement;

class Connection extends Object {

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

    public function __construct($driver = "", $database = "", $host = "localhost", $port = null, $username = null, $password = null) {
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
     * Obtiene una conexión con la base de datos
     * @return PDO objeto pdo de base de datos
     */
    private final function getConnection() {
        if (empty($this->connection)) {
            $this->connection = new PDO ($this->getDsn(), $this->getUsername(), $this->getPassword(), $this->getDriverOptions());
            $this->connection->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
            $this->connection->dbtype = $this->getDriver();
        }
        return $this->connection;
    }

    protected function getDsn() {
        $dsn = "{$this->getDriver()}:host={$this->getHost()};dbname={$this->getDatabase()}";
        $port = $this->getPort();
        if (!empty($port))
            $dsn .= ";port=$port";
        return $dsn;
    }

    public function getDriver() {
        return $this->driver;
    }

    public function getDriverOptions() {
        return $this->driverOptions;
    }

    public function getHost() {
        return $this->host;
    }

    public function getPort() {
        return $this->port;
    }

    public function getDatabase() {
        return $this->database;
    }

    public function getUsername() {
        return $this->username;
    }

    public function getPassword() {
        return $this->password;
    }

    public function setDriver($driver) {
        $this->driver = $driver;
    }

    public function setDriverOptions($driverOptions) {
        $this->driverOptions = $driverOptions;
    }

    public function setHost($host) {
        $this->host = $host;
    }

    public function setPort($port) {
        $this->port = $port;
    }

    public function setDatabase($database) {
        $this->database = $database;
    }

    public function setUsername($username) {
        $this->username = $username;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    /**
     * Retorna el Logger asociado a la base de datos
     * @return Logger
     */
    public function getLogger() {
        return $this->logger;
    }

    public function setLogger(Logger $logger) {
        $this->logger = $logger;
    }

    public function setLoggingEnabled($logginEnabled) {
        $this->loggingEnabled = $logginEnabled;
    }

    public function isLoggingEnabled() {
        return $this->loggingEnabled;
    }

    public function setIgnoreUpdates($ignoreUpdates) {
        $this->ignoreUpdates = $ignoreUpdates;
    }

    public function isIgnoreUpdates() {
        return $this->ignoreUpdates;
    }

    public final function transaction(Closure $closure) {
        $this->beginTransaction();
        try {
            $databaseClousure = $closure->bindTo($this);
            $databaseClousure($this);
            $this->commit();
        }
        catch (Exception $exception) {
            $this->rollback();
            throw $exception;
        }
    }

    public final function beginTransaction() {
        return $this->getConnection()->beginTransaction();
    }

    public final function commit() {
        return $this->getConnection()->commit();
    }

    public final function rollback() {
        return $this->getConnection()->rollBack();
    }

    public final function inTransaction() {
        return $this->getConnection()->inTransaction();
    }

    public final function getLastInsertedId($sequenceName = null) {
        return $this->getConnection()->lastInsertId($sequenceName);
    }

    /**
     * Obtiene un Statement con los resultados de la búsqueda
     * @param type $sql
     * @param array $bindings
     * @return PDOStatement
     * @throws Exception
     */
    public final function query($sql, array $bindings = array()) {
        $sqlSentence = $sql . (!empty($bindings) ? " [" . implode(",", $bindings) . "]" : "");
        if ($this->loggingEnabled && $this->logger != null)
            $this->logger->info("SQL: " . $sqlSentence);

        $queryStatement = false;
        if (empty($bindings)) {
            $queryStatement = $this->getConnection()->query($sql);
            if (!$queryStatement)
                throw new Exception ("Unable to execute sql \"" . $sqlSentence . "\" " . $this->getConnection()->errorInfo()[2]);
        }
        else {
            $queryStatement = $this->getConnection()->prepare($sql);
            if ($queryStatement == false)
                throw new Exception ("Unable to prepare sql statement \"" . $sqlSentence . "\"");

            $sqlExecuted = $queryStatement->execute($bindings);
            if (!$sqlExecuted)
                throw new Exception ("Unable to execute prepared statement \"" . $sqlSentence . "\" " . $queryStatement->errorInfo()[2]);
        }
        return $queryStatement;
    }

    public final function exec($sql, array $bindings = []) {
        $sqlSentence = $sql . (!empty($bindings) ? " [" . implode(",", $bindings) . "]" : "");
        if ($this->loggingEnabled && $this->logger != null)
            $this->logger->info("SQL: " . $sqlSentence);

        $affectedRows = false;
        if (!$this->ignoreUpdates) {
            if (empty($bindings)) {
                $affectedRows = $this->getConnection()->exec($sql);
                if (!$affectedRows)
                    throw new Exception ("Unable to execute sql \"" . $sqlSentence . "\" " . $this->getConnection()->errorInfo()[2]);
            }
            else {
                $preparedStatement = $this->getConnection()->prepare($sql);
                if ($preparedStatement == false)
                    throw new Exception ("Unable to prepare sql statement \"" . $sqlSentence . "\"");

                $sqlExecuted = $preparedStatement->execute($bindings);
                if (!$sqlExecuted)
                    throw new Exception ("Unable to execute prepared statement \"" . $sqlSentence . "\" " . $preparedStatement->errorInfo()[2]);
                $affectedRows = $preparedStatement->rowCount();
            }
        }
        return $affectedRows;
    }

    public final function quote($parameter, $parameterType = PDO::PARAM_STR) {
        return $this->getConnection()->quote($string, $parameterType);
    }

    /**
     * Obtiene la tabla con la que va a trabajar para hacer consultas
     * @param $tableName Nombre de la tabla
     * @return ConnectionQuery Objeto de SQL para hacer la consulta
     */
    public function createQuery($tableName = null) {
        return new ConnectionQuery($this, $tableName);
    }
}