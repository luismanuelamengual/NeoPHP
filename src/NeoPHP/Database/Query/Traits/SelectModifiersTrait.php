<?php

namespace NeoPHP\Database\Query\Traits;

trait SelectModifiersTrait {

    private $distinct = false;
    private $limit = null;
    private $offset = null;

    public function limit($limit = null) {
        $result = $this;
        if ($limit != null) {
            $this->limit = $limit;
        }
        else {
            $result = $this->limit;
        }
        return $result;
    }

    public function offset($offset = null) {
        $result = $this;
        if ($offset != null) {
            $this->offset = $offset;
        }
        else {
            $result = $this->limit;
        }
        return $result;
    }

    public function distinct($distinct = null) {
        $result = $this;
        if ($distinct != null) {
            $this->distinct = $distinct;
        }
        else {
            $result = $this->distinct;
        }
        return $result;
    }
}