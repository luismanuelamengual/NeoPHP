# Models

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