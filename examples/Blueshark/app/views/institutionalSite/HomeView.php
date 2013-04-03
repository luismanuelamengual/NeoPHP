<?php

require_once ("app/views/institutionalSite/InstitutionalSiteView.php");

class HomeView extends InstitutionalSiteView
{
    protected function build()
    {
        parent::build();
        $this->addScriptFile("js/jquery.min.js");
        $this->addScriptFile("js/jquery.slides.min.js"); 
        $this->addScript('
            $(function()
            {
                $("#slider").slides(
                {
                    preload: true,
                    preloadImage: "images/institutionalSite/home/slides/loading.gif",
                    play: 5000,
                    pause: 2500,
                    hoverPause: true,
                    generatePagination: false
                });
            });
        ');
    }
    
    protected function createHomeLink ()
    {
        $homeLink = parent::createHomeLink();
        $homeLink->setAttribute("class", "current-page-item");
        return $homeLink;
    }
    
    protected function createPage ()
    {
        $page = parent::createPage();
        $page->insert($this->createBanner(), 1);
        return $page;
    }
    
    protected function createBanner ()
    {
        return new Tag("div", array("id"=>"banner"), new Tag("div", array("id"=>"bannerContent"), $this->createSlider()));
    }
    
    protected function createSlider ()
    {        
        $slidesContainer = new Tag("div", array("class"=>"slides_container"));
        $slidesContainer->add ($this->createSlide("What is Lorem Ipsum ?", "Lorem Ipsum is simply dummy text of the printing and typesetting industry.", "images/institutionalSite/home/slides/slide1.jpg"));
        $slidesContainer->add ($this->createSlide("Why do we use it ?", "It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout.", "images/institutionalSite/home/slides/slide2.jpg"));
        $slidesContainer->add ($this->createSlide("Where does it come from ?", "Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC", "images/institutionalSite/home/slides/slide3.jpg"));
        $slidesContainer->add ($this->createSlide("Where can i get some ?", "There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form.", "images/institutionalSite/home/slides/slide4.jpg"));
        return new Tag("div", array("id"=>"slider"), array($slidesContainer));
    }
    
    protected function createSlide ($title, $description, $image = "", $imageAlt = "")
    {   
        $image = new Tag("img", array("src"=>$image, "width"=>940, "height"=>270, "alt"=>$imageAlt));
        $title = new Tag("div", array("class"=>"title"), new Tag("p", array(), $title));
        $caption = new Tag("div", array("class"=>"description"), new Tag("p", array(), $description));
        return new Tag("div", array("class"=>"slide"), array($image, $title, $caption));
    }
    
    protected function createBodyContent ()
    {
        $contentTag = parent::createBodyContent ();
        $contentTag->add($this->createSection1());
        $contentTag->add($this->createSection2());
        return $contentTag;
    }
    
    protected function createSection1 ()
    {
        $title = new Tag("h2", array(), "Finibus Bonorum et Malorum");
        $text = new Tag("p", array(), "Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt.");
        $readMoreButton = new Tag("a", array("href"=>App::getInstance()->getUrl("institutionalSite/showAboutUs"), "class"=>"button"), "Leer más");
        return new Tag("div", array("class"=>"section span6"), array($title, $text, $readMoreButton));
    }
    
    protected function createSection2 ()
    {
        $title = new Tag("h2", array(), "1914 translation by H. Rackham");
        $text = new Tag("p", array(), "But I must explain to you how all this mistaken idea of denouncing pleasure and praising pain was born and I will give you a complete account of the system, and expound the actual teachings of the great explorer of the truth, the master-builder of human happiness. No one rejects, dislikes, or avoids pleasure itself, because it is pleasure, but because those who do not know how to pursue pleasure rationally encounter consequences that are extremely painful.");
        $readMoreButton = new Tag("a", array("href"=>App::getInstance()->getUrl("institutionalSite/showServices"), "class"=>"button"), "Leer más");
        return new Tag("div", array("class"=>"section span6"), array($title, $text, $readMoreButton));
    }
}

?>
