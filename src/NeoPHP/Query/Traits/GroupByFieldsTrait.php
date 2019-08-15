<?php

namespace NeoPHP\Query\Traits;

trait GroupByFieldsTrait {

    private $groupByFields = [];

    public function groupByFields (array $groupByFields) {
        $this->groupByFields = $groupByFields;
        return $this;
    }

    public function &getGroupByFields () : array {
        return $this->groupByFields;
    }

    public function groupBy(...$fields) {
        $this->groupByFields = array_merge($this->groupByFields, $fields);
        return $this;
    }
}