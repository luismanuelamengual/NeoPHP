<?php

namespace NeoPHP\Resources;

use NeoPHP\Database\DB;
use NeoPHP\Database\Query\DeleteQuery;
use NeoPHP\Database\Query\InsertQuery;
use NeoPHP\Database\Query\SelectQuery;
use NeoPHP\Database\Query\UpdateQuery;

/**
 * Class ConnectionResourceManager
 * @package NeoPHP\Core\Resources
 */
class ConnectionResourceManager extends ResourceManager {

    private $connection;

    /**
     * ConnectionResourceManager constructor.
     * @param $config
     */
    public function __construct(array $config = []) {
        $this->table(isset($config["tableName"])? $config["tableName"] : $config["resourceName"]);
        $connectionName = isset($config["databaseName"])? $config["databaseName"] : get_property("database.default");
        $this->connection = DB::connection($connectionName);
    }

    /**
     * @return mixed
     */
    protected function getConnection() {
        return $this->connection;
    }

    /**
     * @return SelectQuery
     */
    protected function createSelectQuery(): SelectQuery {
        $query = new SelectQuery($this->table());
        $query->limit($this->limit());
        $query->offset($this->offset());
        $query->distinct($this->distinct());
        $query->selectFields($this->selectFields());
        $query->orderByFields($this->orderByFields());
        $query->groupByFields($this->groupByFields());
        $query->whereConditions($this->whereConditions());
        $query->havingConditions($this->havingConditions());
        $query->joins($this->joins());
        return $query;
    }

    /**
     * @return InsertQuery
     */
    protected function createInsertQuery(): InsertQuery {
        $query = new InsertQuery($this->table());
        $query->fields($this->fields());
        return $query;
    }

    /**
     * @return UpdateQuery
     */
    protected function createUpdateQuery(): UpdateQuery {
        $query = new UpdateQuery($this->table());
        $query->fields($this->fields());
        $query->whereConditions($this->whereConditions());
        return $query;
    }

    /**
     * @return DeleteQuery
     */
    protected function createDeleteQuery(): DeleteQuery {
        $query = new DeleteQuery($this->table());
        $query->whereConditions($this->whereConditions());
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