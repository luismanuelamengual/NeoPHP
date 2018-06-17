<?php

namespace NeoPHP\Database\Query\Traits;

use NeoPHP\Database\Query\ConditionGroup;

trait WhereConditionsTrait {

    private $whereConditions = null;

    public function hasWhereConditions() {
        return $this->whereConditions != null && !empty($this->whereConditions->getConditions());
    }

    public function whereConditionGroup(ConditionGroup $whereConditions) {
        $this->whereConditions = $whereConditions;
        return $this;
    }

    public function &getWhereConditionGroup () {
        if ($this->whereConditions == null) {
            $this->whereConditions = new ConditionGroup();
        }
        return $this->whereConditions;
    }

    public function whereConnector($connector=null) {
        $this->getWhereConditionGroup()->connector($connector);
        return $this;
    }

    public function where ($field, $operatorOrValue, $value=null) {
        $this->getWhereConditionGroup()->on($field, $operatorOrValue, $value);
        return $this;
    }

    public function whereGroup(ConditionGroup $group) {
        $this->getWhereConditionGroup()->onGroup($group);
        return $this;
    }

    public function whereRaw($sql, array $bindings = []) {
        $this->getWhereConditionGroup()->onRaw($sql, $bindings);
        return $this;
    }

    public function whereField($field, $operatorOrField, $otherField=null) {
        $this->getWhereConditionGroup()->onField($field, $operatorOrField, $otherField);
        return $this;
    }

    public function whereNull($field) {
        $this->getWhereConditionGroup()->onNull($field);
        return $this;
    }

    public function whereNotNull($field) {
        $this->getWhereConditionGroup()->onNotNull($field);
        return $this;
    }

    public function whereIn($field, $value) {
        $this->getWhereConditionGroup()->onIn($field, $value);
        return $this;
    }

    public function whereNotIn($field, $value) {
        $this->getWhereConditionGroup()->onNotIn($field, $value);
        return $this;
    }

    public function whereLike($field, $value, $caseSensitive=false) {
        $this->getWhereConditionGroup()->onLike($field, $value, $caseSensitive);
        return $this;
    }

    public function whereNotLike($field, $value, $caseSensitive=false) {
        $this->getWhereConditionGroup()->onNotLike($field, $value, $caseSensitive);
        return $this;
    }
}