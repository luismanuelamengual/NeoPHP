<?php

namespace NeoPHP\Database;

use PDO;
use RuntimeException;

/**
 * Class Connections
 * @package NeoPHP\Database
 */
abstract class DB {

    private static $connections = [];

    /**
     * @param null $connectionName
     * @return Connection
     */
    public static function connection($connectionName=null): Connection {
        if ($connectionName == null) {
            $connectionName = getProperty("database.default");
            if ($connectionName == null) {
                throw new RuntimeException("No default database was configured !!");
            }
        }
        if (!isset(self::$connections[$connectionName])) {
            $connectionsConfig = getProperty("database.connections", []);
            if (!isset($connectionsConfig[$connectionName])) {
                throw new RuntimeException("Database connection with name \"$connectionName\" doesnt exist !!");
            }
            $connectionConfig = $connectionsConfig[$connectionName];
            $connectionDsn = $connectionConfig["driver"];
            $connectionDsn .= ":host=" . $connectionConfig["host"];
            $connectionDsn .= ";port=" . $connectionConfig["port"];
            $connectionDsn .= ";dbname=" . $connectionConfig["database"];
            $connectionPdo = new PDO($connectionDsn, $connectionConfig["username"], $connectionConfig["password"]);
            self::$connections[$connectionName] = new Connection($connectionPdo, $connectionConfig);
        }
        return self::$connections[$connectionName];
    }

    /**
     * @param $table
     * @return ConnectionTable
     */
    public static function table($table): ConnectionTable {
        return self::connection()->table($table);
    }

    /**
     * @param $sql
     * @param $bindings
     * @return bool|int
     */
    public static function exec($sql, $bindings) {
        return self::connection()->exec($sql, $bindings);
    }

    /**
     * @param $sql
     * @param $bindings
     * @return array
     */
    public static function query($sql, $bindings) {
        return self::connection()->query($sql, $bindings);
    }
}