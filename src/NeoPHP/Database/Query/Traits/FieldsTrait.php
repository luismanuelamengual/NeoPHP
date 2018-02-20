<?php

namespace NeoPHP\Database\Query\Traits;

trait FieldsTrait {

    private $fields = [];

    public function clearFields() {
        $this->fields = [];
        return $this;
    }

    public function getFields(): array {
        return $this->fields;
    }

    public function setFields(array $fields) {
        $this->fields = $fields;
        return $this;
    }

    public function set($name, $value) {
        $this->fields[$name] = $value;
        return $this;
    }

    public function get($name) {
        return $this->fields[$name];
    }
}