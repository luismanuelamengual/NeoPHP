<?php

namespace NeoPHP\Query;

use NeoPHP\Query\Traits\FieldsTrait;
use NeoPHP\Query\Traits\TableTrait;
use NeoPHP\Query\Traits\WhereConditionsTrait;

class UpdateQuery extends Query {

    use TableTrait,
        WhereConditionsTrait,
        FieldsTrait;

    public function __construct($table=null) {
        $this->table($table);
    }

    public function __clone() {
        $this->whereConditions = clone $this->whereConditions;
    }
}