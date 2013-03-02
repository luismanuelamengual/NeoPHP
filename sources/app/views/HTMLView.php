<?php

require_once ("app/views/HTMLViewListener.php");
require_once ("app/widgets/html/Tag.php");
require_once ("app/widgets/html/HTMLComponent.php");

class HTMLView implements View
{
    private $builded = false;
    private $hashes = array();
    private $listeners = array();
    protected $docType;
    protected $htmlTag;
    protected $headTag;
    protected $bodyTag;
    
    public final function __construct ($settings = array())
    {
        $this->docType = '<!DOCTYPE html>';
        $this->htmlTag = new Tag("html");
        $this->headTag = new Tag("head");
        $this->bodyTag = new Tag("body");
        $this->htmlTag->add($this->headTag);
        $this->htmlTag->add($this->bodyTag);
    }
    
    public final function render()
    {
        if (!$this->builded)
        {
            $this->build();
            $this->fireViewBuildEvent();
            $this->builded = true;
        }
        echo $this->docType . "\n" . $this->htmlTag->toHtml();
    }
    
    public final function addListener (HTMLViewListener $listener)
    {
        array_push($this->listeners, $listener);
    }
    
    public final function getHtmlTag ()
    {
        return $this->htmlTag;
    }
    
    public final function getHeadTag ()
    {
        return $this->headTag;
    }
    
    public final function getBodyTag ()
    {
        return $this->bodyTag;
    }
    
    public final function addStyleFile ($styleFile, $hash=null)
    {
        if ($hash == null)
            $hash = md5($styleFile);
        if (!in_array($hash, $this->hashes))
        {
            $this->headTag->add(new Tag("link", array("rel"=>"stylesheet", "type"=>"text/css", "href"=>$styleFile)));
            array_push($this->hashes, $hash);
        }
    }
    
    public final function addStyle ($style, $hash=null)
    {
        if ($hash == null)
            $hash = md5($style);
        if (!in_array($hash, $this->hashes))
        {
            $this->headTag->add(new Tag("style", array("type"=>"text/css"), $style));
            array_push($this->hashes, $hash);
        }
    }

    public final function addScriptFile ($scriptFile, $hash=null)
    {
        if ($hash == null)
            $hash = md5($scriptFile);
        if (!in_array($hash, $this->hashes))
        {
            $this->htmlTag->add(new Tag("script", array("type"=>"text/javascript", "src"=>$scriptFile), ""));
            array_push($this->hashes, $hash);
        }
    }

    public final function addScript ($script, $hash=null)
    {
        if ($hash == null)
            $hash = md5($script);
        if (!in_array($hash, $this->hashes))
        {
            $this->htmlTag->add(new Tag("script", array("type"=>"text/javascript"), $script));
            array_push($this->hashes, $hash);
        }
    }
    
    public final function addOnLoadScript ($script, $hash=null)
    {
        if ($hash == null)
            $hash = md5($script);
        if (!in_array($hash, $this->hashes))
        {
            $onLoadScript = $this->bodyTag->getAttribute("onload");
            if (empty($onLoadScript))
                $onLoadScript = "";
            $onLoadScript .= $script;
            $this->bodyTag->setAttribute("onload", $onLoadScript);
            array_push($this->hashes, $hash);
        }
    }
    
    private final function fireViewBuildEvent ()
    {
        foreach ($this->listeners as $listener)
            call_user_func (array($listener, "onViewBuild"), $this);
    }
    
    protected function build() {}
}

?>
