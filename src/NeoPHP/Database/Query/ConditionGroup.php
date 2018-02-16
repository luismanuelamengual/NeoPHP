<?php

namespace NeoPHP\Database\Query;

/**
 * Class ConditionGroup
 * @package NeoPHP\Database\Query
 */
class ConditionGroup {

    private $conditions = [];
    private $connector;

    /**
     * ConditionGroup constructor.
     * @param string $connector
     */
    public function __construct($connector="AND") {
        $this->connector = $connector;
    }

    /**
     * @return string
     */
    public function getConnector(): string {
        return $this->connector;
    }

    /**
     * @param string $connector
     */
    public function setConnector(string $connector) {
        $this->connector = $connector;
    }

    /**
     * @return array
     */
    public function getConditions(): array {
        return $this->conditions;
    }

    /**
     * @param array $conditions
     */
    public function setConditions(array $conditions) {
        $this->conditions = $conditions;
    }

    /**
     *
     */
    public function clearConditions() {
        $this->conditions = [];
    }

    /**
     * @param array ...$arguments
     */
    public function addCondition(...$arguments) {
        $condition = null;
        switch (sizeof($arguments)) {
            case 1:
                $condition = $arguments[0];
                break;
            case 2:
                $condition = new \stdClass();
                $condition->field = $arguments[0];
                $condition->value = $arguments[1];
                break;
            case 3:
                $condition = new \stdClass();
                $condition->field = $arguments[0];
                $condition->operator = $arguments[1];
                $condition->value = $arguments[2];
                break;
        }
        $this->conditions[] = $condition;
    }
}