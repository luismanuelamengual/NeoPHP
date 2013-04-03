<?php

require_once ("app/views/institutionalSite/InstitutionalSiteView.php");

class ServicesView extends InstitutionalSiteView 
{
    protected function createServicesLink ()
    {
        $aboutUsLink = parent::createServicesLink();
        $aboutUsLink->setAttribute("class", "current-page-item");
        return $aboutUsLink;
    }
    
    protected function createBodyContent ()
    {
        $contentTag = parent::createBodyContent ();
        $contentTag->add($this->createSection1());
        $contentTag->add($this->createSection2());
        $contentTag->add($this->createSection3());
        $contentTag->add($this->createSection4());
        $contentTag->add($this->createSection5());
        $contentTag->add($this->createSection6());
        return $contentTag;
    }
    
    protected function createSection1 ()
    {
        $title = new Tag("h2", array(), "Lorem ipsum #1");
        $text = new Tag("p", array(), "Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.");
        return new Tag("div", array("class"=>"section span4"), array($title, $text));
    }
    
    protected function createSection2 ()
    {
        $title = new Tag("h2", array(), "Lorem ipsum #2");
        $text = new Tag("p", array(), "Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.");
        return new Tag("div", array("class"=>"section span4"), array($title, $text));
    }
    
    protected function createSection3 ()
    {
        $title = new Tag("h2", array(), "Lorem ipsum #3");
        $text = new Tag("p", array(), "Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.");
        return new Tag("div", array("class"=>"section span4"), array($title, $text));
    }
    
    protected function createSection4 ()
    {
        $title = new Tag("h2", array(), "Lorem ipsum #4");
        $text = new Tag("p", array(), "Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.");
        return new Tag("div", array("class"=>"section span4"), array($title, $text));
    }
    
    protected function createSection5 ()
    {
        $title = new Tag("h2", array(), "Lorem ipsum #5");
        $text = new Tag("p", array(), "Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.");
        return new Tag("div", array("class"=>"section span4"), array($title, $text));
    }
    
    protected function createSection6 ()
    {
        $title = new Tag("h2", array(), "Lorem ipsum #6");
        $text = new Tag("p", array(), "Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.");
        return new Tag("div", array("class"=>"section span4"), array($title, $text));
    }
}

?>
