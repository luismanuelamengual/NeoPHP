<?php

class WebUtils
{
    public static function getContents ($url, $method="GET", $contentType="application/x-www-form-urlencoded", $content=null, $timeout=null, $proxy=null)
    {
        $options = array();
        $options['http'] = array();
        $options['http']['method'] = $method;
        $headers = array();
        $headers[] = "Connection: close";
        
        if ($proxy)
            $options['http']['proxy'] = $proxy;
        if ($timeout != null)
            $options['http']['timeout'] = $timeout;
        if ($content != null)
        {            
            $headers[] = "Content-type: " . $contentType;
            $headers[] = "Content-Length: " . strlen ($content);
            $options['http']['content'] = $content;
        }
        $options['http']['header'] = implode("\r\n", $headers);
        $result = @file_get_contents($url, false, stream_context_create($options));
        $header = $http_response_header;
        list($http, $statusCode, $statusDescription) = explode(" ", $header[0]);
        $data = array();
        $data['header'] = $header;
        $data['statusCode'] = $statusCode;
        $data['data'] = $result;
        return $data;
    }
}

?>
