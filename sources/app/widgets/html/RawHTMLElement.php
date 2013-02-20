<?php

require_once ("app/widgets/html/HTMLElement.php");

class RawHTMLElement implements HTMLElement
{
    private $html = null;
    
    public function __construct($html=null)
    {
        $this->html = $html;
    }
    
    public function setHtml ($html)
    {
        $this->html = $html;
    }
    
    public function getHtml ()
    {
        return $this->html;
    }
    
    public function toHtml($offset=0)
    {
        $html = "";
        if ($offset > 0)
            $html .= str_repeat(" ", $offset);
        $html .= $this->html;
        return $html;
    }
}

?>
