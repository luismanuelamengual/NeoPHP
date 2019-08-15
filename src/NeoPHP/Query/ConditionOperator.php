<?php

namespace NeoPHP\Query;

abstract class ConditionOperator {

    const EQUALS = "eq";
    const EQUALS_FIELD = "eqField";
    const DISTINCT = "dt";
    const LIKE = "like";
    const NOT_LIKE = "notLike";
    const GREATER_THAN = "gt";
    const GREATER_OR_EQUALS_THAN = "gte";
    const LESS_THAN = "lt";
    const LESS_OR_EQUALS_THAN = "lte";
    const NULL = "null";
    const NOT_NULL = "notNull";
    const IN = "in";
    const NOT_IN = "notIn";
    const CONTAINS = "ct";
    const NOT_CONTAINS = "nct";

    public static function getOperator(string $operatorString) {
        switch (trim($operatorString)) {
            case '=':
                $result = self::EQUALS;
                break;
            case '!=':
            case '<>':
                $result = self::DISTINCT;
                break;
            case '>':
                $result = self::GREATER_THAN;
                break;
            case '<':
                $result = self::LESS_THAN;
                break;
            case '>=':
                $result = self::GREATER_OR_EQUALS_THAN;
                break;
            case '<=':
                $result = self::LESS_OR_EQUALS_THAN;
                break;
            default :
                $result = $operatorString;
                break;
        }
        return $result;
    }
}