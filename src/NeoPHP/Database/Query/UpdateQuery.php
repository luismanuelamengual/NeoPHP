<?php

namespace NeoPHP\Database\Query;

use NeoPHP\Database\Query\Traits\FieldsTrait;
use NeoPHP\Database\Query\Traits\TableTrait;
use NeoPHP\Database\Query\Traits\WhereConditionsTrait;

class UpdateQuery extends Query {

    use TableTrait,
        WhereConditionsTrait,
        FieldsTrait;

    public function __construct($table=null) {
        $this->source($table);
    }
}