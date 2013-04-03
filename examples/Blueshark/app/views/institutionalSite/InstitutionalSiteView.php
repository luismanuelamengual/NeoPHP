<?php

require_once ("NeoPHP/views/HTMLView.php");

class InstitutionalSiteView extends HTMLView
{
    protected $contentTag;
    protected $headerTag;
    protected $footerTag;
    
    protected function build()
    {
        $this->buildHead();
        $this->buildBody();
        $this->addStyleFile ("css/styles.css");
    }
    
    protected function buildHead ()
    {
        $this->headTag->add(new Tag("title", array(), App::getInstance()->getPreferences()->title));
        $this->headTag->add(new Tag("meta", array("http-equiv"=>"content-type", "content"=>"text/html; charset=UTF-8")));
        $this->headTag->add(new Tag("meta", array("name"=>"language", "content"=>"es")));
        $this->headTag->add(new Tag("meta", array("name"=>"description", "content"=>"")));
        $this->headTag->add(new Tag("meta", array("name"=>"keywords", "content"=>"")));
        $this->headTag->add(new Tag("meta", array("name"=>"viewport", "content"=>"width=device-width, initial-scale=1.0")));
        $this->headTag->add(new Tag("meta", array("name"=>"apple-mobile-web-app-capable", "content"=>"yes")));
        $this->headTag->add(new Tag("meta", array("name"=>"apple-touch-fullscreen", "content"=>"yes")));
    }
    
    protected function buildBody ()
    {
        $this->bodyTag->add($this->createPage());
    }
    
    protected function createPage ()
    {
        $page = new Tag("div", array("class"=>"page"));
        $page->add(new Tag("div", array("id"=>"header"), $this->createHeaderContent()));
        $page->add(new Tag("div", array("id"=>"body"), $this->createBodyContent()));
        $page->add(new Tag("div", array("id"=>"footer"), $this->createFooterContent()));
        return $page;
    }
    
    protected function createHeaderContent ()
    {
        $this->headerTag = new Tag("div", array("id"=>"headerContent"));
        $this->headerTag->add($this->createTitle());
        $this->headerTag->add($this->createNavBar());
        return $this->headerTag;
    }
    
    protected function createBodyContent ()
    {
        $this->contentTag = new Tag("div", array("id"=>"bodyContent"));
        return $this->contentTag;
    }
    
    protected function createFooterContent ()
    {            
        $this->footerTag = new Tag("div", array("id"=>"footerContent"));
        $this->footerTag->add(new Tag("div", array("id"=>"copyright"), "&copy; Copyright 2013. " . App::getInstance()->getPreferences()->title . " - Todos los derechos reservados"));
        return $this->footerTag;
    }
    
    protected function createTitle ()
    {
        return new Tag("h1", array(), new Tag("a", array("href"=>"#", "class"=>"title"), App::getInstance()->getPreferences()->title));
    }
    
    protected function createNavBar ()
    {
        $navBar = new Tag("nav", array("class"=>"navBar"));
        $navBar->add($this->createHomeLink());
        $navBar->add($this->createAboutUsLink());
        $navBar->add($this->createServicesLink());
        $navBar->add($this->createContactUsLink());
        $navBar->add($this->createLoginLink());
        return $navBar;
    }
    
    protected function createHomeLink ()
    {
        return new Tag("a", array("href"=>App::getInstance()->getUrl("institutionalSite/showHome")), "Inicio");
    }
    
    protected function createAboutUsLink ()
    {
        return new Tag("a", array("href"=>App::getInstance()->getUrl("institutionalSite/showAboutUs")), "Empresa");
    }
    
    protected function createServicesLink ()
    {
        return new Tag("a", array("href"=>App::getInstance()->getUrl("institutionalSite/showServices")), "Servicios");
    }
    
    protected function createContactUsLink ()
    {
        return new Tag("a", array("href"=>App::getInstance()->getUrl("institutionalSite/showContactUs")), "Contacto");
    }
    
    protected function createLoginLink ()
    {
        return new Tag("a", array("href"=>App::getInstance()->getUrl("institutionalSite/showLogin")), "Iniciar Sesión");
    }
}

?>
