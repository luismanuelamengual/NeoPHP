<?php

namespace NeoPHP\sql;

use PDO;
use stdClass;

class ConnectionQuery
{
    private $connection;
    private $sql;
    
    public function __construct (Connection $database, $table=null)
    {
        $this->connection = $database;
        $this->sql = new stdClass();
        $this->setTable($table);
    }
    
    public function reset ()
    {
        $this->sql = new stdClass();
    }
    
    public function addField ($field, $fieldFormat=null, $fieldTable=null)
    {
        $this->addFields([$field], $fieldFormat, $fieldTable);
    }
    
    public function addFields (array $fields, $fieldsFormat=null, $fieldsTable=null)
    {
        if (empty($this->sql->fields))
            $this->sql->fields = [];
        foreach ($fields as $field)
        {    
            $fieldName = $field;
            if (!empty($fieldsFormat))
            {
                $fieldName .= " AS " . str_replace("%s", $field, $fieldsFormat);
            }
            if (!empty($fieldsTable))
            {
                $fieldName = $fieldsTable . "." . $fieldName;
            }
            $this->sql->fields[] = $fieldName;
        }
        return $this;
    }
    
    public function setTable ($table)
    {
        $this->sql->table = $table;
        return $this;
    }
    
    public function addJoin ($table, $sourceColumn, $destinationColumn=null, $joinType="JOIN")
    {
        if (empty($this->sql->joins))
            $this->sql->joins = [];
        $this->sql->joins[] = compact("table", "sourceColumn", "destinationColumn", "joinType");
        return $this;
    }
    
    public function addRawJoin ($table, $conditionExpression, array $conditionBindings=[], $joinType="JOIN")
    {
        if (empty($this->sql->joins))
            $this->sql->joins = [];
        $this->sql->joins[] = compact("table", "conditionExpression", "conditionBindings", "joinType");
        return $this;
    }
    
    public function addInnerJoin ($table, $sourceColumn, $destinationColumn=null)
    {
        return $this->addJoin($table, $sourceColumn, $destinationColumn, "INNER JOIN");
    }
    
    public function addLeftJoin ($table, $sourceColumn, $destinationColumn=null)
    {
        return $this->addJoin($table, $sourceColumn, $destinationColumn, "LEFT JOIN");
    }
    
    public function addRightJoin ($table, $sourceColumn, $destinationColumn=null)
    {
        return $this->addJoin($table, $sourceColumn, $destinationColumn, "RIGHT JOIN");
    }
        
    public function addWhere ($column, $operator, $value, $connector = "AND")
    {
        $conditionGroup = new ConnectionQueryConditionGroup();
        $conditionGroup->addCondition($column, $operator, $value);
        return $this->addWhereGroup($conditionGroup, $connector);
    }
    
    public function addRawWhere ($expression, array $bindings = [], $connector = "AND")
    {
        $conditionGroup = new ConnectionQueryConditionGroup();
        $conditionGroup->addRawCondition($expression, $bindings);
        return $this->addWhereGroup($conditionGroup, $connector);
    }
    
    public function addWhereGroup (ConnectionQueryConditionGroup $conditionGroup, $connector = "AND")
    {
        if (empty($this->sql->whereClause))
            $this->sql->whereClause = new ConnectionQueryConditionGroup();
        
        if ($connector != $this->sql->whereClause->getConnector() && !$this->sql->whereClause->isEmpty())
        {
            $oldConnector = $this->sql->whereClause->getConnector();
            $this->sql->whereClause->setConnector($connector);
            if (sizeof($this->sql->whereClause->getConditions()) > 1)
            {
                $oldConditions = $this->sql->whereClause->getConditions();
                $oldConditionsGroup = new ConnectionQueryConditionGroup($oldConnector);
                $oldConditionsGroup->setConditions($oldConditions);
                $this->sql->whereClause->clear();
                $this->sql->whereClause->addConditionGroup($oldConditionsGroup);
            }
        }

        if (sizeof($conditionGroup->getConditions()) > 1)
        {
            $this->sql->whereClause->addConditionGroup($conditionGroup);
        }
        else
        {
            $this->sql->whereClause->addConditions($conditionGroup->getConditions());
        }
        return $this;
    }
    
    public function addHaving ($column, $operator, $value, $connector = "AND")
    {
        $conditionGroup = new ConnectionQueryConditionGroup();
        $conditionGroup->addCondition($column, $operator, $value);
        return $this->addHavingGroup($conditionGroup, $connector);
    }
    
    public function addRawHaving ($expression, array $bindings = [], $connector = "AND")
    {
        $conditionGroup = new ConnectionQueryConditionGroup();
        $conditionGroup->addRawCondition($expression, $bindings);
        return $this->addHavingGroup($conditionGroup, $connector);
    }
        
    public function addHavingGroup (ConnectionQueryConditionGroup $conditionClause, $connector = "AND")
    {
        if (empty($this->sql->havingClause))
            $this->sql->havingClause = new ConnectionQueryConditionGroup();
        
        if ($connector != $this->sql->havingClause->getConnector() && !$this->sql->havingClause->isEmpty())
        {
            $oldConnector = $this->sql->havingClause->getConnector();
            $this->sql->havingClause->setConnector($connector);
            if (sizeof($this->sql->havingClause->getConditions()) > 1)
            {
                $oldConditions = $this->sql->havingClause->getConditions();
                $oldConditionsGroup = new ConnectionQueryConditionGroup($oldConnector);
                $oldConditionsGroup->setConditions($oldConditions);
                $this->sql->havingClause->clear();
                $this->sql->havingClause->addConditionGroup($oldConditionsGroup);
            }
        }

        if (sizeof($conditionGroup->getConditions()) > 1)
        {
            $this->sql->havingClause->addConditionGroup($conditionGroup);
        }
        else
        {
            $this->sql->havingClause->addConditions($conditionGroup->getConditions());
        }
        return $this;
    }
    
