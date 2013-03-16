<?php

require_once ("app/widgets/html/Tag.php");
require_once ("app/widgets/html/HTMLComponent.php");

class HTMLView implements View
{
    private $innerData;
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
        $this->innerData = new stdClass();
        $this->innerData->hashes = array();
    }
    
    public final function render()
    {
        if (empty($this->innerData->builded))
        {
            $this->build();
            $this->innerData->builded = true;
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
        if (!empty($this->innerData->titleTag))
        {
            $this->innerData->titleTag->setContent ($title);
        }
        else
        {
            $this->innerData->titleTag = new Tag("title", array(), $title);
            $this->headTag->insert ($this->innerData->titleTag, 0);
            $this->innerData->metasOffset = empty($this->innerData->metasOffset)? 1 : ($this->innerData->metasOffset+1);
        }
    }
    
    public final function addMeta ($attributes)
    {
        if (empty($this->innerData->metasOffset))
            $this->innerData->metasOffset = 0;
        $this->headTag->insert(new Tag("meta", $attributes), ($this->innerData->metasOffset++));
    }
    
    public final function addStyleFile ($styleFile, $hash=null)
    {
        if ($hash == null)
            $hash = md5($styleFile);
        if (!in_array($hash, $this->innerData->hashes))
        {
            $this->headTag->add(new Tag("link", array("rel"=>"stylesheet", "type"=>"text/css", "href"=>$styleFile)));
            $this->innerData->hashes[] = $hash;
        }
    }
    
    public final function addStyle ($style, $hash=null)
    {
        if ($hash == null)
            $hash = md5($style);
        if (!in_array($hash, $this->innerData->hashes))
        {
            $this->headTag->add(new Tag("style", array("type"=>"text/css"), $style));
            $this->innerData->hashes[] = $hash;
        }
    }

    public final function addScriptFile ($scriptFile, $hash=null)
    {
        if ($hash == null)
            $hash = md5($scriptFile);
        if (!in_array($hash, $this->innerData->hashes))
        {
            $this->htmlTag->add(new Tag("script", array("type"=>"text/javascript", "src"=>$scriptFile), ""));
            $this->innerData->hashes[] = $hash;
        }
    }

    public final function addScript ($script, $hash=null)
    {
        if ($hash == null)
            $hash = md5($script);
        if (!in_array($hash, $this->innerData->hashes))
        {
            $this->htmlTag->add(new Tag("script", array("type"=>"text/javascript"), $script));
            $this->innerData->hashes[] = $hash;
        }
    }
    
    public final function addOnLoadScript ($script, $hash=null)
    {
        if ($hash == null)
            $hash = md5($script);
        if (!in_array($hash, $this->innerData->hashes))
        {
            $onLoadScript = $this->bodyTag->getAttribute("onload");
            if (empty($onLoadScript))
                $onLoadScript = "";
            $onLoadScript .= $script;
            $this->bodyTag->setAttribute("onload", $onLoadScript);
            $this->innerData->hashes[] = $hash;
        }
    }
    
    protected function build() {}
}

?>
