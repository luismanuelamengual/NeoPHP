# Database

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