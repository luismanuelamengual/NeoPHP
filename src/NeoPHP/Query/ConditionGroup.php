<?php

namespace NeoPHP\Query;

use stdClass;

class ConditionGroup {

    const CONNECTOR_AND = "AND";
    const CONNECTOR_OR = "OR";

    private $conditions = [];
    private $connector;

    public function __construct($connector=self::CONNECTOR_AND) {
        $this->connector = $connector;
    }

    public function __clone() {
        $clonedConditions = [];
        foreach ($this->conditions as $condition) {
            $clonedConditions[] = clone $condition;
        }
        $this->conditions = $clonedConditions;
    }

    public function connector($connector) {
        $this->connector = $connector;
        return $this;
    }

    public function getConnector() {
        return $this->connector;
    }

    public function conditions($conditions) {
        $this->conditions = $conditions;
        return $this;
    }

    public function &getConditions() {
        return $this->conditions;
    }

    public function isEmpty() {
        return empty($this->conditions);
    }

    public function on ($field, $operatorOrValue, $value=null) {
        if ($value !== null) {
            $operator = ConditionOperator::getOperator($operatorOrValue);
        }
        else if (is_array($operatorOrValue)) {
            if (sizeof($operatorOrValue) == 1) {
                $value = current($operatorOrValue);
                $operator = ConditionOperator::EQUALS;
            } else {
                $value = $operatorOrValue;
                $operator = ConditionOperator::IN;
            }
        } else {
            $value = $operatorOrValue;
            $operator = ConditionOperator::EQUALS;
        }
        $condition = new stdClass();
        $condition->type = ConditionType::BASIC;
        $condition->field = $field;
        $condition->operator = $operator;
        $condition->value = $value;
        $this->conditions[] = $condition;
        return $this;
    }

    public function onGroup(ConditionGroup $group) {
        $condition = new stdClass();
        $condition->type = ConditionType::GROUP;
        $condition->group = $group;
        $this->conditions[] = $condition;
        return $this;
    }

    public function onRaw($sql, array $bindings = []) {
        $condition = new stdClass();
        $condition->type = ConditionType::RAW;
        $condition->sql = $sql;
        $condition->bindings = $bindings;
        $this->conditions[] = $condition;
        return $this;
    }

    public function onField($field, $operatorOrField, $otherField=null) {
        if ($otherField != null) {
            $operator = ConditionOperator::getOperator($operatorOrField);
        }
        else {
            $operator = ConditionOperator::EQUALS_FIELD;
            $otherField = $operatorOrField;
        }
        $condition = new stdClass();
        $condition->type = ConditionType::BASIC;
        $condition->field = $field;
        $condition->operator = $operator;
        $condition->value = $otherField;
        $this->conditions[] = $condition;
        return $this;
    }

    public function onNull($field) {
        $condition = new stdClass();
        $condition->type = ConditionType::BASIC;
        $condition->operator = ConditionOperator::NULL;
        $condition->field = $field;
        $this->conditions[] = $condition;
        return $this;
    }

    public function onNotNull($field) {
        $condition = new stdClass();
        $condition->type = ConditionType::BASIC;
        $condition->operator = ConditionOperator::NOT_NULL;
        $condition->field = $field;
        $this->conditions[] = $condition;
        return $this;
    }

    public function onIn($field, $value) {
        $condition = new stdClass();
        $condition->type = ConditionType::BASIC;
        $condition->field = $field;
        $condition->operator = ConditionOperator::IN;
        $condition->value = $value;
        $this->conditions[] = $condition;
        return $this;
    }

    public function onNotIn($field, $value) {
        $condition = new stdClass();
        $condition->type = ConditionType::BASIC;
        $condition->field = $field;
        $condition->operator = ConditionOperator::NOT_IN;
        $condition->value = $value;
        $this->conditions[] = $condition;
        return $this;
    }

    public function onLike($field, $value, $caseSensitive=false) {
        $condition = new stdClass();
        $condition->type = ConditionType::BASIC;
        $condition->field = $field;
        $condition->operator = ConditionOperator::LIKE;
        $condition->value = $value;
        $condition->caseSensitive = $caseSensitive;
        $this->conditions[] = $condition;
        return $this;
    }

    public function onNotLike($field, $value, $caseSensitive=false) {
        $condition = new stdClass();
        $condition->type = ConditionType::BASIC;
        $condition->field = $field;
        $condition->operator = ConditionOperator::NOT_LIKE;
        $condition->value = $value;
        $condition->caseSensitive = $caseSensitive;
        $this->conditions[] = $condition;
        return $this;
    }

    public function getCondition ($field, $operator = null, $caseSensitive = true, $mandatory = false) {
        return $this->findCondition($this, $field, $operator, $caseSensitive, $mandatory);
    }

    public function removeCondition ($field, $operator = null, $caseSensitive = true, $mandatory = false) {
        return $this->findCondition($this, $field, $operator, $caseSensitive, $mandatory, true);
    }

    private function findCondition (ConditionGroup &$conditionGroup, $field, $operator = null, $caseSensitive = true, $mandatory = false, $removeCondition = false) {
        $foundCondition = null;
        if (!$conditionGroup->isEmpty() && (!$mandatory || $conditionGroup->getConnector() == ConditionGroup::CONNECTOR_AND) ) {
            if ($operator != null) {
                if (is_array($operator)) {
                    foreach ($operator as &$op) {
                        $op = ConditionOperator::getOperator($op);
                    }
                } else {
                    $operator = ConditionOperator::getOperator($operator);
                }
            }
            $conditions = &$conditionGroup->getConditions();
            foreach ($conditions as $key=>$condition) {
                if ($condition->type == ConditionType::GROUP) {
                    if (!$mandatory) {
                        $foundCondition = $this->findCondition($condition->group, $field, $operator, $caseSensitive, $mandatory, $removeCondition);
                        if ($foundCondition != null) {
                            break;
                        }
                    }
                }
                else if ($condition->type != ConditionType::RAW) {
                    if ($caseSensitive ? ($condition->field == $field) : (strcasecmp($condition->field, $field) == 0)) {
                        if ($operator == null || (is_array($operator) ? in_array($condition->operator, $operator) : $condition->operator == $operator)) {
                            $foundCondition = $condition;
                            if ($removeCondition) {
                                unset($conditions[$key]);
                                $conditions = array_values($conditions);
                            }
                            break;
                        }
                    }
                }
            }
        }
        return $foundCondition;
    }
}