<?php

namespace NeoPHP\cli;

use NeoPHP\mvc\MVCApplication;

class CLIApplication extends MVCApplication
{  
    public function handleCommand () 
    {   
        $arguments = $GLOBALS["argv"];
        $actionName = !empty($arguments[1])? $arguments[1] : "main";
        $actionParams = array();
        for ($i = 2; $i < sizeof($arguments); $i++)
            $actionParams[] = $arguments[$i];
        $this->processAction($actionName, $actionParams);
    }
}

?>