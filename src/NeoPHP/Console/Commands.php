<?php

namespace NeoPHP\Console;

use RuntimeException;
use NeoPHP\ActionNotFoundException;

/**
 * Class Commands
 * @package Sitrack\Console
 */
abstract class Commands {

    private static $commandInstances = [];
    private static $commands = [];

    /**
     * @param $commandName
     * @param $action
     */
    public static function register($commandName, $action) {
        self::$commands[$commandName] = $action;
    }

    /**
     * @throws \Exception
     */
    public static function handleCommand() {
        try {
            global $argv;
            $tokens = array_slice($argv, 1);
            $commandName = (string)$tokens[0];
            $commandTokens = array_slice($tokens, 1);

            $commandParameters = [];
            for ($i = 0; $i < sizeof($commandTokens); $i++) {
                $commandToken = $commandTokens[$i];
                $commandAssignationIndex = strpos($commandToken, '=');

                if ($commandAssignationIndex <= 0) {
                    throw new RuntimeException("Invalid command parameter \"$commandToken\" !!");
                }

                $commandParameterName = substr($commandToken, 0, $commandAssignationIndex);
                $commandParameterValue = substr($commandToken, $commandAssignationIndex + 1);
                $commandParameters[$commandParameterName] = $commandParameterValue;
            }

            self::executeCommand($commandName, $commandParameters);
        }
        catch (CommandNotFoundException $exception) {
            echo $exception->getMessage() . "\n";
        }
    }

    /**
     * Ejecuta un comando
     * @param string $commandName Nombre del comando
     * @param array $commandParameters
     * @throws CommandNotFoundException
     * @return mixed
     */
    public static function executeCommand($commandName, array $commandParameters) {
        if (isset(self::$commands[$commandName])) {
            $commandAction = self::$commands[$commandName];
        }
        else {
            //todo: hacer la busqueda de un comando a partir de un namespace base
            throw new CommandNotFoundException("Command name \"$commandName\" not found !!");
        }

        $result = null;
        if (is_string($commandAction) && class_exists($commandAction) && is_subclass_of($commandAction, Command::class)) {
            if (!isset(self::$commandInstances[$commandAction])) {
                self::$commandInstances[$commandAction] = new $commandAction;
            }
            self::$commandInstances[$commandAction]->handle($commandParameters);
        }
        else  {
            try {
                get_app()->execute($commandAction, $commandParameters);
            }
            catch (ActionNotFoundException $ex) {
                throw new CommandNotFoundException("Command \"$commandAction\" not found !!", 0, $ex);
            }
        }
        return $result;
    }
}