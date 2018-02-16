<?php

namespace NeoPHP\Database\Query;

/**
 * Class ConditionGroup
 * @package NeoPHP\Database\Query
 */
class ConditionGroup {

    const CONNECTOR_AND = "AND";
    const CONNECTOR_OR = "OR";

    private $conditions = [];
    private $connector;

    /**
     * ConditionGroup constructor.
     * @param string $connector
     */
    public function __construct($connector=self::CONNECTOR_AND) {
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
     * @return ConditionGroup
     */
    public function setConnector(string $connector) {
        $this->connector = $connector;
        return $this;
    }

    /**
     * @return array
     */
    public function getConditions(): array {
        return $this->conditions;
    }

    /**
     * @param array $conditions
     * @return ConditionGroup
     */
    public function setConditions(array $conditions) {
        $this->conditions = $conditions;
        return $this;
    }

    /**
     *
     */
    public function clearConditions() {
        $this->conditions = [];
        return $this;
    }

    /**
     * @param array ...$arguments
     * @return ConditionGroup
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
        return $this;
    }
}