<?php

require_once ("app/widgets/Tag.php");

class HTMLView implements View
{
    private $builded = false;
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
    
    protected function addStyleFile ($styleFile)
    {
        $this->headTag->add(new Tag("link", array("rel"=>"stylesheet", "type"=>"text/css", "href"=>$styleFile)));
    }
    
    protected function addStyle ($style)
    {
        $this->headTag->add(new Tag("style", array("type"=>"text/css"), $style));
    }

    protected function addScriptFile ($scriptFile)
    {
        $this->htmlTag->add(new Tag("script", array("type"=>"text/javascript", "src"=>$scriptFile), ""));
    }

    protected function addScript ($script)
    {
        $this->htmlTag->add(new Tag("script", array("type"=>"text/javascript"), $script));
    }
    
    protected function onViewBuilded()
    {
    }
}

?>
