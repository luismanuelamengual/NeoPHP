<?php

namespace NeoPHP\Database\Query\Traits;

trait OrderByFieldsTrait {

    private $orderByFields = [];

    public function orderByFields ($fields = null) {
        $result = $this;
        if ($fields == null) {
            $result = $this->orderByFields;
        }
        else {
            $this->orderByFields = is_array($fields)? $fields : func_get_args();
        }
        return $result;
    }

    public function orderBy($column, $direction="ASC") {
        $this->orderByFields[] = ["column"=>$column, "direction"=>$direction];
        return $this;
    }
}