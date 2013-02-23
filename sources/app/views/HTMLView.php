<?php

require_once ("app/widgets/html/Tag.php");
require_once ("app/widgets/html/HTMLComponent.php");

class HTMLView implements View
{
    private $builded = false;
    private $hashes = array();
    protected $docTypeDeclaration;
    protected $htmlTag;
    protected $headTag;
    protected $bodyTag;
    
    public function __construct ()
    {
        $this->docTypeDeclaration = '<!DOCTYPE html>';
        $this->htmlTag = new Tag("html");
        $this->headTag = new Tag("head");
        $this->bodyTag = new Tag("body");
        $this->htmlTag->add($this->headTag);
        $this->htmlTag->add($this->bodyTag);
    }
    
    public function render()
    {
        if (!$this->builded)
        {
            $this->build();
            $this->onBuilded();
            $this->builded = true;
        }
        echo $this->docTypeDeclaration . "\n" . $this->htmlTag->toHtml();
    }
    
    public function getHtmlTag ()
    {
        return $this->htmlTag;
    }
    
    public function getHeadTag ()
    {
        return $this->htmlTag;
    }
    
    public function getBodyTag ()
    {
        return $this->htmlTag;
    }
    
    public function addStyleFile ($styleFile, $hash=null)
    {
        if ($hash == null)
            $hash = md5($styleFile);
        if (!in_array($hash, $this->hashes))
        {
            $this->headTag->add(new Tag("link", array("rel"=>"stylesheet", "type"=>"text/css", "href"=>$styleFile)));
            array_push($this->hashes, $hash);
        }
    }
    
    public function addStyle ($style, $hash=null)
    {
        if ($hash == null)
            $hash = md5($style);
        if (!in_array($hash, $this->hashes))
        {
            $this->headTag->add(new Tag("style", array("type"=>"text/css"), $style));
            array_push($this->hashes, $hash);
        }
    }

    public function addScriptFile ($scriptFile, $hash=null)
    {
        if ($hash == null)
            $hash = md5($scriptFile);
        if (!in_array($hash, $this->hashes))
        {
            $this->htmlTag->add(new Tag("script", array("type"=>"text/javascript", "src"=>$scriptFile), ""));
            array_push($this->hashes, $hash);
        }
    }

    public function addScript ($script, $hash=null)
    {
        if ($hash == null)
            $hash = md5($script);
        if (!in_array($hash, $this->hashes))
        {
            $this->htmlTag->add(new Tag("script", array("type"=>"text/javascript"), $script));
            array_push($this->hashes, $hash);
        }
    }
    
    public function addOnLoadScript ($script, $hash=null)
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
    
    protected function build() {}
    protected function onBuilded() {}
}

?>
