<?php

namespace NeoPHP\Database\Query\Traits;

trait GroupByFieldsTrait {

    private $groupByFields = [];

    public function groupByFields ($fields = null) {
        $result = $this;
        if ($fields == null) {
            $result = $this->groupByFields;
        }
        else {
            $this->groupByFields = is_array($fields)? $fields : func_get_args();
        }
        return $result;
    }

    public function groupBy($column) {
        $this->groupByFields[] = $column;
        return $this;
    }
}