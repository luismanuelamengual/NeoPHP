<?php

namespace NeoPHP\Query;

class RawExpression {

    private $value;
    private $bindings;

    public function __construct($value, array $bindings = []) {
        $this->value = $value;
        $this->bindings = $bindings;
    }

    public function getValue() {
        return $this->value;
    }

    public function getBindings(): array {
        return $this->bindings;
    }
}