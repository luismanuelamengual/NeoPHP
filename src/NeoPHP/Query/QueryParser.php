<?php

namespace NeoPHP\Query;

use Exception;
use RuntimeException;

abstract class QueryParser {

    const STATE_NONE = "NONE";
    const STATE_SELECT = "SELECT";
    const STATE_WHERE = "WHERE";
    const STATE_ORDER_BY = "ORDER_BY";
    const STATE_GROUP_BY = "GROUP_BY";

    public static function parseQuery ($sql) {
        try {
            $tokens = self::getTokens($sql);
            $state = self::STATE_NONE;
            $whereConditions = [];
            $query = null;
            for ($i = 0; $i < sizeof($tokens); $i++) {
                $token = $tokens[$i];

                switch ($token) {
                    case "SELECT":
                        $query = new SelectQuery();
                        $state = self::STATE_SELECT;
                        break;
                    case "FROM":
                        $source = $tokens[++$i];
                        $query->source($source);
                        $state = self::STATE_NONE;
                        break;
                    case "WHERE":
                        $state = self::STATE_WHERE;
                        $whereConditions[] = new ConditionGroup();
                        break;
                    case "LIMIT":
                        $state = self::STATE_NONE;
                        $query->limit($tokens[++$i]);
                        break;
                    case "OFFSET":
                        $state = self::STATE_NONE;
                        $query->offset($tokens[++$i]);
                        break;
                    case "ORDER":
                        if ($tokens[$i + 1] == "BY") {
                            $i++;
                            $state = self::STATE_ORDER_BY;
                        }
                        break;
                    case "GROUP":
                        if ($tokens[$i + 1] == "BY") {
                            $i++;
                            $state = self::STATE_GROUP_BY;
                        }
                        break;
                    default:
                        switch ($state) {
                            case self::STATE_SELECT:
                                if ($tokens[$i + 1] == "AS") {
                                    $query->selectField($token, $tokens[$i + 2]);
                                    $i += 2;
                                }
                                else {
                                    if ($token != "*") {
                                        $query->selectField($token);
                                    }
                                }
                                break;
                            case self::STATE_ORDER_BY:
                                $direction = "ASC";
                                if ($tokens[$i + 1] == "ASC") {
                                    $direction = "ASC";
                                    $i++;
                                }
                                else if ($tokens[$i + 1] == "DESC") {
                                    $direction = "DESC";
                                    $i++;
                                }
                                $query->orderByField($token, $direction);
                                break;
                            case self::STATE_GROUP_BY:
                                $query->groupBy($token);
                                break;
                            case self::STATE_WHERE:
                                switch ($token) {
                                    case "(":
                                        $whereConditions[] = new ConditionGroup();
                                        break;
                                    case ")":
                                        $lastWhereConditionGroup = array_pop($whereConditions);
                                        if (!empty($lastWhereConditionGroup)) {
                                            end($whereConditions)->onGroup($lastWhereConditionGroup);
                                        }
                                        break;
                                    case "AND":
                                    case "OR":
                                        end($whereConditions)->connector($token);
                                        break;
                                    default:
                                        $field = $token;
                                        $operator = $tokens[++$i];
                                        $value = $tokens[++$i];
                                        if ($value == "(") {
                                            $value = [];
                                            for ($i = $i + 1; $i < sizeof($tokens); $i++) {
                                                $valueItem = $tokens[$i];
                                                if ($valueItem == ")") {
                                                    break;
                                                }
                                                $value[] = $valueItem;
                                            }
                                        }
                                        $lastConditionGroup = end($whereConditions);
                                        $lastConditionGroup->on($field, $operator, $value);
                                        break;
                                }
                                break;
                        }
                        break;
                }
            }

            $lastWhereConditionGroup = array_pop($whereConditions);
            if (!empty($lastWhereConditionGroup)) {
                $query->whereConditionGroup($lastWhereConditionGroup);
            }

            return $query;
        }
        catch (Exception $ex) {
            throw new RuntimeException("Error parsing query: " . $ex->getMessage(), 0, $ex);
        }
    }

    private static function getTokens($sql) {
        $sql = trim($sql);
        $tokens = [];
        $token = null;
        $isStringToken = false;
        $pos = 0;
        while ($pos < strlen($sql)) {
            $character = $sql{$pos};
            if (empty($token)) {
                if ($character == '(' || $character == ')') {
                    $tokens[] = $character;
                }
                else if (!ctype_space($character) && $character != ',') {
                    if ($character == '\'') {
                        $isStringToken = true;
                    }
                    $token = $character;
                }
            }
            else {
                if ($isStringToken) {
                    if ($character == '\\') {
                        $pos++;
                        $token .= $sql{$pos};
                        $pos++;
                        continue;
                    }

                    $token .= $character;
                    if ($character == '\'') {
                        $tokens[] = substr($token, 1, strlen($token) - 2);
                        $isStringToken = false;
                        $token = null;
                    }
                }
                else {
                    if (ctype_space($character) || $character == ',' || $character == '(' || $character == ')') {
                        $tokens[] = $token;
                        $token = null;

                        if ($character == '(' || $character == ')') {
                            $tokens[] = $character;
                        }
                    }
                    else {
                        $token .= $character;
                    }
                }
            }
            $pos++;
        }
        if (!empty($token)) {
            $tokens[] = $token;
        }
        return $tokens;
    }
}