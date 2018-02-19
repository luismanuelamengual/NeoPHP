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
        $modifiers = $query->getModifiers();
        if (!empty($modifiers)) {
            foreach ($modifiers as $modifier) {
                $sql .= " $modifier";
            }
        }
        $sql .= " ";
        $selectFields = $query->getSelectFields();
        if (empty($selectFields)) {
            $sql .= "*";
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
        $sql .= " FROM ";
        $sql .= $query->getTable();
        $joins = $query->getJoins();
        if (!empty($joins)) {
            foreach ($joins as $join) {
                $sql .= " " . $this->buildJoinSql($join, $bindings);
            }
        }
        if ($query->hasWhereConditions()) {
            $sql .= " WHERE ";
            $sql .= $this->buildConditionGroupSql($query->getWhereConditions(), $bindings);
        }
        $groupByFields = $query->getGroupByFields();
        if (!empty($groupByFields)) {
            for ($i = 0; $i < sizeof($groupByFields); $i++) {
                if ($i > 0) {
                    $sql .= ", ";
                }
                $groupByField = $groupByFields[$i];
                $sql .= $this->buildGroupByFieldSql($groupByField, $bindings);
            }
        }
        return $sql;
    }

    protected function buildGroupByFieldSql ($field, array &$bindings) {
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
        }
        return $sql;
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
        if ($connector == ConditionGroup::CONNECTOR_AND) {
            $connector = "AND";
        }
        else if ($connector == ConditionGroup::CONNECTOR_OR) {
            $connector = "OR";
        }
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
                $operator = isset($condition["operator"])? $condition["operator"] : "=";
                $operator = strtoupper($operator);
                $sql .= $condition["field"];
                $sql .= " $operator";
                if (isset($condition["value"])) {
                    $value = $condition["value"];
                    $sql .= " " . $this->buildValueSql($value, $bindings);
                }
            }
        }
        return $sql;
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