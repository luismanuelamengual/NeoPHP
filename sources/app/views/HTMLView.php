<?php

require_once ("app/widgets/html/Tag.php");
require_once ("app/widgets/html/HTMLComponent.php");

class HTMLView implements View
{
    private $builded = false;
    private $hashes = array();
    protected $docTypeDeclaration;
    protected $htmlTag;
    protected $bodyTag;
    protected $headTag;
    
    public function __construct ()
    {
    }
    
    public function render()
    {
        if (!$this->builded)
        {
            $this->build();
            $this->onViewBuilded();
            $this->builded = true;
        }
        echo $this->docTypeDeclaration . "\n" . $this->htmlTag->toHtml();
    }
    
    protected function build()
    {
        $this->docTypeDeclaration = '<!DOCTYPE html>';
        $this->htmlTag = new Tag("html");
        $this->bodyTag = new Tag("body");
        $this->headTag = new Tag("head");
        $this->htmlTag->add($this->headTag);
        $this->htmlTag->add($this->bodyTag);
    }
    
    protected function addStyleFile ($styleFile, $hash=null)
    {
        if ($hash == null)
            $hash = md5($styleFile);
        if (!in_array($hash, $this->hashes))
        {
            $this->headTag->add(new Tag("link", array("rel"=>"stylesheet", "type"=>"text/css", "href"=>$styleFile)));
            array_push($this->hashes, $hash);
        }
    }
    
    protected function addStyle ($style, $hash=null)
    {
        if ($hash == null)
            $hash = md5($style);
        if (!in_array($hash, $this->hashes))
        {
            $this->headTag->add(new Tag("style", array("type"=>"text/css"), $style));
            array_push($this->hashes, $hash);
        }
    }

    protected function addScriptFile ($scriptFile, $hash=null)
    {
        if ($hash == null)
            $hash = md5($scriptFile);
        if (!in_array($hash, $this->hashes))
        {
            $this->htmlTag->add(new Tag("script", array("type"=>"text/javascript", "src"=>$scriptFile), ""));
            array_push($this->hashes, $hash);
        }
    }

    protected function addScript ($script, $hash=null)
    {
        if ($hash == null)
            $hash = md5($script);
        if (!in_array($hash, $this->hashes))
        {
            $this->htmlTag->add(new Tag("script", array("type"=>"text/javascript"), $script));
            array_push($this->hashes, $hash);
        }
    }
    
    protected function addOnLoadScript ($script, $hash=null)
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
    
    protected function onViewBuilded()
    {
    }
}

?>
