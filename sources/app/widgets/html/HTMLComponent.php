<?php

require_once ("app/widgets/html/HTMLElement.php");

abstract class HTMLComponent implements HTMLElement, HTMLViewListener
{
    private $builded;
    protected $view;
    protected $settings;
    protected $htmlElement;
    
    public final function __construct (HTMLView $view, $settings=array()) 
    {
        $this->builded = false;
        $this->settings = array_merge($this->getDefaultSettings(), $settings);
        $this->htmlElement = null;
        $this->view = $view;
        $this->view->addListener($this);
    }
    
    public final function toHtml ($offset=0)
    {
        return !empty($this->htmlElement)? $this->htmlElement->toHtml($offset) : "";
    }
    
    public final function onViewBuild (HTMLView $view)
    {
        if (!$this->builded)
        {
            $this->buildComponent();
            $this->builded = true;
        }
    }
    
    protected final function setHTMLElement (HTMLElement $element)
    {
        $this->htmlElement = $element;
    }
        
    protected final function addStyleFile ($styleFile, $hash=null)
    {
        $this->view->addStyleFile($styleFile, $hash);
    }
    
    protected final function addStyle ($style, $hash=null)
    {
        $this->view->addStyle($style, $hash);
    }

    protected final function addScriptFile ($scriptFile, $hash=null)
    {
        $this->view->addScriptFile($scriptFile, $hash);
    }

    protected final function addScript ($script, $hash=null)
    {
        $this->view->addScript($script, $hash);
    }
    
    protected final function addOnLoadScript ($onLoadScript, $hash=null)
    {
        $this->view->addOnLoadScript($onLoadScript, $hash);
    }
    
    protected function getDefaultSettings ()
    {
        return array ();
    }
    
    protected abstract function buildComponent ();
}

?>