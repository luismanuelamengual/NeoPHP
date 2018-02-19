<?php

namespace NeoPHP\Database;

use Closure;
use Exception;
use NeoPHP\Database\Builder\BaseQueryBuilder;
use NeoPHP\Database\Query\Query;
use NeoPHP\Database\Query\SelectQuery;
use PDO;

/**
 * Class Connection
 * @package NeoPHP\Database
 */
class Connection {

    private $pdo;
    private $readOnly;
    private $queryBuilder;

    /**
     * Connection constructor.
     * @param PDO $pdo
     */
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->readOnly = false;
        $this->queryBuilder = new BaseQueryBuilder();
    }

    /**
     * @return PDO
     */
    public function getPdo(): PDO {
        return $this->pdo;
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
     * @param $sql
     * @param array $bindings
     * @return mixed
     */
    private function getSqlSentence ($sql, array $bindings = []) {
        $sqlSentence = $sql;
        foreach ($bindings as $key=>$value) {
            if (!is_numeric($value)) {
                $value = $this->quote($value);
            }
            if (is_numeric($key)) {
                $sqlSentence = preg_replace('/'.preg_quote("?", '/').'/', $value, $sqlSentence, 1);
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
        $elapsedTime = microtime(true) - $startTimestamp;
        if (getProperty("app.debug")) {
            getLogger()->debug("SQL: " . $this->getSqlSentence($sql, $bindings) . " [" . number_format ($elapsedTime, 4) . "]");
        }
        return $queryStatement->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * @param $sql
     * @param array $bindings
     * @return bool|int
     * @throws Exception
     */
    public final function exec($sql, array $bindings = []) {
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
        if (getProperty("app.debug")) {
            getLogger()->debug("SQL: " . $this->getSqlSentence($sql, $bindings) . " [" . number_format ($elapsedTime, 4) . "]");
        }
        return $affectedRows;
    }

    /**
     * @param Query $query
     * @return array|bool|int|null
     * @throws Exception
     */
    public final function execQuery (Query $query) {
        $result = null;
        $bindings = [];
        $sql = $this->queryBuilder->buildSql($query, $bindings);
        if ($query instanceof SelectQuery) {
            $result = $this->query($sql, $bindings);
        }
        else {
            $result = $this->exec($sql, $bindings);
        }
        return $result;
    }
}