<?php

namespace NeoPHP\Database\Query\Traits;

trait TableTrait {

    private $table;

    public function table($table = null) {
        $result = $this;
        if ($table != null) {
            $this->table = $table;
        }
        else {
            $result = $this->table;
        }
        return $result;
    }
}