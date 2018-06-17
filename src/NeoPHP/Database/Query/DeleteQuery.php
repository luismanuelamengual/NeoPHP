<?php

namespace NeoPHP\Database\Query;

use NeoPHP\Database\Query\Traits\TableTrait;
use NeoPHP\Database\Query\Traits\WhereConditionsTrait;

class DeleteQuery extends Query {

    use TableTrait,
        WhereConditionsTrait;

    public function __construct($table=null) {
        $this->source($table);
    }
}