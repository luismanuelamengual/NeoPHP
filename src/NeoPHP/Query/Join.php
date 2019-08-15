<?php

namespace NeoPHP\Query;

class Join extends ConditionGroup {

    const TYPE_JOIN = "JOIN";
    const TYPE_INNER_JOIN = "INNER JOIN";
    const TYPE_OUTER_JOIN = "OUTER JOIN";
    const TYPE_LEFT_JOIN = "LEFT JOIN";
    const TYPE_RIGHT_JOIN = "RIGHT JOIN";
    const TYPE_CROSS_JOIN = "CROSS JOIN";

    private $table;
    private $type;

    public function __construct($table, $type=self::TYPE_INNER_JOIN) {
        parent::__construct();
        $this->table = $table;
        $this->type = $type;
    }

    public function table($table) {
        $this->table = $table;
        return $this;
    }

    public function &getTable() {
        return $this->table;
    }

    public function type($type) {
        $this->type = $type;
        return $this;
    }

    public function &getType() {
        return $this->type;
    }
}