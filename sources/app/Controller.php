<?php

abstract class Controller
{ 
    public final function executeAction($action, $params=array())
    {
        $returnValue = false;
        if (empty($action) || $action == "")
            $action = "default";
        $actionFunction = $action . "Action";
        if (method_exists($this, $actionFunction))
        {
            if ($this->onBeforeActionExecution($action) === true)
            {
                $actionParameters = array();
                $controllerMethod = new ReflectionMethod($this, $actionFunction);
                foreach ($controllerMethod->getParameters() as $parameter)
                {
                    $parameterName = $parameter->getName();
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
                $this->onAfterActionExecution($action);
            }
        }
        else
        {
            throw new Exception('No se ha encontrado el metodo "' . $actionFunction . '" en el controlador "' . get_class($this) . '"');
        }
        return $returnValue;
    }
    
    protected function onBeforeActionExecution ($action)
    {   
        return true;
    }
    
    protected function onAfterActionExecution ($action)
    {
    }
}

?>
