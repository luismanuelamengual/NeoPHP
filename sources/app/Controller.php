<?php

abstract class Controller
{ 
    public function executeAction($action, $params=array())
    {
        $returnValue = FALSE;
        $executeAction = $this->onBeforeActionExecution($action);
        if ($executeAction === true)
        {
            $actionFunction = $action . "Action";
            if (empty($action) || $action == "")
            {
                $this->defaultAction();
            }
            else if (method_exists($this, $actionFunction))
            {
                $actionParameters = array();
                $controllerMethod = new ReflectionMethod($this, $actionFunction);
                $methodParams = $controllerMethod->getParameters();
                foreach ($methodParams as $methodParam)
                {
                    $parameterName = $methodParam->getName();
                    $parameterValue = null;
                    if (isset($params[$parameterName]))
                        $parameterValue = $params[$parameterName];
                    else if (isset($_REQUEST[$parameterName]))
                        $parameterValue = $_REQUEST[$parameterName];
                    else if ($parameterName == "php_input")
                        $parameterValue = @file_get_contents("php://input");
                    $actionParameters[] = $parameterValue;
                }
                $returnValue = call_user_func_array(array($this, $actionFunction), $actionParameters);
            }
            else
            {
                throw new Exception('No se ha encontrado la función "' . $actionFunction . '" en el controlador "' . get_class($this) . '"');
            }
            $this->onAfterActionExecution($action);
        }
        return $returnValue;
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
