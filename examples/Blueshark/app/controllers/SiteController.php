<?php

class SiteController extends Controller
{   
    public function onBeforeActionExecution ($action)
    {
        $executeAction = App::getInstance()->getSession()->isStarted() && isset(App::getInstance()->getSession()->sessionId);
        if (!$executeAction)
            App::getInstance()->redirectAction("institutionalSite/showHome");
        return $executeAction;
    }
    
    public function logoutAction ()
    {
        App::getInstance()->executeAction('session/destroy');
        App::getInstance()->redirectAction('institutionalSite/showHome');
    }
}

?>
