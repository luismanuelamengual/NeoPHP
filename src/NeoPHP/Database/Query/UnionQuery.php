<?php

namespace NeoPHP\Database\Query;

use NeoPHP\Database\Query\Traits\OrderByFieldsTrait;
use NeoPHP\Database\Query\Traits\SelectModifiersTrait;

class UnionQuery extends Query {

    use SelectModifiersTrait,
        OrderByFieldsTrait;

    private $queries = [];

    public function query(SelectQuery $query) {
        $this->queries[] = $query;
    }

    public function getQueries() {
        return $this->queries;
    }
}