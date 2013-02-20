<?php

class DataObject
{
    const JOINTYPE_INNER = 0;
    const JOINTYPE_LEFT = 1;
    const JOINTYPE_RIGHT = 2;
    const JOINTYPE_FULL = 3;
    const CONDITIONTYPE_AND = 0;
    const CONDITIONTYPE_OR = 1;
    const ORDERBYTYPE_DESC = 0;
    const ORDERBYTYPE_ASC = 1;
    
    private $connection;
    private $tableName;
    private $fields;
    private $sql;
    private $currentResult;
    private $currentFetchIndex;
    
    public function __construct (Connection $connection, $tableName="")
    {
        $this->connection = $connection;
        $this->tableName = $tableName;
        $this->fields = new stdClass();
        $this->sql = new stdClass();
        $this->currentResult = new stdClass();
        $this->currentFetchIndex = -1;
    }
        
    public function __set ($name, $value)
    {
        $this->fields->$name = $value;
    }

    public function __get ($name)
    {
        return $this->fields->$name;
    }
    
    public function __isset ($name)
    {
        return isset($this->fields->$name);
    }
   
    public function __unset ($name)
    {
        unset($this->fields->$name);
    }
    
    public function setTableName ($tableName)
    {
        $this->tableName = $tableName;
    }
    
    public function getTableName ()
    {
        return $this->tableName;
    }
    
    public function setTableAlias ($tableAlias)
    {
        $this->sql->alias = $tableAlias;
    }
    
    public function getTableAlias ()
    {
        return isset($this->sql->alias)? $this->sql->alias : "";
    }
    
    public function setSelectStatement ($selectStatement)
    {
        $this->sql->selectStatement = $selectStatement;
    }
    
    public function getSelectStatement ()
    {
        return isset($this->sql->selectStatement)? $this->sql->selectStatement : "";
    }
    
    public function setWhereStatement ($whereStatement)
    {
        $this->sql->whereStatement = $whereStatement;
    }
    
    public function getWhereStatement ()
    {
        return isset($this->sql->whereStatement)? $this->sql->whereStatement : "";
    }
    
    public function setHavingStatement ($havingStatement)
    {
        $this->sql->havingStatement = $havingStatement;
    }
    
    public function getHavingStatement ()
    {
        return isset($this->sql->havingStatement)? $this->sql->havingStatement : "";
    }
    
    public function setOrderByStatement ($orderByStatement)
    {
        $this->sql->orderByStatement = $orderByStatement;
    }
    
    public function getOrderByStatement ()
    {
        return isset($this->sql->orderByStatement)? $this->sql->orderByStatement : "";
    }
    
    public function setGroupByStatement ($groupByStatement)
    {
        $this->sql->groupByStatement = $groupByStatement;
    }
    
    public function getGroupByStatement ()
    {
        return isset($this->sql->groupByStatement)? $this->sql->groupByStatement: "";
    }
    
    public function setJoinStatement ($joinStatement)
    {
        $this->sql->joinStatement = $joinStatement;
    }
    
    public function getJoinStatement ()
    {
        return isset($this->sql->joinStatement)? $this->sql->joinStatement : "";
    }
    
    public function addJoin (DataObject $dataObject, $type=DataObject::JOINTYPE_INNER, $sourceField=null, $destinationField=null)
    {
        $sourceField = !empty($sourceField)? $sourceField : (lcfirst($dataObject->getTableName()) . "id");
        $destinationField = !empty($destinationField)? $destinationField : $sourceField;
        if (empty($this->sql->joinStatement))
            $this->sql->joinStatement = "";
        else
            $this->sql->joinStatement .= " ";
        switch ($type)
        {
            case DataObject::JOINTYPE_INNER: $this->sql->joinStatement .= "INNER JOIN "; break;
            case DataObject::JOINTYPE_LEFT: $this->sql->joinStatement .= "LEFT JOIN "; break;
            case DataObject::JOINTYPE_RIGHT: $this->sql->joinStatement .= "RIGHT JOIN "; break;
            case DataObject::JOINTYPE_FULL: $this->sql->joinStatement .= "FULL JOIN "; break;
        }
        $this->sql->joinStatement .= $dataObject->getTableName();
        $sourceTableName = $this->tableName;
        $sourceTableAlias = $this->getTableAlias();
        if (!empty($sourceTableAlias))
            $sourceTableName = $sourceTableAlias;
        $destinationTableName = $dataObject->getTableName();
        $destinationTableAlias = $dataObject->getTableAlias();
        if (!empty($destinationTableAlias))
        {
            $destinationTableName = $destinationTableAlias;
            $this->sql->joinStatement .= " AS " . $destinationTableAlias;
        }
        $this->sql->joinStatement .= " ON " . $sourceTableName . "." . $sourceField . "=" . $destinationTableName . "." . $destinationField;
        $dataJoinStatement = $dataObject->getJoinStatement();
        if (!empty($dataJoinStatement))
            $this->sql->joinStatement .= " " . $dataJoinStatement;
    }
    
    public function addSelectField ($field, $fieldAlias=null, $fieldTable=null)
    {
        $this->addSelectFields(array($field), $fieldAlias, $fieldTable);
    }
    
