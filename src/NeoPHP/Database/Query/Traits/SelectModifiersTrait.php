<?php

namespace NeoPHP\Database\Query\Traits;

trait SelectModifiersTrait {

    private $distinct = false;
    private $limit = null;
    private $offset = null;

    public function limit($limit) {
        $this->limit = $limit;
        return $this;
    }

    public function &getLimit() {
        return $this->limit;
    }

    public function offset($offset) {
        $this->offset = $offset;
        return $this;
    }

    public function &getOffset() {
        return $this->offset;
    }

    public function distinct($distinct) {
        $this->distinct = $distinct;
        return $this;
    }

    public function &getDistinct() {
        return $this->distinct;
    }
}