    public function addOrderBy ($fields, $direction="ASC")
    {
        if (empty($this->sql->orderByColumns))
            $this->sql->orderByColumns = [];
        if (!is_array($fields))
            $fields = [$fields];
        
        foreach ($fields as $field)
        {
            $fieldName = $field;
            $fieldDirection = $direction;
            if (is_array($field))
                list($fieldName, $fieldDirection) = $field;
            $this->sql->orderByColumns[] = $fieldName . " " . $fieldDirection;
        }
        return $this;
    }
    
    public function addGroupBy ($fields)
    {
        if (empty($this->sql->groupByColumns))
            $this->sql->groupByColumns = [];
        if (!is_array($fields))
            $fields = [$fields];
        
        foreach ($fields as $field)
        {
            $this->sql->groupByColumns[] = $field;
        }
        return $this;
    }
    
    public function setLimit ($limit)
    {
        $this->sql->limit = $limit;
        return $this;
    }
    
    public function setOffset ($offset)
    {
        $this->sql->offset = $offset;
        return $this;
    }
    
    public function insert ($values=null)
    {
        $keys = [];
        $bindings = [];
        $wildCards = [];
        foreach ($values as $field=>$fieldValue)
        {
            $keys[] = $field;
            $bindings[] = $fieldValue;
            $wildCards[] = "?";
        }        
        $sql = "INSERT INTO " . $this->sql->table . " (" . implode(",", $keys) . ") VALUES (" . implode(",", $wildCards) . ")";
        return $this->connection->exec($sql, $bindings);
    }
    
    public function update ($values=null)
    {
        $processedFields = [];
        $bindings = [];
        foreach ($values as $field=>$fieldValue)
        {
            $processedFields[] = $field . "=?";
            $bindings[] = $fieldValue;
        }
        $sql = "UPDATE " . $this->sql->table. " SET " . implode(",", $processedFields);
        if (!empty($this->sql->whereClause))
        {
            $sql .= " WHERE " . $this->getConditionGroupExpression($this->sql->whereClause, $bindings);
        }
        return $this->connection->exec($sql, $bindings);
    }
    
    public function delete ()
    {
        $bindings = [];
        $sql = "DELETE FROM " . $this->sql->table;
        if (!empty($this->sql->whereClause))
        {
            $sql .= " WHERE " . $this->getConditionGroupExpression($this->sql->whereClause, $bindings);
        }
        return $this->connection->exec($sql, $bindings);
    }
    
    public function get ($fetchType=PDO::FETCH_ASSOC)
    {
        $bindings = [];
        $sql = "";
        $sql .= "SELECT";
        $sql .= " " . (empty($this->sql->fields)? "*" : implode(", ", $this->sql->fields));
        $sql .= " FROM " . $this->sql->table;
        if (!empty($this->sql->joins))
        {
            foreach ($this->sql->joins as $join)
            {
                $sql .= " " . $join["joinType"] . " " . $join["table"];
                $sql .= " ON ";
                if (!empty($join["conditionExpression"]))
                {
                    $sql .= $join["conditionExpression"];
                    $bindings = array_merge($bindings, $join["conditionBindings"]);
                }
                else
                {
                    $sql .= $join["sourceColumn"] . " = " . $join["destinationColumn"];
                }
            }
        }
        if (!empty($this->sql->whereClause))
        {
            $sql .= " WHERE " . $this->getConditionGroupExpression($this->sql->whereClause, $bindings);
        }
        if (!empty($this->sql->havingClause))
        {
            $sql .= " HAVING " . $this->getConditionGroupExpression($this->sql->havingClause, $bindings);
        }
        if (!empty($this->sql->groupByColumns))
        {
            $sql .= " GROUP BY " . implode(",", $this->sql->groupByColumns);
        }
        if (!empty($this->sql->orderByColumns))
        {
            $sql .= " ORDER BY " . implode(",", $this->sql->orderByColumns);
        }
        if (!empty($this->sql->offset))
        {
            $sql .= " OFFSET " . $this->sql->offset;
        }
        if (!empty($this->sql->limit))
        {
            $sql .= " LIMIT " . $this->sql->limit;
        }
        
        $statement = $this->connection->query($sql, $bindings);
        return $statement->fetchAll($fetchType);;
    }
    
    public function getFirst ($fetchType=PDO::FETCH_ASSOC)
    {
        $this->setLimit(1);
        $results = $this->get($fetchType);
        return !empty($results)? reset($results) : null;
    }
    
    private function getConditionGroupExpression (ConnectionQueryConditionGroup $conditionGroup, array &$bindings)
    {
        $expression = "";
        foreach ($conditionGroup->getConditions() as $condition)
        {
            if (!empty($expression))
            {
                $expression .= " " . $conditionGroup->getConnector() . " ";
            }
            
            if ($condition instanceof ConnectionQueryConditionGroup)
            {
                $expression .= "(" . $this->getConditionGroupExpression($condition, $bindings) . ")";
            }
            else if (isset($condition["expression"]))
            {
                $expression .= $condition["expression"];
                $bindings = array_merge($bindings, $condition["bindings"]);
            }
            else
            {
                $expression .= $condition["operand1"] . " " . $condition["operator"] . " ?";
                $bindings[] = $condition["operand2"];
            }
        }
        return $expression;
    }
}