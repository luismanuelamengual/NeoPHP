<?php

namespace NeoPHP\Database;

use PDO;
use stdClass;

class Query {

    private $table;
    private $selectFields = [];
    private $whereCondition = null;
    private $havingCondition = null;
    private $orderByFields = [];
    private $groupByFields = [];
    private $joins = [];
    private $limit = null;
    private $offset = null;

    public function __construct() {
    }

    public function addFields(array $fields, $fieldsFormat = null, $fieldsTable = null) {
        foreach ($fields as $field) {
            $fieldName = $field;
            if (!empty($fieldsFormat)) {
                $fieldName .= " AS " . str_replace("%s", $field, $fieldsFormat);
            }
            if (!empty($fieldsTable)) {
                $fieldName = $fieldsTable . "." . $fieldName;
            }
            $this->sql->fields[] = $fieldName;
        }
        return $this;
    }

    public function addField($field, $alias = null, $table = null) {
        $this->fields[] = $field;
    }

    public function setTable($table) {
        $this->sql->table = $table;
        return $this;
    }

    public function addJoin($table, $sourceColumn, $destinationColumn = null, $joinType = "JOIN") {
        if (empty($this->sql->joins))
            $this->sql->joins = [];
        $this->sql->joins[] = compact("table", "sourceColumn", "destinationColumn", "joinType");
        return $this;
    }

    public function addRawJoin($table, $conditionExpression, array $conditionBindings = [], $joinType = "JOIN") {
        if (empty($this->sql->joins))
            $this->sql->joins = [];
        $this->sql->joins[] = compact("table", "conditionExpression", "conditionBindings", "joinType");
        return $this;
    }

    public function addInnerJoin($table, $sourceColumn, $destinationColumn = null) {
        return $this->addJoin($table, $sourceColumn, $destinationColumn, "INNER JOIN");
    }

    public function addLeftJoin($table, $sourceColumn, $destinationColumn = null) {
        return $this->addJoin($table, $sourceColumn, $destinationColumn, "LEFT JOIN");
    }

    public function addRightJoin($table, $sourceColumn, $destinationColumn = null) {
        return $this->addJoin($table, $sourceColumn, $destinationColumn, "RIGHT JOIN");
    }

    public function getWhereClause() {
        if (empty($this->sql->whereClause))
            $this->sql->whereClause = new ConnectionQueryFilterGroup();
        return $this->sql->whereClause;
    }

    public function setWhereClause(ConnectionQueryFilter $filter) {
        if ($filter instanceof ConnectionQueryFilterGroup) {
            $this->sql->whereClause = $filter;
        }
        else {
            $whereClause = new ConnectionQueryFilterGroup();
            $whereClause->addFilter($filter);
            $this->sql->whereClause = $whereClause;
        }
    }

    public function addWhere($column, $operator, $value = null) {
        return $this->addWhereFilter(new ConnectionQueryColumnFilter($column, $operator, $value));
    }

    public function addRawWhere($expression, array $bindings = []) {
        return $this->addWhereFilter(new ConnectionQueryRawFilter($expression, $bindings));
    }

    public function addWhereFilter(ConnectionQueryFilter $filter) {
        $this->getWhereClause()->addFilter($filter);
        return $this;
    }

    public function getHavingClause() {
        if (empty($this->sql->havingClause))
            $this->sql->havingClause = new ConnectionQueryFilterGroup();
        return $this->sql->havingClause;
    }

    public function setHavingClause(ConnectionQueryFilter $filter) {
        if ($filter instanceof ConnectionQueryFilterGroup) {
            $this->sql->havingClause = $filter;
        }
        else {
            $havingClause = new ConnectionQueryFilterGroup();
            $havingClause->addFilter($filter);
            $this->sql->havingClause = $havingClause;
        }
    }

    public function addHaving($column, $operator, $value) {
        return $this->addHavingFilter(new ConnectionQueryColumnFilter($column, $operator, $value));
    }

    public function addRawHaving($expression, array $bindings = []) {
        return $this->addHavingFilter(new ConnectionQueryRawFilter($expression, $bindings));
    }

    public function addHavingFilter(ConnectionQueryFilter $filter) {
        $this->getHavingClause()->addFilter($filter);
        return $this;
    }

    public function addOrderBy($fields, $direction = "ASC") {
        if (empty($this->sql->orderByColumns))
            $this->sql->orderByColumns = [];
        if (!is_array($fields))
            $fields = [$fields];

        foreach ($fields as $field) {
            $fieldName = $field;
            $fieldDirection = $direction;
            if (is_array($field))
                list($fieldName, $fieldDirection) = $field;
            $this->sql->orderByColumns[] = $fieldName . " " . $fieldDirection;
        }
        return $this;
    }

    public function addGroupBy($fields) {
        if (empty($this->sql->groupByColumns))
            $this->sql->groupByColumns = [];
        if (!is_array($fields))
            $fields = [$fields];

        foreach ($fields as $field) {
            $this->sql->groupByColumns[] = $field;
        }
        return $this;
    }

    public function setLimit($limit) {
        $this->sql->limit = $limit;
        return $this;
    }

