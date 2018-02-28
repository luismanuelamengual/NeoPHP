[![Latest Stable Version](https://img.shields.io/packagist/v/neogroup/neophp.svg)](https://packagist.org/packages/monolog/monolog)
![](https://img.shields.io/github/license/luismanuelamengual/NeoPHP.svg)
![](https://img.shields.io/github/forks/luismanuelamengual/NeoPHP.svg?style=social&label=Fork)
![](https://img.shields.io/github/stars/luismanuelamengual/NeoPHP.svg?style=social&label=Star)
![](https://img.shields.io/github/watchers/luismanuelamengual/NeoPHP.svg?style=social&label=Watch)
![](https://img.shields.io/github/followers/luismanuelamengual.svg?style=social&label=Follow)

# NeoPHP
Great PHP framework for web developers, not for web 'artisans' (wtf ??!!)

Getting started
---------------
To install the NeoPHP framework we have to **run the following command (Composer required)**, assuming we want to start a new project named "MyApp":
```
composer create-project neogroup/neophp-startup-project MyApp
```
This command will create an empty NeoPHP project. The structure of the created proyect will be as follows ...

```
MyApp                             Base application path
├─ config                         Directory where all config files are located
│  ├─ app.php                     General application configuration file
│  ├─ database.php                Database configuration file
│  ├─ logging.php                 Logging configuration file
│  ├─ resources.php               Resources configuration file
│  ├─ views.php                   Views configuration file
│  └─ models.php                  Models configuration file
├─ public                         Directory for all public resources (css, js, assets, etc)
│  ├─ bower_components            Directory for all bower components
│  ├─ components                  Directory for components (resources that have css, js, imges, etc)
│  ├─ css                         Directory for style sheet files
│  ├─ img                         Directory for images
│  ├─ js                          Directory for javascript files
│  ├─ .htaccess                   File that handles the site requests and redirect them to index.php
│  ├─ favicon.ico                 Icon the the application
│  ├─ index.php                   Starting main point for the application
│  └─ robots.txt                  Bot detector configuration file
├─ resources                      Directory for all the application resources
│  ├─ messages                    Base directory for translation bundles
│  └─ views                       Base directory for views (templates basically)
├─ src                            Base directory for source files (php classes)
├─ storage                        Base directory for all generated files
│  ├─ framework                   Directory for files that are generated by the framework
│  └─ logs                        Directory for the application logs
├─ vendor                         Base directory for composer packages
├─ .bowerrc                       Bower configuration file
├─ .gitignore                     Git ignrations file
├─ bower.json                     Bower json file for web requirements
├─ composer.json                  Composer json file for PHP requirements
├─ composer.lock                  Composer file the indicates the installed PHP dependencies
├─ LICENSE.md                     License file
└─ README.md                      Readme file
```

Now we have to **add write permissions to the folder "storage"** (this is the place where logs and compiled views are stored). In linux system you can run the following commands
```
cd MyApp
chmod 777 -R storage/
```
The next and final step is to configure the **public** directory. You should **configure your web server's document / web root to be the public directory**. The index.php in this directory serves as the front controller for all HTTP requests entering your application.

Controllers
---------------
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
We can **execute controller methods like "sayHello"** in the following way
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

Routing
---------------
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

Database
---------------
The database configuration for your application is located at config/database.php. In this file you may define all of your database connections, as well as specify which connection should be used by default.

The following is an example of the database.php configuration file in which is defined a connection named "pgsql"
```PHP
<?php

return [

    'default' => 'pgsql',

    'connections' => [
        'pgsql' => [
            'driver' => 'pgsql',
            'host' => '127.0.0.1',
            'port' => '5432',
            'database' => 'main',
            'username' => 'postgres',
            'password' => 'postgres',
            'logQueries' => true
        ]
    ]
];
```

If no connection is defined then the default connection is used. **Raw sql statements** can be executed with the **methods query and exec** of the DB class to the default connection as follows.
```PHP
DB::query($sql, array $bindings = []);
DB::exec($sql, array $bindings = []);
```

Examples
```PHP
DB::query("SELECT * FROM person");
DB::query("SELECT * FROM person WHERE age > ?", 20); 
DB::exec("INSERT INTO person (name, lastname, age) VALUES ('Luis','Amengual',20)");
```

Using **multiple connections** is possible using the connection method as follows
```PHP
DB::connection("secondary")->query("SELECT ...");
DB::connection("test")->exec("INSERT INTO ...")
```

Using transactions (Explicit way)
```PHP
DB::beginTransaction();
try {
    DB::exec("INSERT INTO person (name, lastname) VALUES (?, ?)", ["Luis", "Amengual"]);
    DB::exec("UPDATE users SET active = ? WHERE personid = ?", [1, 21]);
    DB::commit();
}
catch (Exception $ex) {
    DB::rollback();
}
```

Using transactions (Clousure way)
```PHP
DB::transaction(function() {
    $this->exec("INSERT INTO person (name, lastname) VALUES (?, ?)", ["Luis", "Amengual"]);
    $this->exec("UPDATE users SET active = ? WHERE personid = ?", [1, 21]);
});
```

Using **table connections** may be usefull to standarize sql statements. To use table conenctions the method "table" should be used as follows ..
```PHP
DB::table("person")->find();                        //SELECT * FROM person
DB::connection("mysql")->table("person")->find();   //SELECT * FROM person (but from mysql database)
```

Selecting columns
```PHP
DB::table("person")->select("name", "lastname")->find();  //SELECT name, lastname FROM person
```

Adding where conditions
```PHP
//SELECT * FROM person WHERE name = 'Luis'
DB::table("person")->where("age", "Luis")->find();                     

//SELECT * FROM person WHERE age > 20
DB::table("person")->where("age", ">", 20)->find();                

//SELECT * FROM person WHERE personid IN (100, 200)
DB::table("person")->where("personid", "in", [100,200])->find();   
```

Adding raw where conditions
```PHP
//SELECT * FROM person WHERE personid <> 20
DB::table("person")->whereRaw("personid <> ?", [20])->find();
```

Adding column and null where conditions
```PHP
//SELECT * FROM person WHERE name = lastname
DB::table("person")->whereColumn("name", "lastname")->find();

//SELECT * FROM person WHERE name IS NOT NULL
DB::table("person")->whereNotNull("name")->find();

//SELECT * FROM person WHERE name IS NULL
DB::table("person")->whereNull("name")->find();
```

Adding complex where statements
```PHP
//SELECT * FROM person WHERE age > 20 AND (name = 'Luis' OR lastname = name)
$condition = QueryBuilder::conditionGroup("or")->on("name", "Luis")->onColumn("lastname", "name"); 
DB::table("person")->where("age", ">", 20)->whereGroup($condition)->find();
```

Adding where statements with subqueries 
```PHP
//SELECT * FROM users WHERE personid IN (SELECT personid FROM person WHERE age > 20)
$subquery = QueryBuilder::selectFrom("person")->select("person")->where("age", ">", 20);
DB::table("users")->where("personid", "in", $subquery)->find();
```

Adding joins
```PHP
//SELECT * FROM user INNER JOIN person ON user.personid = person.personid 
DB::table("user")->innerJoin("person", "user.personid", "person.personid")->find();  

//SELECT * FROM user LEFT JOIN person ON user.personid = person.personid 
DB::table("user")->leftJoin("person", "user.personid", "person.personid")->find();  
```

Adding complex joins
```PHP
//SELECT * FROM user RIGHT JOIN person ON user.personid = person.personid AND person age < 20
$join = QueryBuilder::join("person", Join::TYPE_RIGHT_JOIN)
    ->onColumn("user.personid", "person.personid");
    ->on("age", "<", 20);
DB::table("user")->join($join)->find();
```

Grouping results
```PHP
//SELECT type, count(*) FROM user GROUP BY type
DB::table("user")->select("type", "count(*)")->groupBy("type")->find();
```

Limiting and offseting results
```PHP
//SELECT * FROM person LIMIT 100 OFFSET 300
DB::table("person")->limit(100)->offset(300)->find();
```

Insert queries
```PHP
//INSERT INTO person (name, age) VALUES ('Luis', 20)
DB::table("person")->set("name","Luis")->set("age",20)->insert();
```

Update queries
```PHP
//UPDATE person SET lastname = 'Amengual' WHERE personid = 12
DB::table("person")->set("lastname","Amengual")->where("personid", 12)->update();
```

Delete queries

```PHP
//DELETE FROM person WHERE age < 30
DB::table("person")->where("age","<",30)->delete();
```

Resources
---------------
**Resources** are usefull when the developer needs to **add some logic to queries** executed in the application. Resource queries have the same syntaxis as database queries and by default they execute the query over the default database connection. The the configuration file for resources is resources.php in the config folder.

The following is the syntaxis to operate over resources
```PHP
Resources::get("persons")->where("username", "pedro")->find();
```
**By default this resource query executes over the default database connection**. But the developer can override the default behavour by assigning a resource manager. This assignation is done in the resources configuration file (resources.php in the config directory) like this ...
```PHP
<?php

return [
    "managers" => [
        "persons" => \App\Person\PersonsResource::class
    ]
];
```
Now we can write the PersonsResource as follows ..
```PHP
<?php

namespace App\Persons;

use NeoPHP\Database\DB;
use NeoPHP\Database\Query\DeleteQuery;
use NeoPHP\Database\Query\InsertQuery;
use NeoPHP\Database\Query\SelectQuery;
use NeoPHP\Database\Query\UpdateQuery;
use NeoPHP\Resources\ResourceManager;

class PersonsResource extends ResourceManager {

    public function find(SelectQuery $query) {
        $query->innerJoin("usuario", "usuario.personaid", "persona.personaid");
        return DB::query($query);
    }

    public function insert(InsertQuery $query) {
        // TODO: Implement insert() method.
    }

    public function update(UpdateQuery $query) {
        // TODO: Implement update() method.
    }

    public function delete(DeleteQuery $query) {
        // TODO: Implement delete() method.
    }
}

```
Now each time we make a find over the "person" resource we will be accesing the "person" table of the default connection with a join with the "user" table

Models
---------------
In NeoPHP **a model can be any php class**. The way the developer interact with model is through the following methods
```PHP
create_model($model, array $options = [])
update_model($model, array $options = [])
delete_model($model, array $options = [])
retrieve_models($modelClass, array $options = [])
retrieve_model_by_id($modelClass, $modelId, array $options = [])
```

Lets suppose we want to operate over the following model ...
```PHP
<?php

namespace MyApp\Persons;

class Person {
    
    private $name;
    private $lastName;
    
    public function __construct($name, $lastName) {
        $this->name = $name;
        $this->lastName = $lastName;
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function getLastName() {
        return $this->lastName;
    }
}

```

Then the developer is encouraged to create a model manager for the model like this ...
```PHP
<?php

namespace MyApp\Persons;

use NeoPHP\Models\ModelManager;

class PersonManager extends ModelManager {
    
    public function create($model, array $options = []) {
        //TODO: The developer must implement the create method
    }

    public function update($model, array $options = []) {
        //TODO: The developer must implement the update method
    }

    public function delete($model, array $options = []) {
        //TODO: The developer must implement the update method
    }

    public function retrieveById ($modelId, array $options = []) {
        //TODO: The developer must implement the retrieveById method
    }

    public function retrieve(array $options = []) {
        //TODO: The developer must implement the retrieve method
    }
}
```

Then the developer must register the model manager so that the framework knows which model manager use when operating over a php object. The managers can be registered in the models.php file in the configuration directory like this ...
```PHP
<?php

return [
    "managers" => [
        \MyApp\Persons\Person::class => \MyApp\Persons\PersonsManager::class
    ]
];
```

Finally operating over the User model could be as follows ...
```PHP
$person = new Person("Luis", "Amengual");
create_model($person);

$persons = retrieve_models(Person::class, ["client"=>8]);
```

Views
---------------
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
Logging
---------------
Logging in NeoPHP is powered by [monolog](https://github.com/Seldaek/monolog). The configuration file for logging can be found in the logging.php file in the config directory. The logging.php configuration documentation can be found in the monolog extension [monolog-cascade](https://github.com/theorchard/monolog-cascade). This configuration file looks like this ...
```PHP
<?php

return [

    "default" => "main",

    'loggers' => [
        'main' => [
            'handlers' => ["rotating_file_handler"]
        ]
    ],

    "handlers" => [
        "rotating_file_handler" => [
            "class" => "Monolog\Handler\RotatingFileHandler",
            "level" => "DEBUG",
            "formatter" => "main_formatter",
            "filename" => get_app()->storagePath() . DIRECTORY_SEPARATOR . "logs" . DIRECTORY_SEPARATOR . "{date}",
            "maxFiles" => 30,
            "set_filename_format" => ["{date}.log", "Y-m-d"]
        ]
    ],

    "formatters" => [
        "main_formatter" => [
            "class" => "Monolog\Formatter\LineFormatter",
            "format" => "[%datetime%] %channel%.%level_name%: %message%\n",
            'include_stacktraces' => true
        ]
    ]
];
```
Many loggers can be configured with the "loggers" property and the default logger can be configured with the "default" property.

In NeoPHP we can obtain a monolog logger with the **get_logger** function like this
```PHP
get_logger();                   //Returns the default logger
get_logger("users")             //Returns the logger named "users"
get_logger(UserManager::class)  //Returns the logger name as the UserManager class
```
These are the availiable levels to log
```PHP
get_logger()->debug("...");
get_logger()->info("...");
get_logger()->notice("...");
get_logger()->warning("...");
get_logger()->alert("...");
get_logger()->error("...");
get_logger()->critical("...");
get_logger()->emergency("...");
```

Messages & translation
---------------
The base path for translation messages can be found in the directory "resources/messages". 
The following is an example of a bundles file
```PHP
<?php

return [
    "save_user": "Save user",
    "delete_user": "Really delete user %d"
    "show_user_name": "The name of the user is %s !!"
];
```

Configuring the script language for messages can be done in this way
```PHP
Messages::setLanguage("es");  //Configures the messages to use the spanish language "es"
```
Retrieving a translated message can be achieved with the **get_message** function ..
```PHP
get_message($key, array $replacements = [])
```
The **key is a string separated by points** that indicates where to find the message. Examples:
```PHP
//Search for property "car" in "resources/messages/es/main.php"
get_message("car")

//Search for property "delete_user_prompt" in "resources/messages/es/users.php"
get_message("users.delete_user_prompt")

//Search for property "search_user" in "resources/messages/es/site/users.php"
get_message("site.users.search_user")

//Search for property "welcome" in "resources/messages/en/site.php"
Messages::setLanguage("en");
get_message("site.welcome")
```
Message **keys may contain replacable values** for example ...
```PHP
//Message "Really delete user %d" shows as "Really delete user 35
get_message("delete_user_prompt", 35)       
```

References & Final Notes
---------------
### Author

Luis Manuel Amengual - <luismanuelamengual@gmail.com><br />
See also the list of [contributors](https://github.com/luismanuelamengual/neophp/contributors) which participated in this project.

### License

NeoPHP is licensed under the MIT License - see the `LICENSE` file for details

### Acknowledgements

This library is heavily inspired by Laravel PHP framework (https://laravel.com) library, although many things have been adjusted to be a more a powerfull and flexible framework
