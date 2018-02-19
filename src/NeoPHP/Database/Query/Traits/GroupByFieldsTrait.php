<?php

namespace NeoPHP\Database\Query\Traits;

trait GroupByFieldsTrait {

    private $groupByFields = [];

    public function clearGroupByFields() {
        $this->groupByFields = [];
        return $this;
    }

    public function addGroupByFields(...$fields) {
        foreach ($fields as $field) {
            $this->addGroupByField($field);
        }
        return $this;
    }

    public function addGroupByField($field) {
        $this->groupByFields[] = $field;
        return $this;
    }

    public function getGroupByFields(): array {
        return $this->groupByFields;
    }

    public function setGroupByFields(array $groupByFields) {
        $this->groupByFields = $groupByFields;
    }
}