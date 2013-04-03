<?php

class MainController extends Controller
{
    public function defaultAction ()
    {
        App::getInstance()->executeAction("institutionalSite/showHome");
    }
}

?>
