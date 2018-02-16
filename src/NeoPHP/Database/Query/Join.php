<?php

namespace NeoPHP\Database\Query;

/**
 * Class Join
 * @package NeoPHP\Database\Query
 */
class Join {

    const TYPE_JOIN = "JOIN";
    const TYPE_INNER_JOIN = "INNER";
    const TYPE_OUTER_JOIN = "OUTER";
    const TYPE_LEFT_JOIN = "LEFT";
    const TYPE_RIGHT_JOIN = "RIGHT";

    private $table;
    private $type;
    private $conditions = [];

    /**
     * Join constructor.
     * @param $type
     */
    public function __construct($table, $type=self::TYPE_INNER_JOIN) {
        $this->table = $table;
        $this->type = $type;
        $this->conditions = new ConditionGroup();
    }

    /**
     * @return mixed
     */
    public function getTable() {
        return $this->table;
    }

    /**
     * @param ConditionGroup $conditions
     * @return $this
     */
    public function setConditions(ConditionGroup $conditions) {
        $this->conditions = $conditions;
        return $this;
    }

    /**
     * @return ConditionGroup
     */
    public function getConditions(): ConditionGroup {
        return $this->conditions;
    }

    /**
     * @param $connector
     * @return $this
     */
    public function setConditionsConnector($connector) {
        $this->conditions->setConnector($connector);
        return $this;
    }

    /**
     * @return string
     */
    public function getConditionsConnector() {
        return $this->conditions->getConnector();
    }

    /**
     * @param array ...$arguments
     * @return $this
     */
    public function addCondition(...$arguments) {
        $this->conditions->addCondition($arguments);
        return $this;
    }
}