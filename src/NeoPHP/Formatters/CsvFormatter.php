<?php

namespace NeoPHP\Formatters;

use RuntimeException;

class CsvFormatter extends Formatter {

    public function format($content) {
        if (!is_array($content) && isset($content->data) && !is_array($content->data)) {
            throw new RuntimeException("Content must be an array to be formatted as CSV !!");
        }
        if (is_object($content)) {
            $content = $content->data;
        }
        $csvContent = "";
        $isFirstLine = true;
        foreach ($content as $contentLine) {
            if (!$isFirstLine) {
                $csvContent .= "\n";
            }
            $isFirstElement = true;
            foreach ($contentLine as $element) {
                if (!$isFirstElement) {
                    $csvContent .= ";";
                }
                $csvElement = $element;
                if (strpos($csvElement, ";")) {
                    $csvElement = "\"" . str_replace("\"", "\"\"", $csvElement) . "\"";
                }
                $csvContent .= $csvElement;
                $isFirstElement = false;
            }
            $isFirstLine = false;
        }

        $fileName = get_request("fileName", "csv") . "_" . floor(microtime(true)) . ".csv";
        $response = get_response ();
        $response->content($csvContent);
        $response->contentType ("text/csv; charset=utf-8");
        $response->header ("Content-Disposition", "attachment; filename=$fileName");
    }
}