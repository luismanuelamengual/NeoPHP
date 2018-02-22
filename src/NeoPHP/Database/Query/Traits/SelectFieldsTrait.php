<?php

namespace NeoPHP\Database\Query\Traits;

trait SelectFieldsTrait {

    private $selectFields = [];

    public function selectFields ($fields = null) {
        $result = $this;
        if ($fields == null) {
            $result = $this->selectFields;
        }
        else {
            $this->selectFields = is_array($fields)? $fields : func_get_args();
        }
        return $result;
    }

    public function select($fields) {
        $fields = is_array($fields) ? $fields : func_get_args();
        $this->selectFields = array_merge($this->selectFields, $fields);
        return $this;
    }
}