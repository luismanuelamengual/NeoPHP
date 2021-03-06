<?php

namespace NeoPHP\Database\Builder;

use DateTimeInterface;
use NeoPHP\Query\ConditionOperator;
use NeoPHP\Query\ConditionType;
use NeoPHP\Query\ConditionGroup;
use NeoPHP\Query\DeleteQuery;
use NeoPHP\Query\InsertQuery;
use NeoPHP\Query\Join;
use NeoPHP\Query\Query;
use NeoPHP\Query\SelectQuery;
use NeoPHP\Query\UnionQuery;
use NeoPHP\Query\UpdateQuery;
use stdClass;

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
        else if ($query instanceof UnionQuery) {
            $sql = $this->buildUnionSql($query, $bindings);
        }
        return $sql;
    }

    protected function buildUnionSql(UnionQuery $query, array &$bindings) {
        $join = '';
        $sql = '(';
        foreach ($query->getQueries() as $query) {
            $sql .= $join;
            $sql .= $this->buildSelectSql($query, $bindings);
            $join = ') UNION (';
        }
        $sql .= ')';

        $orderByFields = $query->getOrderByFields();
        if (!empty($orderByFields)) {
            $sql .= " ORDER BY ";
            for ($i = 0; $i < sizeof($orderByFields); $i++) {
                if ($i > 0) {
                    $sql .= ", ";
                }
                $orderByField = $orderByFields[$i];
                $sql .= $orderByField["field"];
                if (isset($orderByField["direction"])) {
                    $sql .= " " . strtoupper($orderByField["direction"]);
                }
            }
        }

        if ($query->getOffset() != null) {
            $sql .= " OFFSET " . $query->getOffset();
        }
        if ($query->getLimit() != null) {
            $sql .= " LIMIT " . $query->getLimit();
        }
        return $sql;
    }

    protected function buildSelectSql(SelectQuery $query, array &$bindings) {
        $sql = "SELECT";
        if ($query->getDistinct()) {
            $sql .= " DISTINCT";
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
                if ($selectField instanceof stdClass) {
                    $sql .= $selectField->expression . " AS ";
                    if (ctype_lower($selectField->alias)) {
                        $sql .= $selectField->alias;
                    } else {
                        $sql .= '"' . $selectField->alias . '"';
                    }
                }
                else {
                    $sql .= $selectField;
                }
            }
        }
        $sql .= " FROM ";
        $sql .= $this->buildSelectSource($query->getTable(),$bindings);
        $tableAlias = $query->getAlias();
        if (!empty($tableAlias)) {
            $sql .= " AS " . $tableAlias;
        }
        $joins = $query->getJoins();
        if (!empty($joins)) {
            foreach ($joins as $join) {
                $sql .= " " . $this->buildJoinSql($join,$bindings);
            }
        }
        if ($query->hasWhereConditions()) {
            $sql .= " WHERE ";
            $sql .= $this->buildConditionGroupSql($query->getWhereConditionGroup(),$bindings);
        }
        $groupByFields = $query->getGroupByFields();
        if (!empty($groupByFields)) {
            $sql .= " GROUP BY ";
            for ($i = 0; $i < sizeof($groupByFields); $i++) {
                if ($i > 0) {
                    $sql .= ", ";
                }
                $sql .= $groupByFields[$i];
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
                $sql .= $orderByField->field;
                if (isset($orderByField->direction)) {
                    $sql .= " " . strtoupper($orderByField->direction);
                }
            }
        }
        if ($query->hasHavingConditions()) {
            $sql .= " HAVING ";
            $sql .= $this->buildConditionGroupSql($query->getHavingConditionGroup(), $bindings);
        }
        if ($query->getOffset() != null) {
            $sql .= " OFFSET " . $query->getOffset();
        }
        if ($query->getLimit() != null) {
            $sql .= " LIMIT " . $query->getLimit();
        }
        if ($query->isForUpdate()) {
            $sql .= " FOR UPDATE";
        }
        if ($query->isForShare()) {
            $sql .= " FOR SHARE";
        }
        return $sql;
    }

    protected function buildInsertSql(InsertQuery $query, array &$bindings) {
        $sql = "INSERT INTO ";
        $sql .= $query->getTable();
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

    protected function buildUpdateSql(UpdateQuery $query, array &$bindings) {
        $sql = "UPDATE ";
        $sql .= $query->getTable();
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
            $sql .= $this->buildConditionGroupSql($query->getWhereConditionGroup(), $bindings);
        }
        else if (get_property("database.missingWhereClauseProtection", true)) {
            throw new \RuntimeException("Missing where clause in update sql. If intentional check \"database.missingWhereClauseProtection\" property");
        }
        return $sql;
    }

    protected function buildDeleteSql(DeleteQuery $query, array &$bindings) {
        $sql = "DELETE FROM ";
        $sql .= $query->getTable();
        if (!$query->hasWhereConditions() && get_property("database.missingWhereClauseProtection", true)) {
            throw new \RuntimeException("Missing where clause in delete sql. If intentional check \"database.missingWhereClauseProtection\" property");
        }
        if ($query->hasWhereConditions()) {
            $sql .= " WHERE ";
            $sql .= $this->buildConditionGroupSql($query->getWhereConditionGroup(), $bindings);
        }
        else if (get_property("database.missingWhereClauseProtection", true)) {
            throw new \RuntimeException("Missing where clause in delete sql. If intentional check \"database.missingWhereClauseProtection\" property");
        }
        return $sql;
    }

    protected function buildSelectSource ($source, array &$bindings) {
        $sql = "";
        if ($source instanceof SelectQuery) {
            $sql .= "(" . $this->buildSelectSql($source, $bindings) . ")";
        }
        else {
            $sql .= $source;
        }
        return $sql;
    }

    protected function buildJoinSql(Join $join, array &$bindings) {
        $sql = "";
        $sql .= strtoupper($join->getType());
        $sql .= " " . $join->getTable();
        if (!empty($join->getConditions())) {
            $sql .= " ON " . $this->buildConditionGroupSql($join, $bindings);
        }
        return $sql;
    }

    protected function buildConditionGroupSql(ConditionGroup $conditionGroup, array &$bindings) {
        $sql = "";
        $conditions = $conditionGroup->getConditions();
        $connector = strtoupper($conditionGroup->getConnector());
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
        switch ($condition->type) {
            case ConditionType::BASIC:
                $operator = isset($condition->operator) ? $condition->operator : ConditionOperator::EQUALS;
                $sql .= $condition->field;
                switch ($condition->operator) {
                    case ConditionOperator::EQUALS:
                        $sql .= " = " . $this->buildValueSql($condition->value, $bindings);
                        break;
                    case ConditionOperator::EQUALS_FIELD:
                        $sql .= " = " . $condition->value;
                        break;
                    case ConditionOperator::DISTINCT:
                        $sql .= " != " . $this->buildValueSql($condition->value, $bindings);
                        break;
                    case ConditionOperator::GREATER_THAN:
                        $sql .= " > " . $this->buildValueSql($condition->value, $bindings);
                        break;
                    case ConditionOperator::GREATER_OR_EQUALS_THAN:
                        $sql .= " >= " . $this->buildValueSql($condition->value, $bindings);
                        break;
                    case ConditionOperator::LESS_THAN:
                        $sql .= " < " . $this->buildValueSql($condition->value, $bindings);
                        break;
                    case ConditionOperator::LESS_OR_EQUALS_THAN:
                        $sql .= " <= " . $this->buildValueSql($condition->value, $bindings);
                        break;
                    case ConditionOperator::IN:
                        $sql .= " IN " . $this->buildValueSql($condition->value, $bindings);
                        break;
                    case ConditionOperator::NOT_IN:
                        $sql .= " NOT IN " . $this->buildValueSql($condition->value, $bindings);
                        break;
                    case ConditionOperator::NULL:
                        $sql .= " IS NULL";
                        break;
                    case ConditionOperator::NOT_NULL:
                        $sql .= " IS NOT NULL";
                        break;
                    case ConditionOperator::LIKE:
                        $sql .= (!empty($condition->caseSensitive)? " LIKE " : " ILIKE ") . $this->buildValueSql("%" . $condition->value . "%", $bindings);
                        break;
                    case ConditionOperator::NOT_LIKE:
                        $sql .= " NOT" . (!empty($condition->caseSensitive)? " LIKE " : " ILIKE ") . $this->buildValueSql("%" . $condition->value . "%", $bindings);
                        break;
                    case ConditionOperator::CONTAINS:
                        $sql .= " @> " . $this->buildValueSql("{" . (is_array($condition->value)?implode(",",$condition->value) : $condition->value) . "}", $bindings);
                        break;
                    case ConditionOperator::NOT_CONTAINS:
                        $sql .= " NOT @> " . $this->buildValueSql("{" . (is_array($condition->value)?implode(",",$condition->value) : $condition->value) . "}", $bindings);
                        break;
                    default:
                        $sql .= " " . strtoupper($operator) . " " . $this->buildValueSql($condition->value, $bindings);
                        break;
                }
                break;
            case ConditionType::GROUP:
                $sql .= "(" . $this->buildConditionGroupSql($condition->group, $bindings) . ")";
                break;
            case ConditionType::RAW:
                $sql .= $condition->sql;
                $bindings = array_merge($bindings, $condition->bindings);
                break;
        }
        return $sql;
    }

    protected function buildValueSql($value, array &$bindings) {
        $sql = "";

        if ($value === null) {
            $sql = "NULL";
        }
        else if (is_object($value)) {
            if ($value instanceof Query) {
                $sql .= "(" . $this->buildSql($value, $bindings) . ")";
            }
            else if ($value instanceof DateTimeInterface) {
                $sql .= "?";
                $bindings[] = $value->format("Y-m-d H:i:s");
            }
        }
        else if (is_array($value)) {
            $sql .= "(";
            $valuesArray = array_values($value);
            for ($i = 0; $i < sizeof($valuesArray); $i++) {
                if ($i > 0) {
                    $sql .= ", ";
                }
                $sql .= $this->buildValueSql($valuesArray[$i], $bindings);
            }
            $sql .= ")";
        }
        else if (is_bool($value)) {
            $sql .= ($value)? "true" : "false";
        }
        else {
            $sql .= "?";
            $bindings[] = $value;
        }
        return $sql;
    }
}