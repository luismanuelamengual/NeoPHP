<?php

class SessionController extends Controller
{   
    public function startAction ($username, $password)
    {
        App::getInstance()->getSession()->destroy();
        $connection = App::getInstance()->getConnection("production");
        $doUser = $connection->getDataObject("User");
        $doUser->addWhereCondition ('username="' . $username . '"');
        $doUser->addWhereCondition ('password="' . $password . '"');
        if ($doUser->find(true))
        {
            App::getInstance()->getSession()->startSession();
            App::getInstance()->getSession()->sessionId = session_id();
            App::getInstance()->getSession()->sessionName = session_name();
            App::getInstance()->getSession()->userName = $doUser->username;
            App::getInstance()->getSession()->firstName = $doUser->firstname;
            App::getInstance()->getSession()->lastName = $doUser->lastname;
            App::getInstance()->getSession()->clientId = $doUser->clientid;
            App::getInstance()->getSession()->profileId = $doUser->profileid;
        }
    }
    
    public function destroyAction ()
    {
        App::getInstance()->getSession()->destroy();
    }
}

?>
