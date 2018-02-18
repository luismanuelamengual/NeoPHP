<?php

namespace NeoPHP\Database\Builder;

use NeoPHP\Database\Query\Query;

abstract class QueryBuilder {

    public abstract function buildSql (Query $query, array &$bindings);
}