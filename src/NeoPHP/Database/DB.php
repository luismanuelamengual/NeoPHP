<?php

namespace NeoPHP\Database;

use NeoPHP\Database\Builder\PostgresQueryBuilder;
use NeoPHP\Query\Query;
use PDO;
use RuntimeException;

/**
 * Class Connections
 * @package NeoPHP\Database
 */
abstract class DB {

    private static $connections = [];

    /**
     * Returns a connection
     * @param string|null $connectionName name of a connection
     * @return Connection database connection
     */
    public static function connection(?string $connectionName=null): Connection {
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
            if (!empty($connectionConfig["emulate_prepared_statements"])) {
                $connectionPdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
            }
            self::$connections[$connectionName] = new Connection($connectionPdo, $connectionQueryBuilder, $connectionConfig);
        }
        return self::$connections[$connectionName];
    }

    /**
     * Returns a connection query to a certain table on the default connection
     * @param string $table table to make a query
     * @return ConnectionTable
     */
    public static function table($table): ConnectionTable {
        return self::connection()->table($table);
    }

    /**
     * Execute an query on the default connection
     * @param Query|string $sql query to be executed
     * @param array $bindings sql bindings
     * @return bool|int
     * @throws \Exception
     */
    public static function exec($sql, array $bindings = []) {
        return self::connection()->exec($sql, $bindings);
    }

    /**
     * Executes a query sql on the defaul connection
     * @param Query|string $sql query to be executed
     * @param array $bindings sql bindings
     * @return array results
     * @throws \Exception
     */
    public static function query($sql, array $bindings = []) {
        return self::connection()->query($sql, $bindings);
    }

    /**
     * Set the default connection to be read only
     * @param bool|null $readOnly read only
     * @return bool|mixed
     */
    public static function readOnly(?bool $readOnly=null) {
        return self::connection()->readOnly($readOnly);
    }

    /**
     * Set the default connection logging enabled
     * @param bool|null $logEnabled log enabled
     * @return bool|mixed
     */
    public static function logEnabled(?bool $logEnabled=null) {
        return self::connection()->logEnabled($logEnabled);
    }

    /**
     * Set the default connection debug mode
     * @param bool|null $debugEnabled debug enabled
     * @return bool|mixed
     */
    public static function debugEnabled(?bool $debugEnabled=null) {
        return self::connection()->debugEnabled($debugEnabled);
    }

    /**
     * Listens for sql queries
     * @param callable $listener
     */
    public static function listen(callable $listener) {
        self::connection()->listen($listener);
    }

    /**
     * Calls static methods for the default connection
     * @param string $name name of method
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments) {
        return call_user_func([self::connection(), $name], $arguments);
    }
}