<?php

require_once ("app/views/HTMLView.php");

class BootstrapHTMLView extends HTMLView
{
    private $responsive = true;
    
    public function setResponsive ($responsive)
    {
        $this->responsive = $responsive;
    }
    
    protected function build() 
    {
        $this->headTag->add (new Tag("meta", array("name"=>"viewport", "content"=>"width=device-width, initial-scale=1.0")));
        $this->addScriptFile("http://code.jquery.com/jquery.min.js");
        $this->addScriptFile("assets/bootstrap/js/bootstrap.min.js");
        $this->addStyleFile("assets/bootstrap/css/bootstrap.min.css");
        $this->addStyles ();
        if ($this->responsive)
            $this->addStyleFile("assets/bootstrap/css/bootstrap-responsive.min.css");    
    }
    
    protected function addStyles () {}
}

?>
