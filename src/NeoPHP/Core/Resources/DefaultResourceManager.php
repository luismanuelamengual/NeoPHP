<?php

namespace NeoPHP\Core\Resources;

use NeoPHP\Database\Query\DeleteQuery;
use NeoPHP\Database\Query\InsertQuery;
use NeoPHP\Database\Query\SelectQuery;
use NeoPHP\Database\Query\UpdateQuery;

/**
 * Class DefaultResourceManager
 * @package NeoPHP\Core\Resources
 */
class DefaultResourceManager extends ResourceManager {

    private $connection;

    /**
     * DefaultResourceManager constructor.
     * @param $table
     */
    public function __construct($table) {
        $this->setTable($table);
        $this->connection = $this->getResourceConnection();
    }

    /**
     * @return \NeoPHP\Database\Connection
     */
    protected function getResourceConnection () {
        return getConnection();
    }

    /**
     * @return array|null|\PDOStatement
     */
    public function find() {
        $query = new SelectQuery($this->getTable());
        $query->setModifiers($this->getModifiers());
        $query->setSelectFields($this->getSelectFields());
        $query->setJoins($this->getJoins());
        $query->setOrderByFields($this->getOrderByFields());
        $query->setGroupByFields($this->getGroupByFields());
        $query->setWhereConditions($this->getWhereConditions());
        $query->setHavingConditions($this->getHavingConditions());
        $query->setOffset($this->getOffset());
        $query->setLimit($this->getLimit());
        return $this->connection->query($query);
    }

    /**
     * @return bool|int
     */
    public function insert() {
        $query = new InsertQuery($this->getTable());
        $query->setFields($this->getFields());
        return $this->connection->exec($query);
    }

    /**
     * @return bool|int
     */
    public function update() {
        $query = new UpdateQuery($this->getTable());
        $query->setFields($this->getFields());
        $query->setWhereConditions($this->getWhereConditions());
        return $this->connection->exec($query);
    }

    /**
     * @return bool|int
     */
    public function delete() {
        $query = new DeleteQuery($this->getTable());
        $query->setWhereConditions($this->getWhereConditions());
        return $this->connection->exec($query);
    }
}