<?php

require_once ("app/widgets/html/HTMLElement.php");

abstract class HTMLComponent implements HTMLElement
{
    protected $attributes;
    protected $htmlElement;
    
    public final function __construct ($attributes=array()) 
    {
        $this->attributes = array_merge($this->getDefaultAttributes(), $attributes);
        $this->htmlElement = null;
    }
    
    public final function toHtml ($offset=0)
    {
        return !empty($this->htmlElement)? $this->htmlElement->toHtml($offset) : "";
    }
    
    public final function build (HTMLView $view)
    {
        $this->setHTMLElement($this->createHTMLElement($view));
    }
    
    private function setHTMLElement (HTMLElement $htmlElement=null)
    {
        $this->htmlElement = $htmlElement;
    }
    
    protected function getDefaultAttributes ()
    {
        return array ();
    }
    
    protected abstract function createHTMLElement (HTMLView $view);
}

?>