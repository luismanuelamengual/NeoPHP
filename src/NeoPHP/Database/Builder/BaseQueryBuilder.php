<?php

namespace NeoPHP\Database\Builder;

use NeoPHP\Database\Query\ConditionGroup;
use NeoPHP\Database\Query\Join;
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
                $sql .= $this->buildSelectFieldSql($selectField, $bindings);
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
                $sql .= $this->buildJoinSql($join, $bindings);
            }
        }
    }

    protected function buildSelectFieldSql ($field, array &$bindings) {
        $sql = "";
        if (is_string($field)) {
            $sql .= $field;
        }
        else if (is_array($field)) {
            if (isset($field["table"])) {
                $sql .= $field["table"];
                $sql .= ".";
            }
            $sql .= $field["field"];
            if (isset($field["alias"])) {
                $sql .= " AS ";
                $sql .= $field["alias"];
            }
        }
        return $sql;
    }

    protected function buildJoinSql (Join $join, array &$bindings) {
        $sql = "";
        switch ($join->getType()) {
            case Join::TYPE_JOIN: $sql .= "JOIN"; break;
            case Join::TYPE_INNER_JOIN: $sql .= "INNER JOIN"; break;
            case Join::TYPE_OUTER_JOIN: $sql .= "OUTER JOIN"; break;
            case Join::TYPE_LEFT_JOIN: $sql .= "LEFT JOIN"; break;
            case Join::TYPE_RIGHT_JOIN: $sql .= "RIGHT JOIN"; break;
        }
        $sql .= " " . $join->getTable();
        if (!$join->getConditions()->isEmpty()) {
            $sql .= " ON " . $this->buildConditionGroupSql($join->getConditions(), $bindings);
        }
        return $sql;
    }

    protected function buildConditionGroupSql (ConditionGroup $conditionGroup, array &$bindings) {
        $sql = "";
        $conditions = $conditionGroup->getConditions();
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
                    $sql .= "(" . $this->buildConditionGroupSql($condition, $bindings) . ")";
                }
            }
            else if (is_array($condition)) {
                $operator = $condition["operator"] ?: "=";
                $operator = strtoupper($operator);
                $sql .= $condition["field"];
                $sql .= " $operator";
                if (isset($condition["value"])) {
                    $value = $condition["value"];
                    $sql .= " " . $this->buildValueSql($value);
                }
            }
        }
    }

    protected function buildValueSql ($value, array &$bindings) {
        $sql = "";
        if (is_object($value)) {
            if ($value instanceof SelectQuery) {
                $sql .= "(" . $this->buildSelectSql($value, $bindings) . ")";
            }
            else if ($value instanceof RawValue) {
                $sql .= $value->getValue();
            }
        }
        else if (is_array($value)) {
            for ($i = 0; $i < sizeof($value); $i++) {
                if ($i > 0) {
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
        return $sql;
    }
}