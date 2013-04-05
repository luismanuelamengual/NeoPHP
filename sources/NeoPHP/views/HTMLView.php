<?php

require_once ("NeoPHP/widgets/html/Tag.php");
require_once ("NeoPHP/widgets/html/HTMLComponent.php");

class HTMLView implements View
{
    protected $docType;
    protected $htmlTag;
    protected $headTag;
    protected $bodyTag;
    
    public final function __construct ()
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
        if (empty($this->builded))
        {
            $this->build();
            $this->builded = true;
        }
        echo $this->docType . "\n" . $this->htmlTag->toHtml();
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
    
    public final function setTitle ($title)
    {
        if (!empty($this->titleTag))
        {
            $this->titleTag->setContent ($title);
        }
        else
        {
            $this->titleTag = new Tag("title", array(), $title);
            $this->headTag->insert ($this->titleTag, 0);
            $this->metasOffset = empty($this->metasOffset)? 1 : ($this->metasOffset+1);
        }
    }
    
    public final function addMeta ($attributes)
    {
        if (empty($this->metasOffset))
            $this->metasOffset = 0;
        $this->headTag->insert(new Tag("meta", $attributes), ($this->metasOffset++));
    }
    
    public final function addStyleFile ($styleFile, $hash=null)
    {
        if ($hash == null)
            $hash = md5($styleFile);
        if (!isset($this->styleFileHashes[$hash]))
        {
            $this->headTag->add(new Tag("link", array("rel"=>"stylesheet", "type"=>"text/css", "href"=>$styleFile)));
            $this->styleFileHashes[$hash] = true;
        }
    }
    
    public final function addStyle ($style, $hash=null)
    {
        if ($hash == null)
            $hash = md5($style);
        if (!isset($this->styleHashes[$hash]))
        {
            $this->headTag->add(new Tag("style", array("type"=>"text/css"), $style));
            $this->styleHashes[$hash] = true;
        }
    }

    public final function addScriptFile ($scriptFile, $hash=null)
    {
        if ($hash == null)
            $hash = md5($scriptFile);
        if (!isset($this->scriptFileHashes[$hash]))
        {
            $this->htmlTag->add(new Tag("script", array("type"=>"text/javascript", "src"=>$scriptFile), ""));
            $this->scriptFileHashes[$hash] = true;
        }
    }

    public final function addScript ($script, $hash=null)
    {
        if ($hash == null)
            $hash = md5($script);
        if (!isset($this->scriptHashes[$hash]))
        {
            $this->htmlTag->add(new Tag("script", array("type"=>"text/javascript"), $script));
            $this->scriptHashes[$hash] = true;
        }
    }
    
    public final function addOnLoadScript ($script, $hash=null)
    {
        if ($hash == null)
            $hash = md5($script);
        if (!isset($this->onLoadScriptHashes[$hash]))
        {
            $onLoadScript = $this->bodyTag->getAttribute("onload");
            if (empty($onLoadScript))
                $onLoadScript = "";
            $onLoadScript .= $script;
            $this->bodyTag->setAttribute("onload", $onLoadScript);
            $this->onLoadScriptHashes[$hash] = true;
        }
    }
    
    protected function build() {}
}

?>
