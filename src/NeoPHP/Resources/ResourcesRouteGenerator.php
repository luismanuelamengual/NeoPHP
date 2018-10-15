<?php

namespace NeoPHP\Resources;

use NeoPHP\Http\Request;
use NeoPHP\Routing\Route;
use NeoPHP\Routing\RouteGenerator;

class ResourcesRouteGenerator implements RouteGenerator {

    public function generateRoute($method, array $path) : ?Route {
        $route = null;
        $path = array_filter($path);
        if (!empty($path)) {
            $resourcesControllerClassName = ResourceController::class;
            switch ($method) {
                case Request::METHOD_GET:
                    $resourceMethodName = "findResources";
                    break;
                case Request::METHOD_PUT:
                    $resourceMethodName = "insertResource";
                    break;
                case Request::METHOD_POST:
                    $resourceMethodName = "updateResource";
                    break;
                case Request::METHOD_DELETE:
                    $resourceMethodName = "deleteResource";
                    break;
            }
            $resourceAction = $resourcesControllerClassName . "@" . $resourceMethodName;
            $resourceName = implode(".", $path);
            $route = new Route($resourceAction, ["resourceName"=>$resourceName]);
        }
        return $route;
    }
}