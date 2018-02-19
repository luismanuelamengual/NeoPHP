<?php

namespace NeoPHP\Database\Query\Traits;

trait TableTrait {

    private $table;

    public function __construct($table=null) {
        $this->setTable($table);
    }

    public function setTable($table) {
        $this->table = $table;
        return $this;
    }

    public function getTable() {
        return $this->table;
    }
}