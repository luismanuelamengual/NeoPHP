<?php

namespace NeoPHP\Resources;

use NeoPHP\Database\Query\DeleteQuery;
use NeoPHP\Database\Query\InsertQuery;
use NeoPHP\Database\Query\SelectQuery;
use NeoPHP\Database\Query\UpdateQuery;

abstract class ResourceManager {

    public abstract function find(SelectQuery $query);
    public abstract function insert(InsertQuery $query);
    public abstract function update(UpdateQuery $query);
    public abstract function delete(DeleteQuery $query);
}