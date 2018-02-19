<?php

namespace NeoPHP\Database;

use PDO;
use RuntimeException;

/**
 * Class Databases
 * @package NeoPHP\Database
 */
abstract class Databases {

    private static $connections = [];

    /**
     * @param null $connectionName
     * @return Connection
     */
    public static function getConnection($connectionName=null): Connection {
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
     * @param $sql
     * @param array $bindings
     * @return null|\PDOStatement
     */
    public static function query($sql, array $bindings = []) {
        return self::getConnection()->query($sql, $bindings);
    }
}