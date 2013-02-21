<?php

abstract class HTMLComponent
{
    private $created = false;
    protected $attributes;
    protected $componentTag;
    protected $componentAggregates;
    
    public function __construct($attributes=array()) 
    {
        $this->attributes = $attributes;
        $this->componentTag = null;
        $this->componentAggregates = new stdClass();
        $this->componentAggregates->styleFiles = array();
        $this->componentAggregates->styles = array();
        $this->componentAggregates->scriptFiles = array();
        $this->componentAggregates->scripts = array();
        $this->componentAggregates->onLoadScripts = array();
    }
    
    public function getComponent ()
    {
        if (!$this->created)
        {
            $this->componentTag = $this->createComponent();
            $this->created = true;
        }
        return $this->componentTag;
    }
    
    public function getComponentAggregates ()
    {
        return $this->componentAggregates;
    }
    
    protected function addStyleFile ($styleFile)
    {
        array_push($this->componentAggregates->styleFiles, $styleFile);
    }
    
    protected function addStyle ($style)
    {
        array_push($this->componentAggregates->styles, $style);
    }

    protected function addScriptFile ($scriptFile)
    {
        array_push($this->componentAggregates->scriptFiles, $scriptFile);
    }

    protected function addScript ($script)
    {
        array_push($this->componentAggregates->scripts, $script);
    }
    
    protected function addOnLoadScript ($onLoadScript)
    {
        array_push($this->componentAggregates->onLoadScripts, $onLoadScript);
    }
    
    protected function createComponent ()
    {
        return null;
    }
}

?>
