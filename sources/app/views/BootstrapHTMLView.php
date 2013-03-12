<?php

require_once ("app/views/HTMLView.php");

class BootstrapHTMLView extends HTMLView
{
    protected function build() 
    {
        $this->headTag->add (new Tag("meta", array("name"=>"viewport", "content"=>"width=device-width, initial-scale=1.0")));
        $this->headTag->add (new Tag("link", array("href"=>"assets/bootstrap/css/bootstrap.min.css", "rel"=>"stylesheet", "media"=>"screen")));
        $this->addScriptFile("http://code.jquery.com/jquery.min.js");
        $this->addScriptFile("assets/bootstrap/js/bootstrap.min.js");
    }
}

?>
