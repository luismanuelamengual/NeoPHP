<?php

namespace NeoPHP\Database\Query\Traits;

use NeoPHP\Database\Query\ConditionGroup;

trait HavingConditionsTrait {

    private $havingConditions = null;

    public function hasHavingConditions() {
        return $this->havingConditions != null && !empty($this->havingConditions->conditions());
    }

    public function havingConditions(ConditionGroup $havingConditions = null) {
        $result = $this;
        if ($havingConditions != null) {
            $this->havingConditions = $havingConditions;
        }
        else {
            if ($this->havingConditions == null) {
                $this->havingConditions = new ConditionGroup();
            }
            $result = $this->havingConditions;
        }
        return $result;
    }

    public function havingConnector($connector=null) {
        $this->havingConditions()->connector($connector);
    }

    public function having ($column, $operatorOrValue, $value=null) {
        $this->havingConditions()->on($column, $operatorOrValue, $value);
        return $this;
    }

    public function havingGroup(ConditionGroup $group) {
        $this->havingConditions()->onGroup($group);
    }

    public function havingRaw($sql, array $bindings = []) {
        $this->havingConditions()->onRaw($sql, $bindings);
        return $this;
    }

    public function havingColumn($column, $operatorOrColumn, $otherColumn) {
        $this->havingConditions()->onColumn($column, $operatorOrColumn, $otherColumn);
        return $this;
    }

    public function havingNull($column) {
        $this->havingConditions()->onNull($column);
        return $this;
    }

    public function havingNotNull($column) {
        $this->havingConditions()->onNotNull($column);
        return $this;
    }
}