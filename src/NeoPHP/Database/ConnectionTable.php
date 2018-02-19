<?php

namespace NeoPHP\Database;

use NeoPHP\Database\Query\DeleteQuery;
use NeoPHP\Database\Query\InsertQuery;
use NeoPHP\Database\Query\SelectQuery;
use NeoPHP\Database\Query\Traits\FieldsTrait;
use NeoPHP\Database\Query\Traits\GroupByFieldsTrait;
use NeoPHP\Database\Query\Traits\HavingConditionsTrait;
use NeoPHP\Database\Query\Traits\JoinsTrait;
use NeoPHP\Database\Query\Traits\ModifiersTrait;
use NeoPHP\Database\Query\Traits\OffsetAndLimitTrait;
use NeoPHP\Database\Query\Traits\OrderByFieldsTrait;
use NeoPHP\Database\Query\Traits\SelectFieldsTrait;
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
        ModifiersTrait,
        SelectFieldsTrait,
        OrderByFieldsTrait,
        GroupByFieldsTrait,
        WhereConditionsTrait,
        HavingConditionsTrait,
        OffsetAndLimitTrait,
        JoinsTrait;

    private $connection;

    /**
     * ConnectionTable constructor.
     * @param $connection
     * @param $table
     */
    public function __construct($connection, $table) {
        $this->setTable($table);
        $this->connection = $connection;
    }

    /**
     * @return mixed
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
     * @return mixed
     */
    public function insert() {
        $query = new InsertQuery($this->getTable());
        $query->setFields($this->getFields());
        return $this->connection->exec($query);
    }

    /**
     * @return mixed
     */
    public function update() {
        $query = new UpdateQuery($this->getTable());
        $query->setFields($this->getFields());
        $query->setWhereConditions($this->getWhereConditions());
        return $this->connection->exec($query);
    }

    /**
     * @return mixed
     */
    public function delete() {
        $query = new DeleteQuery($this->getTable());
        $query->setWhereConditions($this->getWhereConditions());
        return $this->connection->exec($query);
    }
}