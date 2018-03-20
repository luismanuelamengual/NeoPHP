<?php

namespace NeoPHP\Database\Query;

class ConditionGroup {

    const CONNECTOR_AND = "AND";
    const CONNECTOR_OR = "OR";

    private $conditions = [];
    private $connector;

    public function __construct($connector=self::CONNECTOR_AND) {
        $this->connector = $connector;
    }

    public function connector($connector=null) {
        $result = $this;
        if ($connector == null) {
            $result = $this->connector;
        }
        else {
            $this->connector = $connector;
        }
        return $result;
    }

    public function conditions($conditions=null) {
        $result = $this;
        if ($conditions == null) {
            $result = $this->conditions;
        }
        else {
            $this->conditions = $conditions;
        }
        return $result;
    }

    public function isEmpty() {
        return empty($this->conditions);
    }

    public function on ($column, $operatorOrValue, $value=null) {
        $type = "basic";
        if ($value != null) {
            $operator = $operatorOrValue;
        }
        else {
            $operator = "=";
            $value = $operatorOrValue;
        }
        $this->conditions[] = compact("type", "column", "operator", "value");
        return $this;
    }

    public function onGroup(ConditionGroup $group) {
        $this->conditions[] = ["type"=>"group", "group"=>$group];
        return $this;
    }

    public function onRaw($sql, array $bindings = []) {
        $this->conditions[] = ["type"=>"raw", "sql"=>$sql, "bindings"=>$bindings];
        return $this;
    }

    public function onColumn($column, $operatorOrColumn, $otherColumn=null) {
        $type = "column";
        if ($otherColumn != null) {
            $operator = $operatorOrColumn;
        }
        else {
            $operator = "=";
            $otherColumn = $operatorOrColumn;
        }
        $this->conditions[] = compact("type", "column", "operator", "otherColumn");
        return $this;
    }

    public function onNull($column) {
        $this->conditions[] = ["type"=>"null", "column"=>$column];
        return $this;
    }

    public function onNotNull($column) {
        $this->conditions[] = ["type"=>"notNull", "column"=>$column];
        return $this;
    }
}