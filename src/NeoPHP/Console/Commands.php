<?php

namespace NeoPHP\Console;

use RuntimeException;
use NeoPHP\ActionNotFoundException;

/**
 * Class Commands - Permite la registración de comandos en el sistema
 * @package NeoPHP\Console
 */
abstract class Commands {

    private static $commandInstances = [];
    private static $commands = [];

    /**
     * Registra un nuevo comando en el sistema
     * @param string $commandName nombre de comando
     * @param $action
     */
    public static function register(string $commandName, $action) {
        self::$commands[$commandName] = $action;
    }

    /**
     * Función que maneja un comando ingresado al sistema
     * @throws \Exception
     */
    public static function handleCommand() {
        try {
            global $argv;
            $tokens = array_slice($argv, 1);

            if (empty($tokens)) {
                throw new RuntimeException("Command name is required !!");
            }

            $commandName = (string)$tokens[0];
            $commandTokens = array_slice($tokens, 1);

            $commandParameters = [];
            $currentCommandName = null;

            for ($i = 0; $i < sizeof($commandTokens); $i++) {
                $commandToken = $commandTokens[$i];

                if (substr($commandToken, 0, 2) == '--') {
                    if ($currentCommandName != null) {
                        $commandParameters[$currentCommandName] = true;
                    }
                    $currentCommandName = substr($commandToken, 2);
                }
                else {
                    if ($currentCommandName == null) {
                        throw new RuntimeException("Invalid argument \"$commandToken\". Arguments must start with \"--\"");
                    }
                    $commandParameters[$currentCommandName] = $commandToken;
                    $currentCommandName = null;
                }
            }

            if ($currentCommandName != null) {
                $commandParameters[$currentCommandName] = true;
            }

            self::executeCommand($commandName, $commandParameters);
        }
        catch (CommandNotFoundException $exception) {
            get_logger()->warning($exception->getMessage());
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
            $commandsBaseNamespace = get_property("cli.commands_base_namespace");
            if (!empty($commandsBaseNamespace)) {
                $commandAction = $commandsBaseNamespace;
                $commandTokens = explode(".", $commandName);
                $commandTokensSize = sizeof($commandTokens);
                if ($commandTokensSize > 1) {
                    for ($i = 0; $i < $commandTokensSize - 1; $i++) {
                        $commandToken = $commandTokens[$i];
                        $commandAction .= '\\';
                        $commandAction .= str_replace(' ', '', ucwords(str_replace('_', ' ', $commandToken)));
                    }
                }
                $commandAction .= '\\';
                $commandAction .= str_replace(' ', '', ucwords(str_replace('_', ' ', $commandTokens[$commandTokensSize - 1])));
                $commandAction .= get_property("cli.commands_suffix", "Command");
            }
            else {
                throw new CommandNotFoundException("Command \"$commandName\" not found !!");
            }
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
                throw new CommandNotFoundException("Command \"$commandName\" not found !!. Command class \"$commandAction\" not found !! ", 0, $ex);
            }
        }
        return $result;
    }
}