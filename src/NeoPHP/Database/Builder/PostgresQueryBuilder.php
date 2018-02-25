<?php

namespace NeoPHP\Database\Builder;

use DateTimeInterface;
use NeoPHP\Database\Query\ConditionGroup;
use NeoPHP\Database\Query\DeleteQuery;
use NeoPHP\Database\Query\InsertQuery;
use NeoPHP\Database\Query\Join;
use NeoPHP\Database\Query\Query;
use NeoPHP\Database\Query\SelectQuery;
use NeoPHP\Database\Query\UpdateQuery;

class PostgresQueryBuilder extends QueryBuilder {

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

    protected function buildSelectSql(SelectQuery $query, array &$bindings) {
        $sql = "SELECT";
        if ($query->distinct()) {
            $sql .= " DISTINCT";
        }
        $sql .= " ";
        $selectFields = $query->selectFields();
        if (empty($selectFields)) {
            $sql .= "*";
        }
        else {
            for ($i = 0; $i < sizeof($selectFields); $i++) {
                if ($i > 0) {
                    $sql .= ", ";
                }
                $sql .= $selectFields[$i];
            }
        }
        $sql .= " FROM ";
        $sql .= $query->table();
        $joins = $query->joins();
        if (!empty($joins)) {
            foreach ($joins as $join) {
                $sql .= " " . $this->buildJoinSql($join, $bindings);
            }
        }
        if ($query->hasWhereConditions()) {
            $sql .= " WHERE ";
            $sql .= $this->buildConditionGroupSql($query->whereConditions(), $bindings);
        }
        $groupByFields = $query->groupByFields();
        if (!empty($groupByFields)) {
            $sql .= " GROUP BY ";
            for ($i = 0; $i < sizeof($groupByFields); $i++) {
                if ($i > 0) {
                    $sql .= ", ";
                }
                $sql .= $groupByFields[$i];
            }
        }
        $orderByFields = $query->orderByFields();
        if (!empty($orderByFields)) {
            $sql .= " ORDER BY ";
            for ($i = 0; $i < sizeof($orderByFields); $i++) {
                if ($i > 0) {
                    $sql .= ", ";
                }
                $orderByField = $orderByFields[$i];
                $sql .= $orderByField["column"];
                if (isset($orderByField["direction"])) {
                    $sql .= " " . strtoupper($orderByField["direction"]);
                }
            }
        }
        if ($query->hasHavingConditions()) {
            $sql .= " HAVING ";
            $sql .= $this->buildConditionGroupSql($query->havingConditions(), $bindings);
        }
        if ($query->offset() != null) {
            $sql .= " OFFSET " . $query->offset();
        }
        if ($query->limit() != null) {
            $sql .= " LIMIT " . $query->limit();
        }
        return $sql;
    }

    protected function buildInsertSql(InsertQuery $query, array &$bindings) {
        $sql = "INSERT INTO ";
        $sql .= $query->table();
        $fieldsSql = "";
        $valuesSql = "";
        $i = 0;
        foreach ($query->fields() as $field => $value) {
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

    protected function buildUpdateSql(UpdateQuery $query, array &$bindings) {
        $sql = "UPDATE ";
        $sql .= $query->table();
        $sql .= " SET ";
        $i = 0;
        foreach ($query->fields() as $field => $value) {
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
            $sql .= $this->buildConditionGroupSql($query->whereConditions(), $bindings);
        }
        else if (get_property("database.missingWhereClauseProtection", true)) {
            throw new \RuntimeException("Missing where clause in update sql. If intentional check \"database.missingWhereClauseProtection\" property");
        }
        return $sql;
    }

    protected function buildDeleteSql(DeleteQuery $query, array &$bindings) {
        $sql = "DELETE FROM ";
        $sql .= $query->table();
        if (!$query->hasWhereConditions() && get_property("database.missingWhereClauseProtection", true)) {
            throw new \RuntimeException("Missing where clause in delete sql. If intentional check \"database.missingWhereClauseProtection\" property");
        }
        if ($query->hasWhereConditions()) {
            $sql .= " WHERE ";
            $sql .= $this->buildConditionGroupSql($query->whereConditions(), $bindings);
        }
        else if (get_property("database.missingWhereClauseProtection", true)) {
            throw new \RuntimeException("Missing where clause in delete sql. If intentional check \"database.missingWhereClauseProtection\" property");
        }
        return $sql;
    }

    protected function buildJoinSql(Join $join, array &$bindings) {
        $sql = "";
        switch ($join->type()) {
            case Join::TYPE_JOIN:
                $sql .= "JOIN";
                break;
            case Join::TYPE_INNER_JOIN:
                $sql .= "INNER JOIN";
                break;
            case Join::TYPE_OUTER_JOIN:
                $sql .= "OUTER JOIN";
                break;
            case Join::TYPE_LEFT_JOIN:
                $sql .= "LEFT JOIN";
                break;
            case Join::TYPE_RIGHT_JOIN:
                $sql .= "RIGHT JOIN";
                break;
        }
        $sql .= " " . $join->table();
        if (!empty($join->conditions())) {
            $sql .= " ON " . $this->buildConditionGroupSql($join, $bindings);
        }
        return $sql;
    }

    protected function buildConditionGroupSql(ConditionGroup $conditionGroup, array &$bindings) {
        $sql = "";
        $conditions = $conditionGroup->conditions();
        $connector = $conditionGroup->connector();
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

    protected function buildConditionSql($condition, array &$bindings) {
        $sql = "";
        switch ($condition["type"]) {
            case "basic":
                $operator = isset($condition["operator"]) ? $condition["operator"] : "=";
                $operator = strtoupper($operator);
                $sql .= $condition["column"];
                $sql .= " $operator";
                if (isset($condition["value"])) {
                    $sql .= " " . $this->buildValueSql($condition["value"], $bindings);
                }
                break;
            case "group":
                $sql .= "(" . $this->buildConditionGroupSql($condition, $bindings) . ")";
                break;
            case "raw":
                $sql .= $condition["sql"];
                $bindings = array_merge($bindings, $condition["bindings"]);
                break;
            case "column":
                $operator = isset($condition["operator"]) ? $condition["operator"] : "=";
                $sql .= $condition["column"];
                $sql .= " $operator ";
                $sql .= $condition["otherColumn"];
                break;
            case "null":
                $sql .= $condition["column"];
                $sql .= " IS NULL";
                break;
            case "notNull":
                $sql .= $condition["column"];
                $sql .= " IS NOT NULL";
                break;
        }
        return $sql;
    }

    protected function buildValueSql($value, array &$bindings) {
        $sql = "";
        if (is_object($value)) {
            if ($value instanceof Query) {
                $sql .= "(" . $this->buildSql($value, $bindings) . ")";
            }
            else if ($value instanceof DateTimeInterface) {
                $sql .= "?";
                $bindings[] = date("Y-m-d H:i:s", $value->getTimestamp());
            }
        }
        else if (is_array($value)) {
            $sql .= "(";
            for ($i = 0; $i < sizeof($value); $i++) {
                if ($i > 0) {
                    $sql .= ", ";
                }
                $sql .= $this->buildValueSql($value[$i], $bindings);
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