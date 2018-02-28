# Routing

Routes are a way execute a controller method or a basic closure which matches a certain request path and method.

Basic closure routes are routes that executes a simple callback function. This is an example ...
```PHP
Routes::get("/helloworld", function () {
    echo "Hello World !!";
});
```
In this example, when we enter in the browser the url "/helloworld" then "Hello World !!" will be printed in the screen.

Other type of routes are the ones that executes controller actions. Example:
```PHP
Routes::get("/helloworld", "MyApp\HelloWorldController@sayHello");
```
If we add request parameters to the http request then the controller method can receive them as parameters. For example if run the uri **/helloworld?name=Luis** then the parameter "name" will be passed to the controller action execution and therefore this **parameter "name" will be accesible in the controller method**

These are the **availiable methods** that may be matched with routes
```PHP
Routes::get($uri, $callback);
Routes::post($uri, $callback);
Routes::put($uri, $callback);
Routes::delete($uri, $callback);
```
Its also possible to match any http method with the **any method**
```PHP
Routes::any($uri, $callback);
```
Wildcards can be used to match any path starting with a desired context. To use wildcards the * is used in the path. These are valid examples ..

```PHP
Routes::get("*", "MyApp\MainController@path");
Routes::post("test/*", "MyApp\Test\TestController");
Routes::put("/resources/users/*", function() { echo "test"; });
```
Routes with **path parameters may be declared using the : prefix** in the path. For example ..

```PHP
Routes::get("users/:userId", "MyApp\Users\UsersController@findUser");
```
Then the "userId" parameter may be accesible as a controller method parameter as follows ..
```PHP
<?php

namespace MyApp\Users;

class UsersController {
    
    public function findUser ($userId) {
        echo "Trying to find the user $userId";
    }
}
```
Registering routes that executes before or after certain routes can be achieved using the **before and after methods** as follows ...
```PHP
Routes::before("test", function() { echo "This function executes before the test route"; });
Routes::get("test", function() { echo "This is the actual route"; });
Routes::after("test", function() { echo "This execute after the test route; });
```
The **before routes are specially usefull for session validations or for input transformations**. Example: 
```PHP
Routes::before("site/*", function() { 
    if (!get_session()->isStarted()) {
        header("location: portal");
    }
});
```
In this example all requests to the context "site/" will have session validation and redirect to portal if no session is started

The **after routes are specially usefull for output transformations**. The result of the route is stored in the result parameter. This result may be modified to return another output. Example ...
```PHP
Routes::any("/persons/", function() { 
    return [{ "name"=>"Luis", "lastname"=>"Amengual", "age"=>35 }];
});
Routes::after("persons", function($result) {
    switch (get_request()->get("output")) {
        case "json":
            $result = json_encode($result);
            break;
    }
    return $result;
});
```
In this example if we run the uri "/persons?output=json" then response will be in json format

Its possible also to define error routes for certain contexts with the "error" method. The exeption is passed to the controller as follows ..
```PHP
Routes::error("resources/*", function($exception) { 
    echo "Houston we have a problem !!. Message: " . $exception->getMessage();
});
```