<?php

namespace NeoPHP\Database\Query\Traits;

trait FieldsTrait {

    private $fields = [];

    public function fields($fields = null) {
        $result = $this;
        if ($fields == null) {
            $result = $this->fields;
        }
        else {
            $this->fields = $fields;
        }
        return $result;
    }

    public function set($name, $value) {
        $this->fields[$name] = $value;
        return $this;
    }

    public function get($name) {
        return $this->fields[$name];
    }
}