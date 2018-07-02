<?php

namespace NeoPHP\Database\Query\Traits;

trait SelectModifiersTrait {

    private $distinct = false;
    private $limit = null;
    private $offset = null;
    private $forUpdate = false;
    private $forShare = false;

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

    public function forUpdate($forUpdate) {
        $this->forUpdate = $forUpdate;
        return $this;
    }

    public function isForUpdate () {
        return $this->forUpdate;
    }

    public function forShare($forShare) {
        $this->forShare = $forShare;
        return $this;
    }

    public function isForShare () {
        return $this->forShare;
    }
}