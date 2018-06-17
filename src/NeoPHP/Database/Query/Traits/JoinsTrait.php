<?php

namespace NeoPHP\Database\Query\Traits;

use NeoPHP\Database\Query\Join;

trait JoinsTrait {

    private $joins = [];

    public function joins($joins) {
        $this->joins = is_array($joins)? $joins : func_get_args();
        return $this;
    }

    public function &getJoins () {
        return $this->joins;
    }

    public function innerJoin($table, $originField, $destinationField) {
        return $this->join($table, $originField, $destinationField, Join::TYPE_INNER_JOIN);
    }

    public function outerJoin($table, $originField, $destinationField) {
        return $this->join($table, $originField, $destinationField, Join::TYPE_OUTER_JOIN);
    }

    public function leftJoin($table, $originField, $destinationField) {
        return $this->join($table, $originField, $destinationField, Join::TYPE_LEFT_JOIN);
    }

    public function rightJoin($table, $originField, $destinationField) {
        return $this->join($table, $originField, $destinationField, Join::TYPE_RIGHT_JOIN);
    }

    public function join ($join) {
        if ($join instanceof Join) {
            $this->joins[] = $join;
        }
        else {
            $args = func_get_args();
            $tableName = $args[0];
            $originField = $args[1];
            $destinationField = $args[2];
            $joinObj = new Join($tableName);
            if (isset($args[3])) {
                $joinObj->type($args[3]);
            }
            $joinObj->onField($originField, $destinationField);
            $this->joins[] = $joinObj;
        }
        return $this;
    }
}