<?php

class InstitutionalSiteController extends Controller
{   
    public function showHomeAction ()
    {
        App::getInstance()->createView('institutionalSite/home')->render();
    }
    
    public function showLoginAction ()
    {
        App::getInstance()->createView('institutionalSite/login')->render();
    }
    
    public function showLoginErrorAction ()
    { 
        $view = App::getInstance()->getView('institutionalSite/login');
        $view->showLoginError(true);
        $view->render();
    }
    
    public function showAboutUsAction ()
    {
        App::getInstance()->createView('institutionalSite/aboutUs')->render();
    }
    
    public function showServicesAction ()
    {
        App::getInstance()->createView('institutionalSite/services')->render();
    }
    
    public function showContactUsAction ()
    {
        App::getInstance()->createView('institutionalSite/contactUs')->render();
    }
    
    public function sendMessageAction ($name, $email, $website, $message)
    {
        $view = App::getInstance()->createView('institutionalSite/contactUs');
        $view->setMessageSent (true);
        $view->render();
    }
    
    public function loginAction ()
    {
        App::getInstance()->executeAction('session/start');
        if (App::getInstance()->getSession()->isStarted())    
            App::getInstance()->redirectAction('site/showHome');
        else
            App::getInstance()->redirectAction('institutionalSite/showLoginError');
    }
}

?>
