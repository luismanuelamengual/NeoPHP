<?php

namespace NeoPHP\Database;

use NeoPHP\Database\Query\DeleteQuery;
use NeoPHP\Database\Query\InsertQuery;
use NeoPHP\Database\Query\SelectQuery;
use NeoPHP\Database\Query\Traits\FieldsTrait;
use NeoPHP\Database\Query\Traits\GroupByFieldsTrait;
use NeoPHP\Database\Query\Traits\HavingConditionsTrait;
use NeoPHP\Database\Query\Traits\JoinsTrait;
use NeoPHP\Database\Query\Traits\OrderByFieldsTrait;
use NeoPHP\Database\Query\Traits\SelectFieldsTrait;
use NeoPHP\Database\Query\Traits\SelectModifiersTrait;
use NeoPHP\Database\Query\Traits\TableTrait;
use NeoPHP\Database\Query\Traits\WhereConditionsTrait;
use NeoPHP\Database\Query\UpdateQuery;

/**
 * Class ConnectionTable
 * @package NeoPHP\Database
 */
class ConnectionTable {

    use TableTrait,
        FieldsTrait,
        SelectModifiersTrait,
        SelectFieldsTrait,
        OrderByFieldsTrait,
        GroupByFieldsTrait,
        WhereConditionsTrait,
        HavingConditionsTrait,
        JoinsTrait;

    private $connection;

    /**
     * ConnectionTable constructor.
     * @param $connection
     * @param $table
     */
    public function __construct($connection, $table) {
        $this->table($table);
        $this->connection = $connection;
    }

    /**
     * @param $field
     * @return array
     */
    public function findField($field) {
        $fieldResults = [];
        $this->select([$field]);
        $results = $this->find();
        foreach ($results as $result) {
            $resultVars = get_object_vars($result);
            $fieldResults[] = reset($resultVars);
        }
        return $fieldResults;
    }

    /**
     * @return mixed
     */
    public function find() {
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
        return $this->connection->query($query);
    }

    /**
     * @return mixed
     */
    public function insert() {
        $query = new InsertQuery($this->table());
        $query->fields($this->fields());
        return $this->connection->exec($query);
    }

    /**
     * @return mixed
     */
    public function update() {
        $query = new UpdateQuery($this->table());
        $query->fields($this->fields());
        $query->whereConditions($this->whereConditions());
        return $this->connection->exec($query);
    }

    /**
     * @return mixed
     */
    public function delete() {
        $query = new DeleteQuery($this->table());
        $query->whereConditions($this->whereConditions());
        return $this->connection->exec($query);
    }
}