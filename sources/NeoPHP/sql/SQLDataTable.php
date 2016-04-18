<?php

namespace NeoPHP\sql;

use NeoPHP\core\reflect\ReflectionAnnotatedClass;
use PDO;
use stdClass;

class SQLDataTable
{
    private $connection;
    private $sql;
    
    public function __construct (Connection $connection, $table)
    {
        $this->connection = $connection;
        $this->sql = new stdClass();
        $this->setTable($table);
    }
    
    public function reset ()
    {
        $this->sql = new stdClass();
    }
    
    public function setTable ($table)
    {
        $this->sql->table = $table;
        return $this;
    }
    
    public function addField ($field)
    {
        if (empty($this->sql->fields))
            $this->sql->fields = [];
        $this->sql->fields[] = $field;
        return $this;
    }
    
    public function addFields (array $fields, $fieldsFormat=null, $fieldsTable=null)
    {
        foreach ($fields as $column)
        {    
            $columnName = $column;
            if (!empty($fieldsFormat))
            {
                $columnName .= " " . SQL::KEYWORD_AS . " " . str_replace("%s", $column, $fieldsFormat);
            }
            if (!empty($fieldsTable))
            {
                $columnName = $fieldsTable . "." . $columnName;
            }
            $this->addField($columnName);
        }
        return $this;
    }
    
    public function setFields (array $fields = array())
    {
        $this->sql->fields = $fields;
        return $this;
    }
    
    public function addRawJoin ($table, $conditionExpression, array $conditionBindings=[], $joinType=SQL::KEYWORD_JOIN)
    {
        if (empty($this->sql->joins))
            $this->sql->joins = [];
        $this->sql->joins[] = compact("table", "conditionExpression", "conditionBindings", "joinType");
        return $this;
    }
    
    public function addJoin ($table, $sourceColumn, $destinationColumn=null, $joinType=SQL::KEYWORD_JOIN)
    {
        if (empty($this->sql->joins))
            $this->sql->joins = [];
        $this->sql->joins[] = compact("table", "sourceColumn", "destinationColumn", "joinType");
        return $this;
    }
    
    public function addInnerJoin ($table, $sourceColumn, $destinationColumn=null)
    {
        return $this->addJoin($table, $sourceColumn, $destinationColumn, SQL::KEYWORD_INNER_JOIN);
    }
    
    public function addLeftJoin ($table, $sourceColumn, $destinationColumn=null)
    {
        return $this->addJoin($table, $sourceColumn, $destinationColumn, SQL::KEYWORD_LEFT_JOIN);
    }
    
    public function addRightJoin ($table, $sourceColumn, $destinationColumn=null)
    {
        return $this->addJoin($table, $sourceColumn, $destinationColumn, SQL::KEYWORD_RIGHT_JOIN);
    }
        
    public function addWhere ($column, $operator, $value, $connector = SQL::OPERATOR_AND)
    {
        $conditionGroup = new SQLConditionGroup();
        $conditionGroup->addCondition($column, $operator, $value);
        $this->addWhereGroup($conditionGroup, $connector);
        return $this;
    }
    
    public function addRawWhere ($expression, array $bindings = [], $connector = SQL::OPERATOR_AND)
    {
        $conditionGroup = new SQLConditionGroup();
        $conditionGroup->addRawCondition($expression, $bindings);
        $this->addWhereGroup($conditionGroup, $connector);
        return $this;
    }
    
