<?php

namespace NeoPHP\Resources;

use NeoPHP\Database\Query\DeleteQuery;
use NeoPHP\Database\Query\InsertQuery;
use NeoPHP\Database\Query\SelectQuery;
use NeoPHP\Database\Query\UpdateQuery;

/**
 * Class DefaultResourceManager
 * @package NeoPHP\Core\Resources
 */
class DefaultResourceManager extends ResourceManager {

    /**
     * DefaultResourceManager constructor.
     * @param $table
     */
    public function __construct($table) {
        $this->setTable($table);
    }

    /**
     * @return mixed
     */
    protected function getConnectionName() {
        return getProperty("database.default");
    }

    /**
     * @return mixed
     */
    protected function getConnection() {
        return getConnection($this->getConnectionName());
    }

    /**
     * @return SelectQuery
     */
    protected function createSelectQuery(): SelectQuery {
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
        return $query;
    }

    /**
     * @return InsertQuery
     */
    protected function createInsertQuery(): InsertQuery {
        $query = new InsertQuery($this->getTable());
        $query->setFields($this->getFields());
        return $query;
    }

    /**
     * @return UpdateQuery
     */
    protected function createUpdateQuery(): UpdateQuery {
        $query = new UpdateQuery($this->getTable());
        $query->setFields($this->getFields());
        $query->setWhereConditions($this->getWhereConditions());
        return $query;
    }

    /**
     * @return DeleteQuery
     */
    protected function createDeleteQuery(): DeleteQuery {
        $query = new DeleteQuery($this->getTable());
        $query->setWhereConditions($this->getWhereConditions());
        return $query;
    }

    /**
     * @return array|null|\PDOStatement
     */
    public function find() {
        return $this->getConnection()->query($this->createSelectQuery());
    }

    /**
     * @return bool|int
     */
    public function insert() {

        return $this->getConnection()->query($this->createInsertQuery());
    }

    /**
     * @return bool|int
     */
    public function update() {

        return $this->getConnection()->query($this->createUpdateQuery());
    }

    /**
     * @return bool|int
     */
    public function delete() {

        return $this->getConnection()->query($this->createDeleteQuery());
    }
}