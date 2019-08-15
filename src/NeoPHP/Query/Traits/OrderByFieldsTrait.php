<?php

namespace NeoPHP\Query\Traits;

use stdClass;

trait OrderByFieldsTrait {

    private $orderByFields = [];

    public function orderByFields (array $orderByFields) {
        $this->orderByFields = $orderByFields;
        return $this;
    }

    public function &getOrderByFields () : array {
        return $this->orderByFields;
    }

    public function orderBy(...$fields) {
        foreach ($fields as $field) {
            if (is_array($field) && sizeof($field) == 2) {
                $this->orderByField($field[0], $field[1]);
            }
            else {
                $this->orderByField($field);
            }
        }
        return $this;
    }

    public function orderByField($field, $direction="ASC") {
        $order = new stdClass();
        $order->field = $field;
        $order->direction = $direction;
        $this->orderByFields[] = $order;
        return $this;
    }
}