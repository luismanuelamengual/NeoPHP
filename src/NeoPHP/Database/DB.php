<?php

namespace NeoPHP\Database;

use NeoPHP\Database\Builder\PostgresQueryBuilder;
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
            $connectionName = get_property("database.default");
            if ($connectionName == null) {
                throw new RuntimeException("No default database was configured !!");
            }
        }
        if (!isset(self::$connections[$connectionName])) {
            $connectionsConfig = get_property("database.connections", []);
            if (!isset($connectionsConfig[$connectionName])) {
                throw new RuntimeException("Database connection with name \"$connectionName\" doesnt exist !!");
            }

            $connectionConfig = $connectionsConfig[$connectionName];
            $connectionDriver = $connectionConfig["driver"];
            $connectionQueryBuilder = null;
            switch ($connectionDriver) {
                case "pgsql":
                    $connectionQueryBuilder = new PostgresQueryBuilder();
                    break;
                default:
                    $builderClasses = get_property("database.builders", []);
                    if (isset($builderClasses[$connectionDriver])) {
                        $connectionQueryBuilder = $builderClasses[$connectionDriver];
                    }
                    else {
                        throw new RuntimeException("Database driver \"$connectionDriver\" not supported !!. Consider adding a builder class for the driver.");
                    }
                    break;
            }
            $connectionDsn = $connectionDriver;
            $connectionDsn .= ":host=" . $connectionConfig["host"];
            $connectionDsn .= ";port=" . $connectionConfig["port"];
            $connectionDsn .= ";dbname=" . $connectionConfig["database"];
            $connectionPdo = new PDO($connectionDsn, $connectionConfig["username"], $connectionConfig["password"]);
            self::$connections[$connectionName] = new Connection($connectionPdo, $connectionQueryBuilder, $connectionConfig);
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
     * @param array $bindings
     * @return bool|int
     */
    public static function exec($sql, array $bindings = []) {
        return self::connection()->exec($sql, $bindings);
    }

    /**
     * @param $sql
     * @param array $bindings
     * @return array
     */
    public static function query($sql, array $bindings = []) {
        return self::connection()->query($sql, $bindings);
    }

    /**
     * @param null $readOnly
     * @return bool|mixed
     */
    public static function readOnly($readOnly=null) {
        return self::connection()->readOnly($readOnly);
    }

    /**
     * @return bool|mixed
     */
    public static function logEnabled($logEnabled=null) {
        return self::connection()->logEnabled($logEnabled);
    }

    /**
     * Registra un nuevo listener de sentencias sql
     * @param callable $listener
     */
    public static function listen(callable $listener) {
        self::connection()->listen($listener);
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments) {
        return call_user_func([self::connection(), $name], $arguments);
    }
}