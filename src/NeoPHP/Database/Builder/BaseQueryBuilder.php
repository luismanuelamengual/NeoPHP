<?php

namespace NeoPHP\Database\Builder;

use NeoPHP\Database\Query\ConditionGroup;
use NeoPHP\Database\Query\Query;
use NeoPHP\Database\Query\RawValue;
use NeoPHP\Database\Query\SelectQuery;

class BaseQueryBuilder extends QueryBuilder {

    public function buildSql(Query $query, array &$bindings) {
        $sql = null;
        if ($query instanceof SelectQuery) {
            $sql = $this->buildSelectSql($query, $bindings);
        }
        return $sql;
    }

    protected function buildSelectSql (SelectQuery $query, array &$bindings) {
        $sql = "SELECT";
        $modifiersSql = $this->buildModifiersSql($query, $bindings);
        if (!empty($modifiersSql)) {
            $sql .= " $modifiersSql";
        }
        $selectFieldsSql = $this->buildSelectFieldsSql($query, $bindings);
        if (!empty($selectFieldsSql)) {
            $sql .= " $selectFieldsSql";
        }
        $sql .= " FROM ";
        $sql .= $query->getTable();
        return $sql;
    }

    protected function buildModifiersSql (SelectQuery $query, array &$bindings) {
        $sql = null;
        $modifiers = $query->getModifiers();
        if (!empty($modifiers)) {
            for ($i = 0; $i < sizeof($modifiers); $i++) {
                if ($i > 0) {
                    $sql .= ", ";
                }
                $sql .= $modifiers[$i];
            }
        }
        return $sql;
    }

    protected function buildSelectFieldsSql (SelectQuery $query, array &$bindings) {
        $sql = null;
        $selectFields = $query->getSelectFields();
        if (empty($selectFields)) {
            $sql = "*";
        }
        else {
            for ($i = 0; $i < sizeof($selectFields); $i++) {
                if ($i > 0) {
                    $sql .= ", ";
                }
                $selectField = $selectFields[$i];
                if (is_string($selectField)) {
                    $sql .= $selectField;
                }
                else if (is_array($selectField)) {
                    if (isset($selectField["table"])) {
                        $sql .= $selectField["table"];
                        $sql .= ".";
                    }
                    $sql .= $selectField["field"];
                    if (isset($selectField["alias"])) {
                        $sql .= " AS ";
                        $sql .= $selectField["alias"];
                    }
                }
            }
        }
        return $sql;
    }

    protected function buildJoinsSql (SelectQuery $query, array &$bindings) {
        $sql = null;
        $joins = $query->getJoins();
        if (!empty($joins)) {
            for ($i = 0; $i < sizeof($joins); $i++) {
                if ($i > 0) {
                    $sql .= " ";
                }
                $join = $joins[$i];
            }
        }
    }

    protected function buildConditionsSql (ConditionGroup $conditionGroup, array &$bindings) {
        $sql = null;
        $conditions = $conditionGroup->getConditions();
        if (!empty($conditions)) {
            $connector = $conditionGroup->getConnector();
            for ($i = 0; $i < sizeof($conditions); $i++) {
                if ($i > 0) {
                    $sql .= " $connector ";
                }
                $condition = $conditions[$i];
                if (is_string($condition)) {
                    $sql .= $condition;
                }
                else if (is_object($condition)) {
                    if (is_a($condition, ConditionGroup::class)) {
                        $sql .= "(" . $this->buildConditionsSql($condition, $bindings) . ")";
                    }
                }
                else if (is_array($condition)) {
                    $operator = $condition["operator"] ?: "=";
                    $operator = strtoupper($operator);
                    $value = $condition["value"];
                    $sql .= $condition["field"];
                    $sql .= " $operator ";
                    if (is_object($value)) {
                        if ($value instanceof SelectQuery) {
                            $sql .= "(" . $this->buildSelectSql($value, $bindings) . ")";
                        }
                        else if ($value instanceof RawValue) {
                            $sql .= $value->getValue();
                        }
                    }
                    else if (is_array($value)) {
                        for ($j = 0; $j < sizeof($value); $j++) {
                            if ($j > 0) {
                                $sql .= ", ";
                            }
                            $sql .= "?";
                            $bindings[] = $value[$i];
                        }
                    }
                    else {
                        $sql .= "?";
                        $bindings[] = $value;
                    }
                }
            }
        }
    }
}