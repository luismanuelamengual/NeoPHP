<?php

namespace NeoPHP\Database\Builder;

use MongoDB\Driver\Query;

abstract class QueryBuilder {

    public abstract function buildQuery (Query $query, array &$bindings);
}