<?php

namespace NeoPHP\Database\Query\Traits;

trait SelectFieldsTrait {

    private $selectFields = [];

    public function clearSelectFields() {
        $this->selectFields = [];
        return $this;
    }

    public function addSelectFields(...$fields) {
        foreach ($fields as $field) {
            if (is_array($field)) {
                call_user_func_array([$this, "addSelectField"], $field);
            }
            else {
                $this->addSelectField($field);
            }
        }
        return $this;
    }

    public function addSelectField(...$fieldArguments) {
        $field = null;
        switch (sizeof($fieldArguments)) {
            case 1:
                $field = $fieldArguments[0];
                break;
            case 2:
                $field = [
                    "field" => $fieldArguments[0],
                    "alias" => $fieldArguments[1]
                ];
                break;
            case 3:
                $field = [
                    "field" => $fieldArguments[0],
                    "alias" => $fieldArguments[1],
                    "table" => $fieldArguments[2]
                ];
                break;
        }
        $this->selectFields[] = $field;
        return $this;
    }

    public function getSelectFields(): array {
        return $this->selectFields;
    }

    public function setSelectFields(array $selectFields) {
        $this->selectFields = $selectFields;
        return $this;
    }
}