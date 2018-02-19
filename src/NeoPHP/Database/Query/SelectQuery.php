<?php

namespace NeoPHP\Database\Query;

use NeoPHP\Database\Query\Traits\GroupByFieldsTrait;
use NeoPHP\Database\Query\Traits\HavingConditionsTrait;
use NeoPHP\Database\Query\Traits\JoinsTrait;
use NeoPHP\Database\Query\Traits\ModifiersTrait;
use NeoPHP\Database\Query\Traits\OffsetAndLimitTrait;
use NeoPHP\Database\Query\Traits\OrderByFieldsTrait;
use NeoPHP\Database\Query\Traits\SelectFieldsTrait;
use NeoPHP\Database\Query\Traits\TableTrait;
use NeoPHP\Database\Query\Traits\WhereConditionsTrait;

class SelectQuery extends Query {

    use TableTrait,
        ModifiersTrait,
        SelectFieldsTrait,
        OrderByFieldsTrait,
        GroupByFieldsTrait,
        WhereConditionsTrait,
        HavingConditionsTrait,
        OffsetAndLimitTrait,
        JoinsTrait;
}