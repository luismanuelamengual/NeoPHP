<?php

require_once ("app/utils/WebUtils.php");

class AjaxController extends Controller
{
    public function getContentsAction ($url, $method="GET", $contentType="application/x-www-form-urlencoded", $content=null, $timeout=null, $proxy=null)
    {
        if (substr($url, 0, 7) == "http://")
        {
            if (empty($method))
                $method = $_SERVER["REQUEST_METHOD"];
            if (empty($contentType))
                $contentType = $_SERVER["CONTENT_TYPE"];
            if (empty($content))
                $content = file_get_contents('php://input');
            if (empty($timeout))
                $timeout = 180;
            $result = WebUtils::getContents($url, $method, $contentType, $content, $timeout, $proxy);
            if (is_array($result['header']))
                foreach ($result['header'] as $header)
                    header(str_replace("HTTP/1.1","HTTP/1.0",$header));
            if ($result['data'] != false)
                echo $result['data'];
        }
    }
}

?>
