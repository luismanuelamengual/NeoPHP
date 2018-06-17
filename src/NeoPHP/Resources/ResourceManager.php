<?php

namespace NeoPHP\Resources;

use RuntimeException;
use NeoPHP\Database\Query\DeleteQuery;
use NeoPHP\Database\Query\InsertQuery;
use NeoPHP\Database\Query\SelectQuery;
use NeoPHP\Database\Query\UpdateQuery;

abstract class ResourceManager {

    /**
     * @param SelectQuery $query
     */
    public function find(SelectQuery $query) {
        throw new RuntimeException("Unimplemented resource method \"find\" in resource \"" . get_called_class() . "\" !!");
    }

    /**
     * @param InsertQuery $query
     */
    public function insert(InsertQuery $query) {
        throw new RuntimeException("Unimplemented resource method \"insert\" in resource \"" . get_called_class() . "\" !!");
    }

    /**
     * @param UpdateQuery $query
     */
    public function update(UpdateQuery $query) {
        throw new RuntimeException("Unimplemented resource method \"update\" in resource \"" . get_called_class() . "\" !!");
    }

    /**
     * @param DeleteQuery $query
     */
    public function delete(DeleteQuery $query) {
        throw new RuntimeException("Unimplemented resource method \"delete\" in resource \"" . get_called_class() . "\" !!");
    }
}