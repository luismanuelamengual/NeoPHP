<?php

class InstitutionalSiteController extends Controller
{   
    public function showHomeAction ()
    {
        App::getInstance()->getView('institutionalSite/home')->render();
    }
    
    public function showLoginAction ()
    {
        App::getInstance()->getView('institutionalSite/login')->render();
    }
    
    public function showLoginErrorAction ()
    { 
        $view = App::getInstance()->getView('institutionalSite/login');
        $view->showLoginError(true);
        $view->render();
    }
    
    public function showAboutUsAction ()
    {
        App::getInstance()->getView('institutionalSite/aboutUs')->render();
    }
    
    public function showServicesAction ()
    {
        App::getInstance()->getView('institutionalSite/services')->render();
    }
    
    public function showContactUsAction ()
    {
        App::getInstance()->getView('institutionalSite/contactUs')->render();
    }
    
    public function sendMessageAction ($name, $email, $website, $message)
    {
        $view = App::getInstance()->getView('institutionalSite/contactUs');
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
