<?php

namespace NeoPHP\Database\Query;

class RawValue {

    private $value;

    /**
     * RawValue constructor.
     * @param $value
     */
    public function __construct($value) {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue() {
        return $this->value;
    }
}