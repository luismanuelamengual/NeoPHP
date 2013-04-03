<?php

require_once ("app/views/institutionalSite/InstitutionalSiteView.php");

class ContactUsView extends InstitutionalSiteView 
{
    private $messageSent = false;
    
    protected function createContactUsLink ()
    {
        $contactUsLink = parent::createContactUsLink();
        $contactUsLink->setAttribute("class", "current-page-item");
        return $contactUsLink;
    }
    
    public function setMessageSent ($messageSent)
    {
        $this->messageSent = $messageSent;
    }
    
    protected function createBodyContent ()
    {
        $contentDiv = parent::createBodyContent();
        $contentDiv->add($this->createInfoPanel());
        return $contentDiv;
    }
    
    protected function createInfoPanel ()
    {
        $formPanel = new Tag("div", array("id"=>"contactUsForm", "class"=>"form"));
        $formPanel->add(new Tag("div", array("class"=>"headerRow"), "<span>Contáctenos</span>"));
        if (!$this->messageSent)
        {
            $form = new Tag("form", array("method"=>"post", "action"=>App::getInstance()->getUrl("institutionalSite/sendMessage"), "autocomplete"=>"on"));
            $form->add(new Tag("div", array("class"=>"fieldRow"), new Tag("input", array("id"=>"name", "name"=>"name", "type"=>"text", "placeholder"=>"Nombre", "autofocus"=>"autofocus"))));
            $form->add(new Tag("div", array("class"=>"fieldRow"), new Tag("input", array("id"=>"email", "name"=>"email", "type"=>"text", "placeholder"=>"EMail"))));
            $form->add(new Tag("div", array("class"=>"fieldRow"), new Tag("input", array("id"=>"website", "name"=>"website", "type"=>"text", "placeholder"=>"Sitio Web"))));
            $form->add(new Tag("div", array("class"=>"fieldRow"), new Tag("textarea", array("id"=>"message", "name"=>"message", "placeholder"=>"Mensaje"), "")));
            $form->add(new Tag("div", array("class"=>"buttonsRow"), array(new Tag("input", array("class"=>"button", "type"=>"submit", "value"=>"Enviar mensaje")))));
            $formPanel->add($form);
        }
        else
        {
            $formPanel->add(new Tag("p", array(), "Mensaje enviado correctamente !!"));
        }
        return $formPanel;
    }
}

?>
