<?php

namespace NeoPHP\Database\Query\Traits;

trait TableTrait {

    private $source;

    public function source($source) {
        $this->source = $source;
        return $this;
    }

    public function &getSource() {
        return $this->source;
    }
}