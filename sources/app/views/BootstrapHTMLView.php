<?php

require_once ("app/views/HTMLView.php");

abstract class BootstrapHTMLView extends HTMLView
{
    private $responsive = true;
    
    public final function setResponsive ($responsive)
    {
        $this->responsive = $responsive;
    }
    
    protected final function build() 
    {
        $this->headTag->add (new Tag("meta", array("name"=>"viewport", "content"=>"width=device-width, initial-scale=1.0")));
        $this->addScriptFile("http://code.jquery.com/jquery.min.js");
        $this->addScriptFile("assets/bootstrap/js/bootstrap.min.js");
        $this->addStyleFile("assets/bootstrap/css/bootstrap.min.css");
        $this->buildView ();
        if ($this->responsive)
            $this->addStyleFile("assets/bootstrap/css/bootstrap-responsive.min.css");    
    }
    
    protected abstract function buildView ();
}

?>