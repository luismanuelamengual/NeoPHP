<?php

namespace NeoPHP\Database\Query\Traits;

use NeoPHP\Database\Query\ConditionGroup;

trait WhereConditionsTrait {

    private $whereConditions = null;

    public function hasWhereConditions() {
        return $this->whereConditions != null && !empty($this->whereConditions->conditions());
    }

    public function whereConditions(ConditionGroup $whereConditions = null) {
        $result = $this;
        if ($whereConditions != null) {
            $this->whereConditions = $whereConditions;
        }
        else {
            if ($this->whereConditions == null) {
                $this->whereConditions = new ConditionGroup();
            }
            $result = $this->whereConditions;
        }
        return $result;
    }

    public function whereConnector($connector=null) {
        $this->whereConditions()->connector($connector);
    }

    public function where ($column, $operatorOrValue, $value=null) {
        $this->whereConditions()->on($column, $operatorOrValue, $value);
        return $this;
    }

    public function whereGroup(ConditionGroup $group) {
        $this->whereConditions()->onGroup($group);
    }

    public function whereRaw($sql, array $bindings = []) {
        $this->whereConditions()->onRaw($sql, $bindings);
        return $this;
    }

    public function whereColumn($column, $operatorOrColumn, $otherColumn) {
        $this->whereConditions()->onColumn($column, $operatorOrColumn, $otherColumn);
        return $this;
    }

    public function whereNull($column) {
        $this->whereConditions()->onNull($column);
        return $this;
    }

    public function whereNotNull($column) {
        $this->whereConditions()->onNotNull($column);
        return $this;
    }
}