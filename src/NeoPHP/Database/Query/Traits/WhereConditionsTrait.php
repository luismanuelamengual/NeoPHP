<?php

namespace NeoPHP\Database\Query\Traits;

use NeoPHP\Database\Query\ConditionGroup;

trait WhereConditionsTrait {

    private $whereConditions = null;

    public function clearWhereConditions() {
        $this->whereConditions = null;
        return $this;
    }

    public function hasWhereConditions() {
        return isset($this->whereConditions) && !$this->whereConditions->isEmpty();
    }

    public function getWhereConditions(): ConditionGroup {
        if (!isset($this->whereConditions)) {
            $this->whereConditions = new ConditionGroup();
        }
        return $this->whereConditions;
    }

    public function setWhereConditions(ConditionGroup $whereConditions) {
        $this->whereConditions = $whereConditions;
        return $this;
    }

    public function setWhereConnector($connector) {
        $this->getWhereConditions()->setConnector($connector);
        return $this;
    }

    public function getWhereConnector() {
        return $this->getWhereConditions()->getConnector();
    }

    public function addWhere(...$arguments) {
        call_user_func_array([$this->getWhereConditions(), "addCondition"], $arguments);
        return $this;
    }
}