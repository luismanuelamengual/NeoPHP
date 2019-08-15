<?php

namespace NeoPHP\Query;

use NeoPHP\Query\Traits\GroupByFieldsTrait;
use NeoPHP\Query\Traits\HavingConditionsTrait;
use NeoPHP\Query\Traits\JoinsTrait;
use NeoPHP\Query\Traits\SelectModifiersTrait;
use NeoPHP\Query\Traits\OrderByFieldsTrait;
use NeoPHP\Query\Traits\SelectFieldsTrait;
use NeoPHP\Query\Traits\TableTrait;
use NeoPHP\Query\Traits\WhereConditionsTrait;

class SelectQuery extends Query {

    use TableTrait,
        SelectModifiersTrait,
        SelectFieldsTrait,
        OrderByFieldsTrait,
        GroupByFieldsTrait,
        WhereConditionsTrait,
        HavingConditionsTrait,
        JoinsTrait;

    public function __construct($table=null) {
        $this->table($table);
    }

    public function __clone() {
        $this->whereConditions = clone $this->whereConditions;
        $this->havingConditions = clone $this->havingConditions;
    }
}