<?php

namespace NeoPHP\Database\Query\Traits;

use NeoPHP\Database\Query\ConditionGroup;

trait HavingConditionsTrait {

    private $havingConditions = null;

    public function hasHavingConditions() {
        return $this->havingConditions != null && !empty($this->havingConditions->getConditions());
    }

    public function havingConditionGroup(ConditionGroup $havingConditions) {
        $this->havingConditions = $havingConditions;
        return $this;
    }

    public function &getHavingConditionGroup () {
        if ($this->havingConditions == null) {
            $this->havingConditions = new ConditionGroup();
        }
        return $this->havingConditions;
    }

    public function havingConnector($connector=null) {
        $this->getHavingConditionGroup()->connector($connector);
    }

    public function having ($field, $operatorOrValue, $value=null) {
        $this->getHavingConditionGroup()->on($field, $operatorOrValue, $value);
        return $this;
    }

    public function havingGroup(ConditionGroup $group) {
        $this->getHavingConditionGroup()->onGroup($group);
    }

    public function havingRaw($sql, array $bindings = []) {
        $this->getHavingConditionGroup()->onRaw($sql, $bindings);
        return $this;
    }

    public function havingField($field, $operatorOrField, $otherField=null) {
        $this->getHavingConditionGroup()->onField($field, $operatorOrField, $otherField);
        return $this;
    }

    public function havingNull($field) {
        $this->getHavingConditionGroup()->onNull($field);
        return $this;
    }

    public function havingNotNull($field) {
        $this->getHavingConditionGroup()->onNotNull($field);
        return $this;
    }

    public function havingIn($field, $value) {
        $this->getHavingConditionGroup()->onIn($field, $value);
        return $this;
    }

    public function havingNotIn($field, $value) {
        $this->getHavingConditionGroup()->onNotIn($field, $value);
        return $this;
    }

    public function havingLike($field, $value, $caseSensitive=false) {
        $this->getHavingConditionGroup()->onLike($field, $value, $caseSensitive);
        return $this;
    }

    public function havingNotLike($field, $value, $caseSensitive=false) {
        $this->getHavingConditionGroup()->onNotLike($field, $value, $caseSensitive);
        return $this;
    }
}