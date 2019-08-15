<?php

namespace NeoPHP;

use Dotenv\Dotenv;
use Dotenv\Environment\Adapter\EnvConstAdapter;
use Dotenv\Environment\Adapter\PutenvAdapter;
use Dotenv\Environment\DotenvFactory;
use ErrorException;
use Exception;
use NeoPHP\Console\Commands;
use NeoPHP\Controllers\Controllers;
use NeoPHP\Http\Response;
use NeoPHP\Mail\Mailer;
use NeoPHP\Routing\Routes;
use NeoPHP\Utils\StringUtils;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;

/**
 * Class Application
 * @package NeoPHP\Core
 */
class Application {

    private static $instance;

    private $basePath;
    private $publicPath;
    private $storagePath;
    private $resourcesPath;
    private $configPath;

    private $modules = [];
    private $language = "es";
    private $timeZone;

    /**
     * Creates a new application
     * @param $basePath
     * @return Application
     */
    public static function create(string $basePath): Application {
        self::$instance = new Application($basePath);
        return self::$instance;
    }

    /**
     * Returns the application instance
     * @return Application
     */
    public static function get(): Application {
        return self::$instance;
    }

    /**
     * Application constructor.
     * @param $basePath
     */
    private function __construct(string $basePath) {
        $this->basePath($basePath);
        set_error_handler(array($this, "handleError"), E_ALL | E_STRICT);
        set_exception_handler(array($this, "handleException"));
    }

    /**
     * Set or retrieve the language of the application
     * @param string|null $language
     * @return null|string
     */
    public function language(string $language = null): ?string {
        if (!is_null($language)) {
            $this->language = $language;
        }
        return $this->language;
    }

    /**
     * Set or retrieve the timezone of the application
     * @param DateTimeZone|null $timeZone
     * @return DateTimeZone|null
     */
    public function timeZone(DateTimeZone $timeZone = null): ?DateTimeZone {
        if (!is_null($timeZone)) {
            $this->timeZone = $timeZone;
        }
        return $this->timeZone;
    }

    /**
     * Set or returns the application base path
     * @param string|null $basePath
     * @return mixed
     */
    public function basePath(?string $basePath = null) {
        if ($basePath != null) {
            $this->basePath = $basePath;
            if (file_exists("$basePath/.env")) {
                $factory = new DotenvFactory([
                    new EnvConstAdapter(),
                    new PutenvAdapter(),
                ]);
                Dotenv::create($basePath, null, $factory)->load();
            }
        }
        return $this->basePath;
    }

    /**
     * Set or returns the storage path
     * @param string|null $storagePath
     * @return mixed
     */
    public function storagePath(?string $storagePath = null) {
        if ($storagePath != null) {
            $this->storagePath = $storagePath;
        }
        else if (!isset($this->storagePath)) {
            $this->storagePath = get_property("app.storage_path", $this->basePath . DIRECTORY_SEPARATOR . "storage");
        }
        return $this->storagePath;
    }

    /**
     * Set or returns the resources path
     * @param string|null $resourcesPath
     * @return mixed
     */
    public function resourcesPath(?string $resourcesPath = null) {
        if ($resourcesPath != null) {
            $this->resourcesPath = $resourcesPath;
        }
        else if (!isset($this->resourcesPath)) {
            $this->resourcesPath = get_property("app.resources_path", $this->basePath . DIRECTORY_SEPARATOR . "resources");
        }
        return $this->resourcesPath;
    }

    /**
     * Set or returns the config path
     * @param string|null $configPath
     * @return null|string
     */
    public function configPath(?string $configPath = null) {
        if ($configPath != null) {
            $this->configPath = $configPath;
        }
        else {
            if (!isset($this->configPath)) {
                $this->configPath = $this->basePath() . DIRECTORY_SEPARATOR . "config";
            }
        }
        return $this->configPath;
    }

    /**
     * @param null $publicPath
     * @return null|string
     */
    public function publicPath($publicPath = null) {
        if ($publicPath != null) {
            $this->publicPath = $publicPath;
        }
        else {
            if (!isset($this->publicPath)) {
                $this->publicPath = getcwd();
            }
        }
        return $this->publicPath;
    }

    /**
     * Adds a new module to the application
     * @param Module $module module to add
     */
    public function addModule (Module $module) {
        $this->modules[] = $module;
    }

    /**
     * Initializes the application
     * @throws Exception
     */
    public function start() {
        foreach ($this->modules as $module) {
            $module->start();
        }

        if (php_sapi_name() == "cli") {
            Commands::handleCommand();
        }
        else {
            Routes::handleRequest();
        }
    }

