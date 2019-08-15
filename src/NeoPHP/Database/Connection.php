<?php

namespace NeoPHP\Database;

use Closure;
use Exception;
use PDO;
use NeoPHP\Database\Builder\QueryBuilder;
use NeoPHP\Query\Query;
use stdClass;

/**
 * Class Connection
 * @package NeoPHP\Database
 */
class Connection {

    private $pdo;
    private $readOnly;
    private $queryBuilder;
    private $listeners;
    private $logEnabled;
    private $debugEnabled;

    /**
     * Connection constructor.
     * @param PDO $pdo
     * @param QueryBuilder $queryBuilder
     * @param array $config
     */
    public function __construct(PDO $pdo, QueryBuilder $queryBuilder, array $config = []) {
        $this->pdo = $pdo;
        $this->readOnly = isset($config["readOnly"]) ? $config["readOnly"] : false;
        $this->logEnabled = isset($config["logEnabled"]) ? $config["logEnabled"] : false;
        $this->debugEnabled = isset($config["debugEnabled"]) ? $config["debugEnabled"] : false;
        $this->queryBuilder = $queryBuilder;
        $this->listeners = [];

        $this->listen(function ($sqlData) {
            if ($this->logEnabled) {
                $logSentence = "SQL: ";
                $logSentence .= $sqlData->sql;
                $logSentence .= " [Time: ";
                $logSentence .= number_format($sqlData->elapsedTime, 4);
                if (isset($sqlData->results)) {
                    $logSentence .= ", Results: ";
                    $logSentence .= sizeof($sqlData->results);
                }
                if (isset($sqlData->affectedRows)) {
                    $logSentence .= ", Affected rows: ";
                    $logSentence .= $sqlData->affectedRows;
                }
                $logSentence .= "]";
                get_logger()->debug($logSentence);
            }

            if ($this->debugEnabled) {
                $separator = (php_sapi_name() == "cli")? "\n" : "<br>";
                $logSentence = "SQL: ";
                $logSentence .= $sqlData->sql;
                $logSentence .= " [Time: ";
                $logSentence .= number_format($sqlData->elapsedTime, 4);
                $logSentence .= "]";
                $logSentence .= $separator;
                echo $logSentence;
            }
        });
    }

    /**
     * @return PDO
     */
    public function pdo(): PDO {
        return $this->pdo;
    }

    /**
     * @return QueryBuilder
     */
    public function queryBuilder(): QueryBuilder {
        return $this->queryBuilder;
    }

    /**
     * @param null $readOnly
     * @return bool|mixed
     */
    public function readOnly($readOnly = null) {
        if (isset($readOnly)) {
            $this->readOnly = $readOnly;
        }
        else {
            return $this->readOnly;
        }
    }

    /**
     * @return bool|mixed
     */
    public function logEnabled($logEnabled = null) {
        if (isset($logEnabled)) {
            $this->logEnabled = $logEnabled;
        }
        else {
            return $this->logEnabled;
        }
    }

    /**
     * @param bool|null $debugEnabled
     * @return bool|mixed
     */
    public function debugEnabled($debugEnabled = null) {
        if (isset($debugEnabled)) {
            $this->debugEnabled = $debugEnabled;
        }
        else {
            return $this->debugEnabled;
        }
    }

    /**
     * @param Closure $closure
     * @throws Exception
     */
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

    /**
     * @return bool
     */
    public final function beginTransaction() {
        return $this->pdo->beginTransaction();
    }

    /**
     * @return bool
     */
    public final function commit() {
        return $this->pdo->commit();
    }

    /**
     * @return bool
     */
    public final function rollback() {
        return $this->pdo->rollBack();
    }

    /**
     * @return bool
     */
    public final function inTransaction() {
        return $this->pdo->inTransaction();
    }

    /**
     * @param null $sequenceName
     * @return string
     */
    public final function getLastInsertedId($sequenceName = null) {
        return $this->pdo->lastInsertId($sequenceName);
    }

    /**
     * @param $string
     * @param int $parameterType
     * @return string
     */
    public final function quote($string, $parameterType = PDO::PARAM_STR) {
        return $this->pdo->quote($string, $parameterType);
    }

    /**
     * @param $table
     * @return ConnectionTable
     */
    public final function table($table) {
        return new ConnectionTable($this, $table);
    }

