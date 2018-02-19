<?php

namespace NeoPHP\Database\Query\Traits;

trait OrderByFieldsTrait {

    private $orderByFields = [];

    public function clearOrderByFields() {
        $this->orderByFields = [];
        return $this;
    }

    public function addOrderByFields(...$fields) {
        foreach ($fields as $field) {
            $this->addOrderByField($field);
        }
        return $this;
    }

    public function addOrderByField(...$fieldArguments) {
        $field = null;
        switch (sizeof($fieldArguments)) {
            case 1:
                $field = $fieldArguments[0];
                break;
            case 2:
                $field = [
                    "field" => $fieldArguments[0],
                    "direction" => $fieldArguments[1]
                ];
                break;
        }
        $this->orderByFields[] = $field;
        return $this;
    }

    public function getOrderByFields(): array {
        return $this->orderByFields;
    }

    public function setOrderByFields(array $orderByFields) {
        $this->orderByFields = $orderByFields;
        return $this;
    }
}