    /**
     * Executes an action
     * @param mixed $action action to execute
     * @param array $parameters
     * @return mixed|null
     * @throws Exception
     */
    public function execute($action, array $parameters = []) {
        $result = null;
        if (is_string($action)) {
            $actionParts = explode("@", $action);
            $controllerClass = $actionParts[0];
            $controllerMethodName = sizeof($actionParts) > 1 ? $actionParts[1] : "index";
            $controller = Controllers::get($controllerClass);
            if ($controller == null) {
                throw new ActionNotFoundException ( "Controller \"$controllerClass\" not found !!");
            }
            if (method_exists($controller, $controllerMethodName)) {
                $controllerMethodParams = $this->getParameterValues(new ReflectionMethod($controller, $controllerMethodName), $parameters);
                $result = call_user_func_array([$controller, $controllerMethodName], $controllerMethodParams);
            }
            else {
                throw new ActionNotFoundException ("Method \"$controllerMethodName\" not found in controller \"$controllerClass\" !!");
            }
        }
        else if (is_callable($action)) {
            $actionParams = $this->getParameterValues(new ReflectionFunction($action), $parameters);
            $result = call_user_func_array($action, $actionParams);
        }
        return $result;
    }

    /**
     * Returns the parameters values for a function
     * @param ReflectionMethod|ReflectionFunction $function
     * @param array $parameters
     * @return array
     * @throws \ReflectionException
     */
    private function getParameterValues ($function, array $parameters = []) {
        $functionParams = [];
        $parameterIndex = 0;
        foreach ($function->getParameters() as $parameter) {
            $parameterName = $parameter->getName();
            $parameterValue = null;

            if (array_key_exists($parameterName, $parameters)) {
                $parameterValue = $parameters[$parameterName];
            }
            else if ($parameter->hasType()) {
                $type = $parameter->getType();
                if (!$type->isBuiltin()) {
                    $typeName = (string)$type;
                    if (array_key_exists($typeName, $parameters)) {
                        $parameterValue = $parameters[$typeName];
                    }
                    else if (!$parameter->isDefaultValueAvailable()) {
                        $typeClass = new ReflectionClass($typeName);
                        foreach ($typeClass->getMethods(ReflectionMethod::IS_STATIC) as $staticMethod) {
                            if ($staticMethod->getReturnType() != null && ((string)$staticMethod->getReturnType() == $typeName) && $staticMethod->getNumberOfParameters() == 0) {
                                $parameterValue = $staticMethod->invoke(null);
                                break;
                            }
                        }
                    }
                }
            }

            if ($parameterValue == null) {
                if (array_key_exists($parameterIndex, $parameters)) {
                    $parameterValue = $parameters[$parameterIndex];
                    $parameterIndex++;
                }
                else if ($parameter->isDefaultValueAvailable()) {
                    $parameterValue = $parameter->getDefaultValue();
                }
            }
            $functionParams[] = $parameterValue;
        }
        return $functionParams;
    }