    public function setOffset($offset) {
        $this->sql->offset = $offset;
        return $this;
    }

    public function insert($values = null) {
        $keys = [];
        $bindingSlots = [];
        $bindings = [];
        foreach ($values as $field => $fieldValue) {
            $keys[] = $field;
            $bindingName = $this->getBindingName($bindings);
            $bindings[$bindingName] = $fieldValue;
            $bindingSlots[] = ":" . $bindingName;
        }
        $sql = "INSERT INTO " . $this->sql->table . " (" . implode(",", $keys) . ") VALUES (" . implode(",", $bindingSlots) . ")";
        return $this->connection->exec($sql, $bindings);
    }

    public function update($values = null) {
        $processedFields = [];
        $bindings = [];
        foreach ($values as $field => $fieldValue) {
            $bindingName = $this->getBindingName($bindings);
            $processedFields[] = $field . " = :" . $bindingName;
            $bindings[$bindingName] = $fieldValue;
        }
        $sql = "UPDATE " . $this->sql->table . " SET " . implode(",", $processedFields);
        if (!empty($this->sql->whereClause)) {
            $sql .= " WHERE " . $this->getFilterExpression($this->sql->whereClause, $bindings);
        }
        return $this->connection->exec($sql, $bindings);
    }

    public function delete() {
        $bindings = [];
        $sql = "DELETE FROM " . $this->sql->table;
        if (!empty($this->sql->whereClause)) {
            $sql .= " WHERE " . $this->getFilterExpression($this->sql->whereClause, $bindings);
        }
        return $this->connection->exec($sql, $bindings);
    }

    public function get($fetchType = PDO::FETCH_ASSOC, callable $fetchCallback = null) {
        $bindings = [];
        $sql = "";
        $sql .= "SELECT";
        $sql .= " " . (empty($this->sql->fields) ? "*" : implode(", ", $this->sql->fields));
        $sql .= " FROM " . $this->sql->table;
        if (!empty($this->sql->joins)) {
            foreach ($this->sql->joins as $join) {
                $sql .= " " . $join["joinType"] . " " . $join["table"];
                $sql .= " ON ";
                if (!empty($join["conditionExpression"])) {
                    $sql .= $join["conditionExpression"];
                    $bindings = array_merge($bindings, $join["conditionBindings"]);
                }
                else {
                    $sql .= $join["sourceColumn"] . " = " . $join["destinationColumn"];
                }
            }
        }
        if (!empty($this->sql->whereClause)) {
            $sql .= " WHERE " . $this->getFilterExpression($this->sql->whereClause, $bindings);
        }
        if (!empty($this->sql->havingClause)) {
            $sql .= " HAVING " . $this->getFilterExpression($this->sql->havingClause, $bindings);
        }
        if (!empty($this->sql->groupByColumns)) {
            $sql .= " GROUP BY " . implode(",", $this->sql->groupByColumns);
        }
        if (!empty($this->sql->orderByColumns)) {
            $sql .= " ORDER BY " . implode(",", $this->sql->orderByColumns);
        }
        if (!empty($this->sql->offset)) {
            $sql .= " OFFSET " . $this->sql->offset;
        }
        if (!empty($this->sql->limit)) {
            $sql .= " LIMIT " . $this->sql->limit;
        }

        $statement = $this->connection->query($sql, $bindings);

        $results = null;
        if ($fetchCallback == null) {
            $results = $statement->fetchAll($fetchType);
        }
        else {
            $result = null;
            while ($result = $statement->fetch($fetchType)) {
                call_user_func_array($fetchCallback, [$result]);
            }
        }
        return $results;
    }

    public function getFirst($fetchType = PDO::FETCH_ASSOC, callable $fetchCallback = null) {
        $this->setLimit(1);
        $results = $this->get($fetchType, $fetchCallback);
        return !empty($results) ? reset($results) : null;
    }

    private function getBindingName(array $bindings = []) {
        return "param" . (sizeof($bindings) + 1);
    }

    private function getFilterExpression(ConnectionQueryFilter $filter, array &$bindings = []) {
        $expression = "";
        if ($filter instanceof ConnectionQueryColumnFilter) {
            $bindingName = $this->getBindingName($bindings);
            $expression .= $filter->getProperty();
            $expression .= " ";
            $expression .= $filter->getOperator();
            $expression .= " ";
            $expression .= " :" . $bindingName;
            $bindings[$bindingName] = $filter->getValue();
        }
        else if ($filter instanceof ConnectionQueryRawFilter) {
            $expression = $filter->getFilter();
            $bindings = array_merge($bindings, $filter->getBindings());
        }
        else if ($filter instanceof ConnectionQueryFilterGroup) {
            $childFilters = $filter->getFilters();
            $expressionTokens = [];
            foreach ($childFilters as $childFilter) {
                $expressionTokens[] = $this->getFilterExpression($childFilter, $bindings);
            }
            $expression .= "(";
            $expression .= implode(" " . $filter->getConnector() . " ", $expressionTokens);
            $expression .= ")";
        }
        return $expression;
    }
}