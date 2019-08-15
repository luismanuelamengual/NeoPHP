<?php

namespace NeoPHP\Query;

use NeoPHP\Query\Traits\OrderByFieldsTrait;
use NeoPHP\Query\Traits\SelectModifiersTrait;

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