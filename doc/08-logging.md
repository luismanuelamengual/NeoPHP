# Logging

Logging in NeoPHP is powered by [monolog](https://github.com/Seldaek/monolog). The configuration file for logging can be found in the logging.php file in the config directory. The logging.php configuration documentation can be found in the monolog extension [monolog-cascade](https://github.com/theorchard/monolog-cascade). This configuration file looks like this ...
```PHP
<?php

return [

    "default" => "main",

    'loggers' => [
        'main' => [
            'handlers' => ["rotating_file_handler"]
        ]
    ],

    "handlers" => [
        "rotating_file_handler" => [
            "class" => "Monolog\Handler\RotatingFileHandler",
            "level" => "DEBUG",
            "formatter" => "main_formatter",
            "filename" => get_app()->storagePath() . DIRECTORY_SEPARATOR . "logs" . DIRECTORY_SEPARATOR . "{date}",
            "maxFiles" => 30,
            "set_filename_format" => ["{date}.log", "Y-m-d"]
        ]
    ],

    "formatters" => [
        "main_formatter" => [
            "class" => "Monolog\Formatter\LineFormatter",
            "format" => "[%datetime%] %channel%.%level_name%: %message%\n",
            'include_stacktraces' => true
        ]
    ]
];
```
Many loggers can be configured with the "loggers" property and the default logger can be configured with the "default" property.

In NeoPHP we can obtain a monolog logger with the **get_logger** function like this
```PHP
get_logger();                   //Returns the default logger
get_logger("users")             //Returns the logger named "users"
get_logger(UserManager::class)  //Returns the logger name as the UserManager class
```
These are the availiable levels to log
```PHP
get_logger()->debug("...");
get_logger()->info("...");
get_logger()->notice("...");
get_logger()->warning("...");
get_logger()->alert("...");
get_logger()->error("...");
get_logger()->critical("...");
get_logger()->emergency("...");
```