<?php

namespace NeoPHP\Query;

use NeoPHP\Query\Traits\TableTrait;
use NeoPHP\Query\Traits\WhereConditionsTrait;

class DeleteQuery extends Query {

    use TableTrait,
        WhereConditionsTrait;

    public function __construct($table=null) {
        $this->table($table);
    }
}