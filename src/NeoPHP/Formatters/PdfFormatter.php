<?php

namespace NeoPHP\Formatters;

use Dompdf\Dompdf;
use Dompdf\Options;
use Sitrack\Views\View;
use stdClass;

class PdfFormatter extends Formatter {

    public function format($content) {
        if ($content instanceof View) {
            $content = $content->render(true);
        }
        else if ($content instanceof stdClass || is_array($content)) {
            $content = var_export($content, true);
        }

        $options = new Options();
        $options->setIsRemoteEnabled(true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($content);
        $dompdf->setPaper('A4', get_request("paperType", 'portrait'));
        $dompdf->render();
        $canvas = $dompdf->getCanvas();
        $canvas->page_text($canvas->get_width() - 60, $canvas->get_height() - 30, "{PAGE_NUM} / {PAGE_COUNT}", null, 10, array(0, 0, 0));
        if (get_request("filename")) {
            $dompdf->stream(get_request("filename") . ".pdf");
        }
        else {
            $dompdf->stream();
        }
    }
}