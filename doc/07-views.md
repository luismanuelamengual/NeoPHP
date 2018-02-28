# Views

NeoPHP actually **supports 3 view templates engines: Blade, Twig and Smarty**. Availiable view factories as well as the default one can be configured in the views.php configuration file inside de config directory. This is the content of the default views configuration file ... 
```PHP
<?php

return [

    "default" => "blade",

    "factories" => [
        "blade" => [
            "class" => NeoPHP\Views\Blade\BladeViewFactory::class
        ],
        "twig" => [
            "class" => NeoPHP\Views\Twig\TwigViewFactory::class
        ],
        "smarty" => [
            "class" => NeoPHP\Views\Smarty\SmartyViewFactory::class
        ]
    ]
];
```
The default view factory is "blade" but the developer can change this with the "default" property

The developer can create a view with the "create_view" function. Example: 
```PHP
create_view ("welcome");
```
In this case NeoPHP (with blade) will search for the file "welcome.blade.php" in the "resources/views" directory. If the developer creates the following view ...
```PHP
create_view ("portal.welcome");
```
.. then the NeoPHP will search for the file "welcome.blade.php" but inside the directory "resources/views/portal" 

Passing parameters to the views is possible in several ways. In the create_view function ...
```PHP
create_view ("welcome", ["name"=>"Luis", "lastName"=>"Amengual");
```
or through the "set" method after creating the view
```PHP
$view = create_view ("welcome");
$view->set("name", "Luis");
$view->set("lastName", "Amengual");
```
Rendering a view can be achieved through the "render" method but if the developer is creating a view from a route actions (controller action or clousure) then the routes handler will be responsible of rendering the view if its correctly returned by the action. Example
```PHP
Routes::get("welcome", function() {
    return create_view("welcome", ["name"=>"Luis"]);
});
```