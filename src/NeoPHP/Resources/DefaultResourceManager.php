<?php

namespace NeoPHP\Resources;

use NeoPHP\Database\DB;
use NeoPHP\Database\Query\DeleteQuery;
use NeoPHP\Database\Query\InsertQuery;
use NeoPHP\Database\Query\SelectQuery;
use NeoPHP\Database\Query\UpdateQuery;

/**
 * Class ConnectionResourceManager
 * @package NeoPHP\Resources
 */
class DefaultResourceManager extends ResourceManager {

    /**
     * @param SelectQuery $query
     * @return array
     */
    public function find(SelectQuery $query) {
        return DB::query($query);
    }

    /**
     * @param InsertQuery $query
     * @return bool|int
     */
    public function insert(InsertQuery $query) {
        return DB::exec($query);
    }

    /**
     * @param UpdateQuery $query
     * @return bool|int
     */
    public function update(UpdateQuery $query) {
        return DB::exec($query);
    }

    /**
     * @param DeleteQuery $query
     * @return bool|int
     */
    public function delete(DeleteQuery $query) {
        return DB::exec($query);
    }
}