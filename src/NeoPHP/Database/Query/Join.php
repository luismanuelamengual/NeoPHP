<?php

namespace NeoPHP\Database\Query;

class Join extends ConditionGroup {

    const TYPE_JOIN = "JOIN";
    const TYPE_INNER_JOIN = "INNER";
    const TYPE_OUTER_JOIN = "OUTER";
    const TYPE_LEFT_JOIN = "LEFT";
    const TYPE_RIGHT_JOIN = "RIGHT";

    private $table;
    private $type;

    public function __construct($table, $type=self::TYPE_INNER_JOIN) {
        parent::__construct();
        $this->table = $table;
        $this->type = $type;
    }

    public function table($table=null) {
        $result = $this;
        if ($table != null) {
            $this->table = $table;
        }
        else {
            $result = $this->table;
        }
        return $result;
    }

    public function type($type=null) {
        $result = $this;
        if ($type != null) {
            $this->type = $type;
        }
        else {
            $result = $this->type;
        }
        return $result;
    }
}