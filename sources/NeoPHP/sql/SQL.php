<?php

namespace NeoPHP\sql;

abstract class SQL
{
    const KEYWORD_SELECT = "SELECT";
    const KEYWORD_FROM = "FROM";
    const KEYWORD_AS = "AS";
    const KEYWORD_UPDATE = "UPDATE";
    const KEYWORD_INSERT_INTO = "INSERT INTO";
    const KEYWORD_DELETE_FROM = "DELETE FROM";
    const KEYWORD_JOIN = "JOIN";
    const KEYWORD_INNER_JOIN = "INNER JOIN";
    const KEYWORD_LEFT_JOIN = "LEFT JOIN";
    const KEYWORD_RIGHT_JOIN = "RIGHT JOIN";
    const KEYWORD_SET = "SET";
    const KEYWORD_ON = "ON";
    const KEYWORD_WHERE = "WHERE";
    const KEYWORD_HAVING = "HAVING";
    const KEYWORD_GROUP_BY = "GROUP BY";
    const KEYWORD_ORDER_BY = "ORDER BY";
    const KEYWORD_UNION = "UNION";
    const KEYWORD_LIMIT = "LIMIT";
    const KEYWORD_OFFSET = "OFFSET";
    const KEYWORD_VALUES = "VALUES";
    const KEYWORD_DESC = "DESC";
    const KEYWORD_ASC = "ASC";
    const FUNCTION_MAX = "MAX";
    const FUNCTION_MIN = "MIN";
    const FUNCTION_COUNT = "COUNT";
    const FUNCTION_SUM = "SUM";
    const FUNCTION_AVG = "AVG";
    const OPERATOR_AND = "AND";
    const OPERATOR_OR = "OR";
    const OPERATOR_EQUAL = "=";
    const OPERATOR_NOT_EQUAL = "<>";
    const OPERATOR_GRATER_THAN = ">";
    const OPERATOR_GRATER_THAN_OR_EQUAL = ">=";
    const OPERATOR_LESS_THAN = "<";
    const OPERATOR_LESS_THAN_OR_EQUAL = "<=";
    const OPERATOR_BETWEEN = "BETWEEN";
    const OPERATOR_LIKE = "LIKE";
    const OPERATOR_IN = "IN";
    const ALL_COLUMNS = "*";
    const WILDCARD = "?";
}

?>