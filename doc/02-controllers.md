# Controllers

Controllers can be **any class in the "src" folder**. These controllers are places where we are going to put the business logic. This is an example of a simple controller that writes "hello world" in the browser ...

```PHP
<?php

namespace MyApp;

class HelloWorldController {
    
    public function sayHello () {
        echo "Hello world !!";
    }
}
```
We can access controllers with the class "Controllers" like this
```PHP
Controllers::get( $controllerClass )
```
So, accessing the method sayHello in the HelloWorldController can be achieved in the following way
```PHP
Controllers::get(HelloWorldController::class)->sayHello();
```

## Accesing controller actions from the application
We can also **execute controller actions** from an application instance. This is done in this way ...
```PHP
get_app()->execute("MyApp\HelloWorldController@sayHello");
```
If you dont specify any method for the controller then the **default method "index" will be executed**. Example:
```PHP
get_app()->execute("MyApp\HelloWorldController");
```
Its also possible to **pass arguments to the controller methods**. If we modify the controller a bit like this ...
```PHP
<?php

namespace MyApp;

class HelloWorldController {
    
    public function sayHello ($name) {
        echo "Hi $name, hello world !!";
    }
}
```
Then its possible to pass the name parameters as follows ...
```PHP
get_app()->execute("MyApp\HelloWorldController@sayHello", ["name"=>"Luis"]);
```
The **Boot controller actions** (Actions that are executed on every php request) can be configured in the configuration file **"app.php" in the config folder** with the **bootActions** property. Suppose we want to execute our sayHello method on every php request then the app.php configuration file could look like this ...
```PHP
<?php

return [

    "debug"=>false,

    "bootActions"=> [
        "MyApp\HelloWorldController@sayHello"
    ]
];
```
