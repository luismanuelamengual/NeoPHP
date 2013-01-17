<?php

abstract class Controller
{ 
    public function executeAction($action, $params=array())
    {
        $executeAction = $this->onBeforeActionExecution($action);
        if ($executeAction === true)
        {
            $actionFunction = $action . "Action";
            if (method_exists($this, $actionFunction))
            {
                $actionParameters = array();
                $r = new ReflectionMethod($this, $actionFunction);
                $methodParams = $r->getParameters();
                foreach ($methodParams as $methodParam)
                {
                    $parameterValue = null;
                    if (isset($params[$methodParam->getName()]))
                        $parameterValue = $params[$methodParam->getName()];
                    else if (isset($_REQUEST[$methodParam->getName()]))
                        $parameterValue = $_REQUEST[$methodParam->getName()];
                    $actionParameters[] = $parameterValue;
                }
                call_user_func_array(array($this, $actionFunction), $actionParameters);
            }
            else
            {
                $this->defaultAction();
            }
            $this->onAfterActionExecution($action);
        }
    }
    
    public function onBeforeActionExecution ($action)
    {   
        return true;
    }
    
    public function onAfterActionExecution ($action)
    {
    }
    
    public function defaultAction ()
    {
    }
}

?>