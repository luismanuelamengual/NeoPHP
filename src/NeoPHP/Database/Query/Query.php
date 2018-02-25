<?php

namespace NeoPHP\Database\Query;

/**
 * Class Query
 * @package NeoPHP\Database\Query
 */
abstract class Query {

    /**
     * @param $table
     * @return SelectQuery
     */
    public static function selectFrom ($table): SelectQuery {
        return new SelectQuery($table);
    }

    /**
     * @param $table
     * @return InsertQuery
     */
    public static function insertInto ($table): InsertQuery {
        return new InsertQuery($table);
    }

    /**
     * @param $table
     * @return UpdateQuery
     */
    public static function update ($table): UpdateQuery {
        return new UpdateQuery($table);
    }

    /**
     * @param $table
     * @return DeleteQuery
     */
    public static function deleteFrom ($table): DeleteQuery {
        return new DeleteQuery($table);
    }
}