    /**
     * Handles an application error
     * @param int $errno error number
     * @param string $errstr error message
     * @param string $errfile error file
     * @param int $errline error file line
     * @param string $errcontext error context
     * @throws ErrorException
     */
    public function handleError($errno, $errstr, $errfile, $errline, $errcontext) {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    /**
     * Handles an application exception
     * @param Exception $ex exepción
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function handleException ($ex) {

        $inDebugMode = get_property("app.debug") || get_request("debug");

        $code = $ex->getCode() >= Response::HTTP_BAD_REQUEST ? $ex->getCode(): Response::HTTP_INTERNAL_SERVER_ERROR;

        if (get_property("app.log_errors")) {
            //mejorar el log de la app
            $log = [
                "exception" => $ex->getTraceAsString(),
                "code" => $ex->getCode(),
                "line" => $ex->getLine(),
                "file" => $ex->getFile(),
                "trace" => $ex->getTrace(),
                "previous" => !empty($ex->getPrevious()) ? $ex->getPrevious()->getMessage(): null,
            ];
            if ($code < Response::HTTP_INTERNAL_SERVER_ERROR) {
                get_logger()->info($ex->getMessage(), $log);
            } else {
                get_logger()->error($ex->getMessage(), $log);
            }
        }

        if (!$inDebugMode && $code >= Response::HTTP_INTERNAL_SERVER_ERROR && get_property("app.email_errors")) {
            $recipients = get_property("app.email_error_recipients");
            if (!empty($recipients)) {
                $emailContent = '';
                $emailContent .= '<b><u>Error report</u></b>';
                $emailContent .= "<br><br>";
                $emailContent .= get_class($ex);
                $emailContent .= ": ";
                $emailContent .= $ex->getMessage();
                $emailContent .= " in file ";
                $emailContent .= $ex->getFile();
                $emailContent .= " on line ";
                $emailContent .= $ex->getLine();
                $emailContent .= "<br><br>";
                $emailContent .= "<b>Stack trace: </b>";
                $emailContent .= "<br>";
                $frames = $ex->getTrace();
                $lineNumber = 1;
                foreach ($frames as $frame) {
                    $emailContent .= "<br>";
                    $emailContent .= ($lineNumber++);
                    $emailContent .= ". ";
                    if (!empty($frame["class"])) {
                        $emailContent .= $frame["class"];
                    }
                    if (!empty($frame["type"])) {
                        $emailContent .= $frame["type"];
                    }
                    if (!empty($frame["function"])) {
                        $emailContent .= $frame["function"];
                    }
                    $emailContent .= "() ";
                    $emailContent .= "<u><span style=\"color:#4288CE;\">";
                    if (!empty($frame["file"])) {
                        $emailContent .= $frame["file"];
                    }
                    if (!empty($frame["line"])) {
                        $emailContent .= ":";
                        $emailContent .= $frame["line"];
                    }
                    $emailContent .= "</span></u>";
                }

                $emailContent .= "<br>";

                if (!empty($_SESSION)) {
                    $emailContent .= "<br>";
                    $emailContent .= "<b>Session: </b>";
                    $emailContent .= "<pre>";
                    $emailContent .= print_r($_SESSION, true);
                    $emailContent .= "</pre>";
                }

                if (!empty($_REQUEST)) {
                    $emailContent .= "<br>";
                    $emailContent .= "<b>Request: </b>";
                    $emailContent .= "<pre>";
                    $emailContent .= print_r($_REQUEST, true);
                    $emailContent .= "</pre>";
                }

                if (!empty($_COOKIE)) {
                    $emailContent .= "<br>";
                    $emailContent .= "<b>Cookies: </b>";
                    $emailContent .= "<pre>";
                    $emailContent .= print_r($_COOKIE, true);
                    $emailContent .= "</pre>";
                }

                if (!empty($_SERVER)) {
                    $dataServer = [];
                    foreach ($_SERVER as $index => $data) {
                        if (StringUtils::contains($index, "PASSWORD") || StringUtils::contains($index, "KEY")) continue;
                        $dataServer[$index] = $data;
                    }
                    $emailContent .= "<br>";
                    $emailContent .= "<b>Server: </b>";
                    $emailContent .= "<pre>";
                    $emailContent .= print_r($dataServer, true);
                    $emailContent .= "</pre>";
                }

                if (!empty($_ENV)) {
                    $dataEnv = [];
                    foreach ($_ENV as $index => $data) {
                        if (StringUtils::contains($index, "PASSWORD") || StringUtils::contains($index, "KEY")) continue;
                        $dataEnv[$index] = $data;
                    }
                    $emailContent .= "<br>";
                    $emailContent .= "<b>Enviroment: </b>";
                    $emailContent .= "<pre>";
                    $emailContent .= print_r($dataEnv, true);
                    $emailContent .= "</pre>";
                }

                $mailer = Mailer::create();
                foreach ($recipients as $recipient) {
                    $mailer->addAddress($recipient);
                }
                $mailer->Subject = "Error report";
                $mailer->Body = $emailContent;
                $mailer->send();
            }
        }

        if (php_sapi_name() == "cli") {
            $whoops = new \Whoops\Run;
            $whoops->pushHandler(new \Whoops\Handler\PlainTextHandler());
            $whoops->handleException($ex);
        }
        else {
            $accept = isset($_SERVER["HTTP_ACCEPT"]) ? $_SERVER["HTTP_ACCEPT"] : "";
            $acceptedFormats = explode(",", $accept);
            $acceptHtml = in_array("text/html", $acceptedFormats);

            if ($inDebugMode) {
                $whoops = new \Whoops\Run;
                if ($acceptHtml) {
                    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
                }
                else {
                    $whoops->pushHandler(new \Whoops\Handler\PlainTextHandler());
                }
                $whoops->handleException($ex);
            }
            else {
                http_response_code($code);
                if ($acceptHtml) {
                    include __DIR__ . DIRECTORY_SEPARATOR . "errorPage.php";
                }
                else {
                    $response = new stdClass();
                    $response->success = false;
                    $response->code = $code;
                    $response->message = $ex->getMessage();
                    echo json_encode($response);
                }
            }
        }
        exit;
    }
}