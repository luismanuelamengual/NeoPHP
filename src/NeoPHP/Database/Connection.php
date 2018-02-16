<?php

namespace NeoPHP\Database;

use Closure;
use Exception;
use PDO;
use PDOStatement;

/**
 * Class Connection
 * @package NeoPHP\Database
 */
class Connection {

    private $pdo;
    private $readOnly;

    /**
     * Connection constructor.
     * @param PDO $pdo
     */
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->readOnly = false;
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
     * @return null|PDOStatement
     * @throws Exception
     */
    public final function query($sql, array $bindings = []) {
        $sqlSentence = $sql . (!empty($bindings) ? " [" . implode(",", $bindings) . "]" : "");
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

        return $queryStatement->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * @param $sql
     * @param array $bindings
     * @return bool|int
     * @throws Exception
     */
    public final function exec($sql, array $bindings = []) {
        $sqlSentence = $sql . (!empty($bindings) ? " [" . implode(",", $bindings) . "]" : "");
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
        return $affectedRows;
    }
}