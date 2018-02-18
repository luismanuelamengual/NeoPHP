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
            $sqlSentence = str_replace(":$key", $this->quote($value), $sqlSentence);
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
        $sqlSentence = $this->getSqlSentence($sql, $bindings);
        $queryStatement = null;
        if (empty($bindings)) {
            $queryStatement = $this->pdo->query($sql);
            if (!$queryStatement) {
                throw new Exception ("Unable to execute sql statement \"" . $sqlSentence . "\" " . $this->pdo->errorInfo()[2]);
            }
        }
        else {
            $queryStatement = $this->pdo->prepare($sql);
            if ($queryStatement == false) {
                throw new Exception ("Unable to prepare sql statement \"" . $sqlSentence . "\"");
            }
            $sqlExecuted = $queryStatement->execute($bindings);
            if (!$sqlExecuted) {
                throw new Exception ("Unable to execute prepared statement \"" . $sqlSentence . "\" " . $queryStatement->errorInfo()[2]);
            }
        }
        if (getProperty("app.debug")) {
            getLogger()->debug("SQL: $sqlSentence");
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
        $sqlSentence = $this->getSqlSentence($sql, $bindings);
        $affectedRows = false;
        if (!$this->readOnly) {
            if (empty($bindings)) {
                $affectedRows = $this->pdo->exec($sql);
                if (!$affectedRows) {
                    throw new Exception ("Unable to execute sql \"" . $sqlSentence . "\" " . $this->pdo->errorInfo()[2]);
                }
            }
            else {
                $preparedStatement = $this->pdo->prepare($sql);
                if ($preparedStatement == false) {
                    throw new Exception ("Unable to prepare sql statement \"" . $sqlSentence . "\"");
                }
                $sqlExecuted = $preparedStatement->execute($bindings);
                if (!$sqlExecuted) {
                    throw new Exception ("Unable to execute prepared statement \"" . $sqlSentence . "\" " . $preparedStatement->errorInfo()[2]);
                }
                $affectedRows = $preparedStatement->rowCount();
            }
        }
        if (getProperty("app.debug")) {
            getLogger()->debug("SQL: $sqlSentence");
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