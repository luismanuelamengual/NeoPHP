<?php

namespace NeoPHP\Database;

use Closure;
use Exception;
use NeoPHP\Database\Builder\QueryBuilder;
use NeoPHP\Database\Query\Query;
use PDO;

/**
 * Class Connection
 * @package NeoPHP\Database
 */
class Connection {

    private $pdo;
    private $config;
    private $readOnly;
    private $logQueries;
    private $queryBuilder;

    /**
     * Connection constructor.
     * @param PDO $pdo
     */
    public function __construct(PDO $pdo, QueryBuilder $queryBuilder, array $config = []) {
        $this->pdo = $pdo;
        $this->config = $config;
        $this->readOnly = isset($config["readOnly"]) ? $config["readOnly"] : false;
        $this->logQueries = isset($config["logQueries"]) ? $config["logQueries"] : false;
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * @return PDO
     */
    public function getPdo(): PDO {
        return $this->pdo;
    }

    /**
     * @return array
     */
    public function getConfig(): array {
        return $this->config;
    }

    /**
     * @return QueryBuilder
     */
    public function getQueryBuilder(): QueryBuilder {
        return $this->queryBuilder;
    }

    /**
     * @param QueryBuilder $queryBuilder
     */
    public function setQueryBuilder(QueryBuilder $queryBuilder) {
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * @param $readOnly
     */
    public function setReadOnly($readOnly) {
        $this->readOnly = $readOnly;
    }

    /**
     * @return bool
     */
    public function isReadOnly() {
        return $this->readOnly;
    }

    /**
     * @return bool|mixed
     */
    public function getLogQueries() {
        return $this->logQueries;
    }

    /**
     * @param bool|mixed $logQueries
     */
    public function setLogQueries($logQueries) {
        $this->logQueries = $logQueries;
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
        $elapsedTime = microtime(true) - $startTimestamp;
        if ($this->logQueries) {
            get_logger()->debug("SQL: " . $this->getSqlSentence($sql, $bindings) . " [Time: " . number_format($elapsedTime, 4) . ", Results: " . sizeof($results) . "]");
        }
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
        $elapsedTime = microtime(true) - $startTimestamp;
        if ($this->logQueries) {
            get_logger()->debug("SQL: " . $this->getSqlSentence($sql, $bindings) . " [Time: " . number_format($elapsedTime, 4) . ", Affected rows: " . $affectedRows . "]");
        }
        return $affectedRows;
    }
}