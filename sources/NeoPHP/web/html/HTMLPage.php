<?php

namespace NeoPHP\web\html;

use NeoPHP\web\WebView;

class HTMLPage extends WebView implements HTMLElement
{
    protected $htmlHeader;
    protected $htmlTag;
    protected $headTag;
    protected $bodyTag;
    
    public function __construct ()
    {
        $this->htmlHeader = '<!DOCTYPE html>';
        $this->htmlTag = new HTMLTag("html");
        $this->headTag = new HTMLTag("head");
        $this->bodyTag = new HTMLTag("body");
        $this->htmlTag->add($this->headTag);
        $this->htmlTag->add($this->bodyTag);
    }
    
    public final function onRender()
    {
        echo $this->toHtml();
    }
    
    public final function toHtml()
    {
        if (empty($this->builded))
        {
            $this->build();
            $this->postBuild();
            $this->builded = true;
        }
        
        $html = "";
        if (!empty($this->htmlHeader))
            $html .= $this->htmlHeader . "\n";
        $html .= $this->htmlTag->toHtml();
        return $html;
    }
    
    public final function addElement ($element)
    {
        if (!isset($this->elements))
            $this->elements = [];
        $this->elements[] = $element;
    }
    
    protected function build() {}
    
    protected final function postBuild()
    {
        if (isset($this->elements))
        {
            foreach ($this->elements as $element)
            {
                if ($element instanceof HTMLComponent)
                {
                    $element->build ($this, $this->bodyTag);
                }
                else if ($element instanceof HTMLElement)
                {
                    $this->bodyTag->add($element->toHtml());
                }
                else
                {
                    $this->bodyTag->add(strval($element));
                }
            }
        }
        
        if (isset($this->metaTags))
            foreach ($this->metaTags as $metaTag)
                $this->headTag->add ($metaTag);
        if (isset($this->baseTag))
            $this->headTag->add($this->baseTag);
        if (isset($this->titleTag))
            $this->headTag->add($this->titleTag);
        if (isset($this->styleFileTags))
            foreach ($this->styleFileTags as $styleFileTag)
                $this->headTag->add ($styleFileTag);
        if (isset($this->styleTags))
            foreach ($this->styleTags as $styleTag)
                $this->headTag->add ($styleTag);
        if (isset($this->scriptFileTags))
            foreach ($this->scriptFileTags as $scriptFileTag)
                $this->htmlTag->add ($scriptFileTag);
        if (isset($this->scriptTags))
            foreach ($this->scriptTags as $scriptTag)
                $this->htmlTag->add ($scriptTag);
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
            $this->titleTag = new HTMLTag("title", [], $title);
        }
    }
    
    public final function setBaseUrl ($baseUrl)
    {
        if (!empty($this->baseTag))
        {
            $this->baseTag->setAttribute("href", $baseUrl);
        }
        else
        {
            $this->baseTag = new HTMLTag("base", ["href"=>$baseUrl]);
        }
    }
    
    public final function addMeta ($attributes)
    {
        $this->metaTags[] = new HTMLTag("meta", $attributes);
    }
    
    public final function addStyleFile ($styleFile, $hash=null)
    {
        if ($hash == null)
            $hash = md5($styleFile);
        if (!isset($this->styleFileTags[$hash]))
            $this->styleFileTags[$hash] = new HTMLTag("link", array("rel"=>"stylesheet", "type"=>"text/css", "href"=>$styleFile));
    }
    
    public final function addStyle ($style, $hash=null)
    {
        if ($hash == null)
            $hash = md5($style);
        if (!isset($this->styleTags[$hash]))
            $this->styleTags[$hash] = new HTMLTag("style", array("type"=>"text/css"), $style);
    }

    public final function addScriptFile ($scriptFile, $hash=null)
    {
        if ($hash == null)
            $hash = md5($scriptFile);
        if (!isset($this->scriptFileTags[$hash]))
            $this->scriptFileTags[$hash] = new HTMLTag("script", array("type"=>"text/javascript", "src"=>$scriptFile), "");
    }

    public final function addScript ($script, $hash=null)
    {
        if ($hash == null)
            $hash = md5($script);
        if (!isset($this->scriptTags[$hash]))
            $this->scriptTags[$hash] = new HTMLTag("script", array("type"=>"text/javascript"), $script);
    }
}

?>
