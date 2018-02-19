<?php

namespace NeoPHP\Database\Builder;

use NeoPHP\Database\Query\ConditionGroup;
use NeoPHP\Database\Query\DeleteQuery;
use NeoPHP\Database\Query\InsertQuery;
use NeoPHP\Database\Query\Join;
use NeoPHP\Database\Query\Query;
use NeoPHP\Database\Query\RawValue;
use NeoPHP\Database\Query\SelectQuery;
use NeoPHP\Database\Query\UpdateQuery;

class BaseQueryBuilder extends QueryBuilder {

    public function buildSql(Query $query, array &$bindings) {
        $sql = null;
        if ($query instanceof SelectQuery) {
            $sql = $this->buildSelectSql($query, $bindings);
        }
        else if ($query instanceof InsertQuery) {
            $sql = $this->buildInsertSql($query, $bindings);
        }
        else if ($query instanceof UpdateQuery) {
            $sql = $this->buildUpdateSql($query, $bindings);
        }
        else if ($query instanceof DeleteQuery) {
            $sql = $this->buildDeleteSql($query, $bindings);
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
        $sql .= $this->buildTableSql($query->getTable(), $bindings);
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
            $sql .= " GROUP BY ";
            for ($i = 0; $i < sizeof($groupByFields); $i++) {
                if ($i > 0) {
                    $sql .= ", ";
                }
                $groupByField = $groupByFields[$i];
                $sql .= $this->buildGroupByFieldSql($groupByField, $bindings);
            }
        }
        $orderByFields = $query->getOrderByFields();
        if (!empty($orderByFields)) {
            $sql .= " ORDER BY ";
            for ($i = 0; $i < sizeof($orderByFields); $i++) {
                if ($i > 0) {
                    $sql .= ", ";
                }
                $orderByField = $orderByFields[$i];
                $sql .= $this->buildOrderByFieldSql($orderByField, $bindings);
            }
        }
        if ($query->hasHavingConditions()) {
            $sql .= " HAVING ";
            $sql .= $this->buildConditionGroupSql($query->getHavingConditions(), $bindings);
        }
        if ($query->getOffset() != null) {
            $sql .= " OFFSET " . $query->getOffset();
        }
        if ($query->getLimit() != null) {
            $sql .= " LIMIT " . $query->getLimit();
        }
        return $sql;
    }

    protected function buildInsertSql (InsertQuery $query, array &$bindings) {
        $sql = "INSERT INTO ";
        $sql .= $this->buildTableSql($query->getTable(), $bindings);
        $fieldsSql = "";
        $valuesSql = "";
        $i = 0;
        foreach ($query->getFields() as $field => $value) {
            if ($i > 0) {
                $fieldsSql .= ", ";
                $valuesSql .= ", ";
            }
            $fieldsSql .= $field;
            $valuesSql .= $this->buildValueSql($value, $bindings);
            $i++;
        }
        $sql .= " ($fieldsSql) VALUES ($valuesSql)";
        return $sql;
    }

    protected function buildUpdateSql (UpdateQuery $query, array &$bindings) {
        $sql = "UPDATE ";
        $sql .= $this->buildTableSql($query->getTable(), $bindings);
        $sql .= " SET ";
        $i = 0;
        foreach ($query->getFields() as $field => $value) {
            if ($i > 0) {
                $sql .= ", ";
            }
            $sql .= $field;
            $sql .= " = ";
            $sql .= $this->buildValueSql($value, $bindings);
            $i++;
        }
        if ($query->hasWhereConditions()) {
            $sql .= " WHERE ";
            $sql .= $this->buildConditionGroupSql($query->getWhereConditions(), $bindings);
        }
        return $sql;
    }

    protected function buildDeleteSql (DeleteQuery $query, array &$bindings) {
        $sql = "DELETE FROM ";
        $sql .= $this->buildTableSql($query->getTable(), $bindings);
        if ($query->hasWhereConditions()) {
            $sql .= " WHERE ";
            $sql .= $this->buildConditionGroupSql($query->getWhereConditions(), $bindings);
        }
        return $sql;
    }

    protected function buildTableSql ($table, array &$bindings) {
        $sql = "";
        if (is_string($table)) {
            $sql .= $table;
        }
        else if (is_array($table)) {
            if (isset($table["database"])) {
                $sql .= $table["database"];
                $sql .= ".";
            }
            $sql .= $table["name"];
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
                $sql .= $this->buildTableSql($field["table"], $bindings);
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
                $sql .= $this->buildTableSql($field["table"], $bindings);
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

    protected function buildOrderByFieldSql ($field, array &$bindings) {
        $sql = "";
        if (is_string($field)) {
            $sql .= $field;
        }
        else if (is_array($field)) {
            if (isset($field["table"])) {
                $sql .= $this->buildTableSql($field["table"], $bindings);
                $sql .= ".";
            }
            $sql .= $field["field"];
            if (isset($field["direction"])) {
                $sql .= " " . strtoupper($field["direction"]) . " ";
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
            $sql .= $this->buildConditionSql($condition, $bindings);
        }
        return $sql;
    }

    protected function buildConditionSql ($condition, array &$bindings) {
        $sql = "";
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
        return $sql;
    }

    protected function buildValueSql ($value, array &$bindings) {
        $sql = "";
        if (is_object($value)) {
            if ($value instanceof Query) {
                $sql .= "(" . $this->buildSql($value, $bindings) . ")";
            }
            else if ($value instanceof RawValue) {
                $sql .= $value->getValue();
            }
        }
        else if (is_array($value)) {
            $sql .= "(";
            for ($i = 0; $i < sizeof($value); $i++) {
                if ($i > 0) {
                    $sql .= ", ";
                }
                $sql .= "?";
                $bindings[] = $value[$i];
            }
            $sql .= ")";
        }
        else {
            $sql .= "?";
            $bindings[] = $value;
        }
        return $sql;
    }
}