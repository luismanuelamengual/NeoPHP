<?php

namespace NeoPHP\Core\Resources;

use NeoPHP\Database\Query\Traits\FieldsTrait;
use NeoPHP\Database\Query\Traits\GroupByFieldsTrait;
use NeoPHP\Database\Query\Traits\HavingConditionsTrait;
use NeoPHP\Database\Query\Traits\JoinsTrait;
use NeoPHP\Database\Query\Traits\ModifiersTrait;
use NeoPHP\Database\Query\Traits\OffsetAndLimitTrait;
use NeoPHP\Database\Query\Traits\OrderByFieldsTrait;
use NeoPHP\Database\Query\Traits\SelectFieldsTrait;
use NeoPHP\Database\Query\Traits\TableTrait;
use NeoPHP\Database\Query\Traits\WhereConditionsTrait;

abstract class ResourceManager {

    use TableTrait,
        FieldsTrait,
        ModifiersTrait,
        SelectFieldsTrait,
        OrderByFieldsTrait,
        GroupByFieldsTrait,
        WhereConditionsTrait,
        HavingConditionsTrait,
        OffsetAndLimitTrait,
        JoinsTrait;

    public abstract function find();
    public abstract function insert();
    public abstract function update();
    public abstract function delete();
}