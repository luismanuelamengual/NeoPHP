# Resources

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
