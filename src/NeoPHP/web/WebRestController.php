<?php

namespace NeoPHP\web;

abstract class WebRestController extends WebController
{
    public function executeAction($action, array $parameters = []) 
    {
        $restAction = "";
        switch ($this->getRequest()->getMethod())
        {
            case "GET":
                $restAction = "getResource";
                break;
            case "PUT":
                $restAction = "createResource";
                break;
            case "POST":
                $restAction = "updateResource";
                break;
            case "DELETE":
                $restAction = "deleteResource";
                break;
        }
        return parent::executeAction($restAction, array_merge(["id"=>$action], $parameters));
    }
}