    public function addWhereGroup (SQLConditionGroup $conditionGroup, $connector = SQL::OPERATOR_AND)
    {
        if (empty($this->sql->whereClause))
            $this->sql->whereClause = new SQLConditionGroup();
        
        if ($connector != $this->sql->whereClause->getConnector() && !$this->sql->whereClause->isEmpty())
        {
            $oldConnector = $this->sql->whereClause->getConnector();
            $this->sql->whereClause->setConnector($connector);
            if (sizeof($this->sql->whereClause->getConditions()) > 1)
            {
                $oldConditions = $this->sql->whereClause->getConditions();
                $oldConditionsGroup = new SQLConditionGroup($oldConnector);
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
    
    public function addHaving ($column, $operator, $value, $connector = SQL::OPERATOR_AND)
    {
        $conditionGroup = new SQLConditionGroup();
        $conditionGroup->addCondition($column, $operator, $value);
        $this->addHavingGroup($conditionGroup, $connector);
        return $this;
    }
    
    public function addRawHaving ($expression, array $bindings = [], $connector = SQL::OPERATOR_AND)
    {
        $conditionGroup = new SQLConditionGroup();
        $conditionGroup->addRawCondition($expression, $bindings);
        $this->addHavingGroup($conditionGroup, $connector);
        return $this;
    }
    
    public function addHavingGroup (SQLConditionGroup $conditionClause, $connector = SQL::OPERATOR_AND)
    {
        if (empty($this->sql->havingClause))
            $this->sql->havingClause = new SQLConditionGroup();
        
        if ($connector != $this->sql->havingClause->getConnector() && !$this->sql->havingClause->isEmpty())
        {
            $oldConnector = $this->sql->havingClause->getConnector();
            $this->sql->havingClause->setConnector($connector);
            if (sizeof($this->sql->havingClause->getConditions()) > 1)
            {
                $oldConditions = $this->sql->havingClause->getConditions();
                $oldConditionsGroup = new SQLConditionGroup($oldConnector);
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
    
    public function setOrderBy ($orderBy)
    {
        $this->sql->orderByColumns = [$orderBy];
        return $this;
    }
    
    public function addOrderBy ($column, $direction=SQL::KEYWORD_DESC)
    {
        if (empty($this->sql->orderByColumns))
            $this->sql->orderByColumns = [];
        $this->sql->orderByColumns[] = $column . " " . $direction;
        return $this;
    }
    
    public function setGroupBy ($groupBy)
    {
        $this->sql->groupByColumns = [$groupBy];
        return $this;
    }
    
    public function addGroupBy ($column)
    {
        if (empty($this->sql->groupByColumns))
            $this->sql->groupByColumns = [];
        $this->sql->groupByColumns[] = $column;
        return $this;
    }
    
    public function setLimit ($limit)
    {
        $this->sql->limit = $limit;
    }
    
    public function setOffset ($offset)
    {
        $this->sql->offset = $offset;
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
            $wildCards[] = SQL::WILDCARD;
        }        
        $sql = SQL::KEYWORD_INSERT_INTO . " " . $this->sql->table . " (" . implode(",", $keys) . ") " . SQL::KEYWORD_VALUES . " (" . implode(",", $wildCards) . ")";
        return $this->connection->exec($sql, $bindings);
    }
    
    public function update ($values=null)
    {
        $processedFields = [];
        $bindings = [];
        foreach ($values as $field=>$fieldValue)
        {
            $processedFields[] = $field . "=" . SQL::WILDCARD;
            $bindings[] = $fieldValue;
        }
        $sql = SQL::KEYWORD_UPDATE . " " . $this->sql->table. " " . SQL::KEYWORD_SET . " " . implode(",", $processedFields);
        if (!empty($this->sql->whereClause))
        {
            $sql .= " " . SQL::KEYWORD_WHERE . " " . $this->getConditionGroupExpression($this->sql->whereClause, $bindings);
        }
        return $this->connection->exec($sql, $bindings);
    }
    
    public function delete ()
    {
        $bindings = [];
        $sql = SQL::KEYWORD_DELETE_FROM . " " . $this->sql->table;
        if (!empty($this->sql->whereClause))
        {
            $sql .= " " . SQL::KEYWORD_WHERE . " " . $this->getConditionGroupExpression($this->sql->whereClause, $bindings);
        }
        return $this->connection->exec($sql, $bindings);
    }
    
    public function get (ReflectionAnnotatedClass $entityClass = null)
    {
        $bindings = [];
        $sql = "";
        $sql .= SQL::KEYWORD_SELECT;
        $sql .= " " . (empty($this->sql->fields)? SQL::ALL_COLUMNS : implode(", ", $this->sql->fields));
        $sql .= " " . SQL::KEYWORD_FROM . " " . $this->sql->table;
        if (!empty($this->sql->joins))
        {
            foreach ($this->sql->joins as $join)
            {
                $sql .= " " . $join["joinType"] . " " . $join["table"];
                $sql .= " " . SQL::KEYWORD_ON . " ";
                if (!empty($join["conditionExpression"]))
                {
                    $sql .= $join["conditionExpression"];
                    $bindings = array_merge($bindings, $join["conditionBindings"]);
                }
                else
                {
                    $sql .= $join["sourceColumn"] . " " . SQL::OPERATOR_EQUAL . " " . $join["destinationColumn"];
                }
            }
        }
        if (!empty($this->sql->whereClause))
        {
            $sql .= " " . SQL::KEYWORD_WHERE . " " . $this->getConditionGroupExpression($this->sql->whereClause, $bindings);
        }
        if (!empty($this->sql->havingClause))
        {
            $sql .= " " . SQL::KEYWORD_HAVING . " " . $this->getConditionGroupExpression($this->sql->havingClause, $bindings);
        }
        if (!empty($this->sql->groupByColumns))
        {
            $sql .= " " . SQL::KEYWORD_GROUP_BY . " " . implode(",", $this->sql->groupByColumns);
        }
        if (!empty($this->sql->orderByColumns))
        {
            $sql .= " " . SQL::KEYWORD_ORDER_BY . " " . implode(",", $this->sql->orderByColumns);
        }
        if (!empty($this->sql->offset))
        {
            $sql .= " " . SQL::KEYWORD_OFFSET . " " . $this->sql->offset;
        }
        if (!empty($this->sql->limit))
        {
            $sql .= " " . SQL::KEYWORD_LIMIT . " " . $this->sql->limit;
        }
        
        $statement = $this->connection->query($sql, $bindings);
        $results = [];
        $result = null;
        do
        {
            $result = $statement->fetch(PDO::FETCH_ASSOC);
            if ($result != false)
                $results[] = ($entityClass != null)? $this->connection->getEntityManager()->create($entityClass, $result) : $result;
        }
        while (!empty($result));
        return $results;
    }
    
    public function getFirst (ReflectionAnnotatedClass $entityClass = null)
    {
        $this->setLimit(1);
        $results = $this->get($entityClass);
        return !empty($results)? reset($results) : null;
    }
    
    private function getConditionGroupExpression (SQLConditionGroup $conditionGroup, array &$bindings)
    {
        $expression = "";
        foreach ($conditionGroup->getConditions() as $condition)
        {
            if (!empty($expression))
            {
                $expression .= " " . $conditionGroup->getConnector() . " ";
            }
            
            if ($condition instanceof SQLConditionGroup)
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
                $expression .= $condition["operand1"] . " " . $condition["operator"] . " " . SQL::WILDCARD;
                $bindings[] = $condition["operand2"];
            }
        }
        return $expression;
    }
}

?>