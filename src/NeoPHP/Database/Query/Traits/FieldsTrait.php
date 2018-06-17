<?php

namespace NeoPHP\Database\Query\Traits;

trait FieldsTrait {

    private $fields = [];

    public function fields($fields) {
        $this->fields = is_array($fields)? $fields : func_get_args();;
        return $this;
    }

    public function &getFields() {
        return $this->fields;
    }

    public function set($name, $value) {
        $this->fields[$name] = $value;
        return $this;
    }

    public function get($name) {
        return $this->fields[$name];
    }
}