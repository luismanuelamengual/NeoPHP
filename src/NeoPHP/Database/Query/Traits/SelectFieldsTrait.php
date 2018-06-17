<?php

namespace NeoPHP\Database\Query\Traits;

use stdClass;

trait SelectFieldsTrait {

    private $selectFields = [];

    public function selectFields (array $selectFields) {
        $this->selectFields = $selectFields;
        return $this;
    }

    public function &getSelectFields () : array {
        return $this->selectFields;
    }

    public function select(...$fields) {
        foreach ($fields as $field) {
            if (is_array($field) && sizeof($field) == 2) {
                $this->selectField($field[0], $field[1]);
            }
            else {
                $this->selectField($field);
            }
        }
        return $this;
    }

    public function selectField ($field, $alias=null) {
        if (!empty($alias)) {
            $expression = $field;
            $field = new stdClass();
            $field->expression = $expression;
            $field->alias = $alias;
        } else {
            $aliasPos = stripos(strtoupper($field), ' AS ');
            if($aliasPos > 0) {
                $expression = trim(substr($field, 0, $aliasPos));
                $alias = trim(substr($field, $aliasPos + 4));
                $field = new stdClass();
                $field->expression = $expression;
                $field->alias = $alias;
            }
        }
        $this->selectFields[] = $field;
        return $this;
    }
}