<?php

namespace NeoPHP\Query;

use NeoPHP\Query\Traits\FieldsTrait;
use NeoPHP\Query\Traits\TableTrait;

class InsertQuery extends Query {

    use TableTrait,
        FieldsTrait;

    public function __construct($table=null) {
        $this->table($table);
    }
}