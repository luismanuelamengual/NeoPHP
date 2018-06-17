<?php

namespace NeoPHP\Database\Query;

use NeoPHP\Database\Query\Traits\FieldsTrait;
use NeoPHP\Database\Query\Traits\TableTrait;

class InsertQuery extends Query {

    use TableTrait,
        FieldsTrait;

    public function __construct($table=null) {
        $this->source($table);
    }
}