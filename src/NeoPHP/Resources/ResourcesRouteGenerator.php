<?php

namespace NeoPHP\Resources;

use NeoPHP\Http\Request;
use NeoPHP\Routing\Route;
use NeoPHP\Routing\RouteGenerator;

class ResourcesRouteGenerator implements RouteGenerator {

    public function generateRoute($method, array $path) : ?Route {
        $route = null;
        if (!empty($path)) {
            $resourceName = implode(".", array_filter($path));
            $resourceManager = Resource::get($resourceName)->getManager();
            if ($resourceManager != null && !($resourceManager instanceof DefaultResourceManager)) {
                $resourcesControllerClassName = ResourceController::class;
                $contentType = get_request()->header("Content-Type");
                if ("application/sql" == $contentType) {
                    $resourceMethodName = "queryResources";
                } else {
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
                }
                $resourceAction = $resourcesControllerClassName . "@" . $resourceMethodName;
                $route = new Route($resourceAction, ["resourceName"=>$resourceName], true);
            }
        }
        return $route;
    }
}