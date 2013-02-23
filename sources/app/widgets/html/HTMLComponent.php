<?php

require_once ("app/widgets/html/HTMLElement.php");

abstract class HTMLComponent implements HTMLElement
{
    private $created = false;
    protected $view;
    protected $attributes;
    protected $component;
    
    public function __construct(HTMLView $view, $attributes=array()) 
    {
        $this->view = $view;
        $this->attributes = $attributes;
    }
    
    public function toHtml($offset=0)
    {
        if (!$this->created)
        {
            $this->component = $this->createComponent();
            $this->created = true;
        }
        return !empty($this->component)? $this->component->toHtml($offset) : "";
    }
        
    protected function addStyleFile ($styleFile, $hash=null)
    {
        $this->view->addStyleFile($styleFile, $hash);
    }
    
    protected function addStyle ($style, $hash=null)
    {
        $this->view->addStyle($style, $hash);
    }

    protected function addScriptFile ($scriptFile, $hash=null)
    {
        $this->view->addScriptFile($scriptFile, $hash);
    }

    protected function addScript ($script, $hash=null)
    {
        $this->view->addScript($script, $hash);
    }
    
    protected function addOnLoadScript ($onLoadScript, $hash=null)
    {
        $this->view->addOnLoadScript($onLoadScript, $hash);
    }
    
    protected function createComponent ();
}

?>