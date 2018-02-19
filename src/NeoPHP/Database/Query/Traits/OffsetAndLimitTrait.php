<?php

namespace NeoPHP\Database\Query\Traits;

trait OffsetAndLimitTrait {

    private $limit = null;
    private $offset = null;

    public function getLimit() {
        return $this->limit;
    }

    public function setLimit($limit) {
        $this->limit = $limit;
        return $this;
    }

    public function getOffset() {
        return $this->offset;
    }

    public function setOffset($offset) {
        $this->offset = $offset;
        return $this;
    }
}