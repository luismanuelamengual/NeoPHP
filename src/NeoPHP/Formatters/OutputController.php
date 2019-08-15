<?php

namespace NeoPHP\Formatters;

use RuntimeException;

/**
 * Class OutputController
 * @package NeoPHP\Controllers
 * @author Luis Manuel Amengual <luis.amengual@sitrack.com>
 */
class OutputController {

    const OUTPUT_FORMAT_PARAMETER_NAME = "outputFormat";

    /**
     * Procesamiento de la salida de recursos web
     * Formatea la salida en funcion del formato de salida especificado
     */
    public function formatOutput () {
        $request = get_request();
        if ($request->has(self::OUTPUT_FORMAT_PARAMETER_NAME)) {
            $output = $request->get(self::OUTPUT_FORMAT_PARAMETER_NAME);

            $outputParts = explode(".", $output);
            $formatterClassName = __NAMESPACE__;
            foreach ($outputParts as $outputPart) {
                $outputPart = str_replace(' ', '', ucwords(str_replace('_', ' ', $outputPart)));
                $formatterClassName .= "\\" . ucfirst($outputPart);
            }
            $formatterClassName .= "Formatter";

            $formatter = new $formatterClassName;
            if ($formatter == null) {
                throw new RuntimeException("Formatter class \"$formatterClassName\" not found !!");
            }
            if (!is_subclass_of($formatterClassName, Formatter::class)) {
                throw new RuntimeException("Class \"$formatterClassName\" is not a subclass of \"" . Formatter::class . "\" !!");
            }
            $response = get_response();
            $content = $response->content();
            if (empty($content)) {
                $content = ob_get_clean();
            }
            $response->content("");
            return $formatter->format($content);
        }
    }
}