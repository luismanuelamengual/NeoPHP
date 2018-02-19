<?php

namespace NeoPHP\Database\Query;

use NeoPHP\Database\Query\Traits\FieldsTrait;
use NeoPHP\Database\Query\Traits\TableTrait;

class InsertQuery extends Query {

    use TableTrait,
        FieldsTrait;
}