    public function addSelectFields ($fields, $fieldsFormat=null, $fieldsTable=null)
    {
        $processedFields = array();
        foreach ($fields as $field)
        {    
            if (empty($this->sql->selectStatement))
                $this->sql->selectStatement = "";
            else
                $this->sql->selectStatement .= ", ";
            if (!empty($fieldsTable))
                $this->sql->selectStatement .= $fieldsTable . ".";
            $this->sql->selectStatement .= $field;
            if (!empty($fieldsFormat))
                $this->sql->selectStatement .= (" AS " . str_replace("%s", $field, $fieldsFormat));
        }
    }
    
    public function addWhereCondition ($condition, $conditionType = DataObject::CONDITIONTYPE_AND)
    {
        if (empty($this->sql->whereStatement))
            $this->sql->whereStatement = "";
        else
            $this->sql->whereStatement .= " " . (($conditionType == DataObject::CONDITIONTYPE_AND)? "AND " : "OR ");
        $this->sql->whereStatement .= $condition;
    }
    
    public function addOrderByField ($field, $direction=null)
    {
        if (empty($this->sql->orderByStatement))
            $this->sql->orderByStatement = "";
        else
            $this->sql->orderByStatement .= ", ";
        $this->sql->orderByStatement .= $field;
        if ($direction !== null)
            $this->sql->orderByStatement .= " " . ($direction == DataObject::ORDERBYTYPE_DESC?"DESC":"ASC");
    }
    
    private function getProcessedFields ()
    {
        $fields = array();
        $fieldValues = array();
        foreach (get_object_vars($this->fields) as $field=>$fieldValue)
        {
            array_push($fields, $field);
            if (is_string($fieldValue))
                $fieldValue = "'" . $fieldValue . "'";
            array_push($fieldValues, $fieldValue);
        }
        return array_combine($fields, $fieldValues);
    }
    
    public function getInsertSql ()
    {
        $processedFields = $this->getProcessedFields ();
        return "INSERT INTO " . $this->tableName . " (" . implode(",", array_keys($processedFields)) . ") VALUES (" . implode(",", array_values($processedFields)) . ")";
    }
    
    public function getUpdateSql ()
    {
        $processedFields = $this->getProcessedFields ();
        $processedFields = array_map(create_function('$v,$k', 'return $k . "=" . $v;'), array_values($processedFields), array_keys($processedFields));
        $sql = "UPDATE " . $this->tableName . " SET " . implode(",", $processedFields);
        if ($this->sql->whereStatement)
            $sql .= " WHERE " . $this->sql->whereStatement;
        return $sql;
    }
    
    public function getDeleteSql ()
    {
        $sql = "DELETE FROM " . $this->tableName;
        if ($this->sql->whereStatement)
            $sql .= " WHERE " . $this->sql->whereStatement;
        return $sql;
    }
    
    public function getSelectSql ()
    {
        $sql = "SELECT ";
        $sql .= !empty($this->sql->selectStatement)? $this->sql->selectStatement : "*";
        $sql .= " FROM " . $this->tableName;
        if (isset($this->sql->alias))
            $sql .= " AS " . $this->sql->alias;
        if (isset($this->sql->joinStatement))
            $sql .= " " . $this->sql->joinStatement;
        if (isset($this->sql->whereStatement))
            $sql .= " WHERE " . $this->sql->whereStatement;
        if (isset($this->sql->havingStatement))
            $sql .= " HAVING " . $this->sql->havingStatement;
        if (isset($this->sql->groupByStatement))
            $sql .= " GROUP BY " . $this->sql->groupByStatement;
        if (isset($this->sql->orderByStatement))
            $sql .= " ORDER BY " . $this->sql->orderByStatement;
        return $sql;
    }
    
    public function insert ()
    {
        $this->executeSql($this->getInsertSql());
    }
    
    public function update ()
    {
        $this->executeSql($this->getUpdateSql());
    }
    
    public function delete ()
    {
        $this->executeSql($this->getDeleteSql());
    }
    
    public function find ($autoFetch=false)
    {
        $returnValue = true;
        $this->executeSql($this->getSelectSql(), true);
        if ($autoFetch)
            $returnValue = $this->fetch ();
        return $returnValue;
    }
    
    public function getFields ()
    {
        return $this->fields;
    }
    
    public function resetResults ()
    {
        $this->currentResult = new stdClass();
        $this->currentFetchIndex = -1;
    }
    
    public function resetFields ()
    {
        $this->fields = new stdClass();
    }
    
    public function resetSqlData ()
    {
        $this->sql = new stdClass();
    }
    
    public function fetch ()
    {
        return $this->fetchRow($this->currentFetchIndex+1);
    }
    
    public function fetchRow($row)
    {
        $this->resetFields();
        $this->currentFetchIndex = $row;
        $rowFetched = false;
        if (!empty($this->currentResult->resultSet))
        {
            if (array_key_exists($row, $this->currentResult->resultSet))
            {
                $this->fields = $this->currentResult->resultSet[$row];
                $rowFetched = true;
            }
        }
        return $rowFetched;
    }
    
    public function fetchAll()
    {
        return $this->currentResult->resultSet;
    }
    
    private function executeSql ($sql, $isQuery=false)
    {
        $this->resetResults();
        $this->currentResult = ($isQuery)? $this->connection->query($sql) : $this->connection->exec($sql);
        if (!$this->currentResult->success)
            echo "ERROR " . $this->currentResult->error->code . ": " . $this->currentResult->error->driverErrorMessage;
    }
}

?>
