<?php

namespace NeoPHP\Database\Builder;

use NeoPHP\Database\Query\Query;
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
                if (is_string($selectField)) {
                    $sql .= $selectField;
                }
                else if (is_array($selectField)) {
                    if (isset($selectField["table"])) {
                        $sql .= $selectField["table"];
                        $sql .= ".";
                    }
                    $sql .= $selectField["field"];
                    if (isset($selectField["alias"])) {
                        $sql .= " AS ";
                        $sql .= $selectField["alias"];
                    }
                }
            }
        }
        return $sql;
    }
}