    /**
     * @param $sql
     * @param array $bindings
     * @return mixed
     */
    private function getSqlSentence($sql, array $bindings = []) {
        $sqlSentence = $sql;
        foreach ($bindings as $key => $value) {
            if (!is_numeric($value)) {
                $value = $this->quote($value);
            }
            if (is_numeric($key)) {
                $sqlSentence = preg_replace('/' . preg_quote("?", '/') . '/', $value, $sqlSentence, 1);
            }
            else {
                $sqlSentence = str_replace(":$key", $value, $sqlSentence);
            }
        }
        return $sqlSentence;
    }

    /**
     * Registra una entidad que va a escuchar las sentencias
     * sql que se ejecutan sobre esta conexión
     * @param callable $listener Funcion de callback
     */
    public function listen(callable $listener) {
        $this->listeners[] = $listener;
    }

    /**
     * Envia un evento de sql con la informacion de la sentencia
     * @param stdClass $sqlData de la sentencia sql
     */
    private function fire(stdClass $sqlData) {
        foreach ($this->listeners as $listener) {
            $listener($sqlData);
        }
    }

    /**
     * @param $sql
     * @param array $bindings
     * @return array
     * @throws Exception
     */
    public final function query($sql, array $bindings = []) {
        if ($sql instanceof Query) {
            $sql = $this->queryBuilder->buildSql($sql, $bindings);
        }
        $startTimestamp = microtime(true);
        $queryStatement = null;
        if (empty($bindings)) {
            $queryStatement = $this->pdo->query($sql);
            if (!$queryStatement) {
                throw new Exception ("Unable to execute sql statement \"" . $this->getSqlSentence($sql, $bindings) . "\" " . $this->pdo->errorInfo()[2]);
            }
        }
        else {
            $queryStatement = $this->pdo->prepare($sql);
            if ($queryStatement == false) {
                throw new Exception ("Unable to prepare sql statement \"" . $this->getSqlSentence($sql, $bindings) . "\"");
            }
            $sqlExecuted = $queryStatement->execute($bindings);
            if (!$sqlExecuted) {
                throw new Exception ("Unable to execute prepared statement \"" . $this->getSqlSentence($sql, $bindings) . "\" " . $queryStatement->errorInfo()[2]);
            }
        }
        $results = $queryStatement->fetchAll(PDO::FETCH_OBJ);

        $sqlData = new stdClass();
        $sqlData->results = $results;
        $sqlData->elapsedTime = microtime(true) - $startTimestamp;
        $sqlData->sql = $this->getSqlSentence($sql, $bindings);
        $sqlData->rawSql = $sql;
        $sqlData->bindings = $bindings;
        $this->fire($sqlData);
        return $results;
    }

    /**
     * @param $sql
     * @param array $bindings
     * @return bool|int
     * @throws Exception
     */
    public final function exec($sql, array $bindings = []) {
        if ($sql instanceof Query) {
            $sql = $this->queryBuilder->buildSql($sql, $bindings);
        }
        $startTimestamp = microtime(true);
        $affectedRows = false;
        if (!$this->readOnly) {
            if (empty($bindings)) {
                $affectedRows = $this->pdo->exec($sql);
                if (!$affectedRows) {
                    throw new Exception ("Unable to execute sql \"" . $this->getSqlSentence($sql, $bindings) . "\" " . $this->pdo->errorInfo()[2]);
                }
            }
            else {
                $preparedStatement = $this->pdo->prepare($sql);
                if ($preparedStatement == false) {
                    throw new Exception ("Unable to prepare sql statement \"" . $this->getSqlSentence($sql, $bindings) . "\"");
                }
                $sqlExecuted = $preparedStatement->execute($bindings);
                if (!$sqlExecuted) {
                    throw new Exception ("Unable to execute prepared statement \"" . $this->getSqlSentence($sql, $bindings) . "\" " . $preparedStatement->errorInfo()[2]);
                }
                $affectedRows = $preparedStatement->rowCount();
            }
        }

        $sqlData = new stdClass();
        $sqlData->affectedRows = $affectedRows;
        $sqlData->elapsedTime = microtime(true) - $startTimestamp;
        $sqlData->sql = $this->getSqlSentence($sql, $bindings);
        $sqlData->rawSql = $sql;
        $sqlData->bindings = $bindings;
        $this->fire($sqlData);
        return $affectedRows;
    }
}