<?php

namespace NeoPHP\Database\Query;

class SelectQuery extends Query {

    private $table;
    private $selectFields = [];
    private $whereConditions = null;
    private $havingConditions = null;
    private $orderByFields = [];
    private $groupByFields = [];
    private $joins = [];
    private $limit = null;
    private $offset = null;

    public function __construct() {
    }

    public function clearSelectFields() {
        $this->selectFields = [];
    }

    public function addSelectFields(...$fields) {
        foreach ($fields as $field) {
            $this->addSelectField($field);
        }
        return $this;
    }

    public function addSelectField(...$fieldArguments) {
        $field = null;
        switch (sizeof($fieldArguments)) {
            case 1:
                if (is_array($fieldArguments[0])) {
                    $field = $fieldArguments[0];
                }
                else {
                    $field = [
                        "field" => $fieldArguments[0]
                    ];
                }
                break;
            case 2:
                $field = [
                    "field" => $fieldArguments[0],
                    "alias" => $fieldArguments[1]
                ];
                break;
            case 3:
                $field = [
                    "field" => $fieldArguments[0],
                    "alias" => $fieldArguments[1],
                    "table" => $fieldArguments[2]
                ];
                break;
        }
        $this->selectFields[] = $field;
        return $this;
    }

    public function getSelectFields(): array {
        return $this->selectFields;
    }

    public function setTable($table) {
        $this->table = $table;
        return $this;
    }

    public function getTable() {
        return $this->table;
    }

    public function clearWhereConditions() {
        $this->whereConditions = null;
        return $this;
    }

    public function getWhereConditions(): ConditionGroup {
        if (!isset($this->whereConditions)) {
            $this->whereConditions = new ConditionGroup();
        }
        return $this->whereConditions;
    }

    public function setWhereConnector($connector) {
        $this->getWhereConditions()->setConnector($connector);
        return $this;
    }

    public function getWhereConnector() {
        return $this->getWhereConditions()->getConnector();
    }

    public function addWhere(...$arguments) {
        $this->getWhereConditions()->addCondition($arguments);
        return $this;
    }

    public function clearHavingConditions() {
        $this->havingConditions = null;
        return $this;
    }

    public function getHavingConditions(): ConditionGroup {
        if (!isset($this->havingConditions)) {
            $this->havingConditions = new ConditionGroup();
        }
        return $this->havingConditions;
    }

    public function setHavingConnector($connector) {
        $this->getHavingConditions()->setConnector($connector);
        return $this;
    }

    public function getHavingConnector() {
        return $this->getHavingConditions()->getConnector();
    }

    public function addHaving(...$arguments) {
        $this->getHavingConditions()->addCondition($arguments);
        return $this;
    }

    public function clearOrderByFields() {
        $this->orderByFields = [];
    }

    public function addOrderByFields(...$fields) {
        foreach ($fields as $field) {
            $this->addOrderByField($field);
        }
        return $this;
    }

    public function addOrderByField(...$fieldArguments) {
        $field = null;
        switch (sizeof($fieldArguments)) {
            case 1:
                if (is_array($fieldArguments[0])) {
                    $field = $fieldArguments[0];
                }
                else {
                    $field = [
                        "field" => $fieldArguments[0]
                    ];
                }
                break;
            case 2:
                $field = [
                    "field" => $fieldArguments[0],
                    "direction" => $fieldArguments[1]
                ];
                break;
        }
        $this->orderByFields[] = $field;
        return $this;
    }

    public function getOrderByFields(): array {
        return $this->orderByFields;
    }

    public function clearGroupByFields() {
        $this->groupByFields = [];
    }

    public function addGroupByFields(...$fields) {
        foreach ($fields as $field) {
            $this->addGroupByField($field);
        }
        return $this;
    }

    public function addGroupByField($field) {
        $this->groupByFields[] = $field;
        return $this;
    }

    public function getGroupByFields(): array {
        return $this->groupByFields;
    }

    public function getJoins () {
        return $this->joins;
    }

    public function clearJoins () {
        $this->joins = [];
        return $this;
    }

    public function addJoin (...$joinArgument) {
        $joinObj = null;
        switch (sizeof($joinArgument)) {
            case 1:
                if (is_a($joinArgument[0], Join::class)) {
                    $joinObj = $joinArgument[0];
                }
                break;
            case 3:
            case 4:
                $tableName = $joinArgument[0];
                $originField = $joinArgument[1];
                $destinationField = new RawValue($joinArgument[2]);
                $joinObj = new Join($tableName);
                if (isset($joinArgument[3])) {
                    $joinObj->setType($joinArgument[3]);
                }
                $joinObj->addCondition($originField, $destinationField);
                break;
        }
        if ($joinObj != null) {
            $this->joins[] = $joinObj;
        }
        return $this;
    }

    public function getLimit() {
        return $this->limit;
    }

    public function setLimit($limit) {
        $this->limit = $limit;
        return $this;
    }

    public function getOffset() {
        return $this->offset;
    }

    public function setOffset($offset) {
        $this->offset = $offset;
        return $this;
    }
}