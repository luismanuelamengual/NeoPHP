<?php

namespace NeoPHP\Query\Traits;

trait TableTrait {

    private $table;
    private $alias;

    public function table($table) {
        $this->table = $table;
        return $this;
    }

    public function &getTable() {
        return $this->table;
    }

    public function alias($alias) {
        $this->alias = $alias;
        return $this;
    }

    public function &getAlias() {
        return $this->alias;
    }
}