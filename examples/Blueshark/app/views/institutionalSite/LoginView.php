<?php

require_once ("app/views/institutionalSite/InstitutionalSiteView.php");

class LoginView extends InstitutionalSiteView
{
    private $showLoginError = false;
    
    protected function createLoginLink ()
    {
        $loginLink = parent::createLoginLink();
        $loginLink->setAttribute("class", "current-page-item");
        return $loginLink;
    }
    
    public function showLoginError ($showLoginError)
    {
        $this->showLoginError = $showLoginError;
    }
    
    protected function createBodyContent ()
    {
        $contentDiv = parent::createBodyContent();
        $contentDiv->add($this->createLoginPanel());
        return $contentDiv;
    }
    
    protected function createLoginPanel ()
    {
        $formPanel = new Tag("div", array("id"=>"loginForm", "class"=>"form"));
        $formPanel->add(new Tag("div", array("class"=>"headerRow"), "<span>Iniciar Sesión</span>"));
        $form = new Tag("form", array("method"=>"post", "action"=>App::getInstance()->getUrl("institutionalSite/login"), "autocomplete"=>"on"));
        $form->add(new Tag("div", array("class"=>"fieldRow"), new Tag("input", array("id"=>"username", "name"=>"username", "type"=>"text", "placeholder"=>"Nombre de usuario", "autofocus"=>"autofocus"))));
        $form->add(new Tag("div", array("class"=>"fieldRow"), new Tag("input", array("id"=>"password", "name"=>"password", "type"=>"password", "placeholder"=>"Contraseña"))));
        if ($this->showLoginError)
            $form->add(new Tag("div", array("class"=>"alertRow"), new Tag("p", array(), "Nombre de usuario o contraseña incorrecta !!")));
        $form->add(new Tag("div", array("class"=>"buttonsRow"), new Tag("input", array("class"=>"button", "type"=>"submit", "value"=>"Ingresar"))));
        $formPanel->add($form);        
        return $formPanel;
    }
}

?>
