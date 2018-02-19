<?php

namespace NeoPHP\Database\Query\Traits;

use NeoPHP\Database\Query\ConditionGroup;

trait HavingConditionsTrait {

    private $havingConditions = null;

    public function clearHavingConditions() {
        $this->havingConditions = null;
        return $this;
    }

    public function hasHavingConditions() {
        return isset($this->havingConditions) && !$this->havingConditions->isEmpty();
    }

    public function getHavingConditions(): ConditionGroup {
        if (!isset($this->havingConditions)) {
            $this->havingConditions = new ConditionGroup();
        }
        return $this->havingConditions;
    }

    public function setHavingConditions($havingConditions) {
        $this->havingConditions = $havingConditions;
    }

    public function setHavingConnector($connector) {
        $this->getHavingConditions()->setConnector($connector);
        return $this;
    }

    public function getHavingConnector() {
        return $this->getHavingConditions()->getConnector();
    }

    public function addHaving(...$arguments) {
        call_user_func_array([$this->getHavingConditions(), "addCondition"], $arguments);
        return $this;
    }
}