NeoPHP
======

<h3>1. Que atributos tiene</h3>
  - Patrón de diseño MVC (Modelo Vista Controlador)
  - Clases ordenadas de manera jerarquica y estructuradas mediante nomenclatura especifica
  - Unico punto de ingreso (index.php)
  - Soporte para multiples lenguages
  - No se utilizan nunca variables $_GET, $_POST o $_REQUEST. Estas se mapean en argumentos en las acciones de los controladores lo que hace que quede todo mucho más prolijo.
  - No se utilizan variables $_SESSION, La sesión se usa a través de una clase especial que maneja dicha variable,
  - Para base de datos no se pone NADA de SQL, las tablas están modeladas como objetos y a través de métodos podes hacer búsquedas, inserciones, eliminaciónes, etc. Todas las consultas se hacen de manera homogenea y transparentes al que programe por afuera del framework y además utiliza PDO con lo cual no importa la base de datos que este corriendo atrás.

<h3>2. Como funciona</h3>

2.1. Controladores

Se utiliza solo 1 url y una acción asociada, es decir supongamos que el proyecto se llama "azureus", entonces la url para acceder a las paginas va a ser del tipo "http://localhost/azureus/?action=???". La acción va a indicar que acción hacer en la aplicación, por ejemplo:
  - action=site/showMainPage   => Se ejecutará la función "showMainPageAction" que va a estar dentro del controlador "SiteController" (La clase SiteController deberá estar en la carpeta "controllers", es decir quedaría app->controllers->SiteController)
  - action=site/users/addUser => Se ejecutará la función "addUserAction" que va a estar dentro del controlador "UsersController" (Este controlador lo buscaría en la siguiente ruta app->controllers->site->UsersController)
  - action=site/ => Ejecutaría la function "defaultAction" en el controlador "SiteController"

Si no se especifica una acción, el framework busca un controlador con el nombre "MainController" y dentro de el la funcion "defaultAction", es decir, si se quiere hacer el famoso "Hola Mundo" quedaría de la siguiente manera

`````php
<?php
class MainController extends Controller
{
    public function defaultAction ()
    {
        echo "hola mundo";
    }
}
?>
`````

Si a una acción le llegan variables GET o POST, estas llegan mapeadas de forma automática como argumentos de la acción, de la siguiente manera

`````php
<?php
class SiteController extends Controller
{
    public function loginAction ($username, $password)
    {
        //En este caso las variables $_POST['username'] y $_POST['password']
        //fueron mapeadas *automaticamente* en los argumentos de la funcion
    }
}
?>
`````

La idea con los controladores es crear controladores que agrupen funcionalidad sobre 1 mismo aspecto, es decir podriamos crear un controlador que maneje toda la lógica de usuarios, por ejemplo UserController que tenga las siguientes acciones:
  - addUserAction  //Accion de agregar un usuario a la base de datos 
  - updateUserAction  //Accion de actualizar un usuario a la base de datos 
  - deleteUserAction  //Accion de borrar un usuario a la base de datos 
  - showUserFormAction  //Muestra el formulario de datos de usuario
  - showUserInformationAction  //Muestra el formulario de datos de usuario una vez ingresado/actualizado (información no editable)

2.2. Vistas

Crear vistas es muy facil, todas las vistas heredan de una clase "View" que contiene un método "render", y hay una clase incluida en el framework, que es la clase HTMLView que te permite crear vistas de tipo HTML. Si queres crear un vista con el clasico "hola mundo" seria de la siguiente manera:

Paso 1: Crear un archivo PHP llamado "HelloWorldView" dentro de la carpeta "view"

`````php
<?php
require_once ("app/views/HTMLView.php");
class HelloWorldView extends HTMLView
{
    protected function build()
    {
        parent::build();
        $this->buildHead();
        $this->buildBody();
    }
   
    protected function buildHead ()
    {
        $this->headTag->add(new Tag("title", array(), "Hello World Example"));
        $this->headTag->add(new Tag("meta", array("http-equiv"=>"content-type", "content"=>"text/html; charset=UTF-8")));
        $this->headTag->add(new Tag("meta", array("name"=>"language", "content"=>"es")));
    }
   
    protected function buildBody ()
    {
        $this->bodyTag->add($this->createHelloWorldPanel());
    }
    
    protected function createHelloWorldPanel ()
    {
        return new Tag("div", array("class"=>"helloWorldPanel"), new Tag("span", array(), "HelloWorld"));
    }
}
?>
`````

El resultado en HTML de ejecutar el método render a esta vista será el siguiente

`````html
<!DOCTYPE html>
<html>
    <head>
        <title>Hello World Example</title>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
        <meta name="language" content="es" />
    </head>
    <body>
        <div class="helloWorldPanel">
            <span>HelloWorld</span>
        </div>
    </body>
</html>
`````

Paso 2: Crear una acción que renderize la vista

`````php
<?php
class MainController extends Controller
{
    public function defaultAction ()
    {
        App::getInstance()->getView("helloWorld")->render ();
    }
}
?>
`````

Y listo !!, ahi queda. Eventualmente se podría configurar ciertas cosas a la vista antes de renderizarla, por ejemplo podrías hacer lo siguiente:

`````php
$helloWorldView = App::getInstance()->getView("helloWorld");
$helloWorldView->setHelloWorldText ("Hola Mundito");
$helloWorldView->render();
`````

2.3. Traducciones

Las traducciones se hacen utilizando la clase Translator. Utiliza una nomenclatura especial para poder cargar correctamente los archivos de idiomas. Los archivos de idioma se crean en la carpeta resources. Ahi se puede crear una estructura jerarquiqua de carpetas finalizando con archivos .ini en donde estaran finalmente los textos en los distintos idiomas.

El archivo .ini de idioma debe tener la siguiente estructura

`````ini
[es]
firstname = nombre
lastname = apellido

[en]
firstname = FirstName
lastname = LastName
`````

Luego desde PHP se debe especificar el idioma con el que trabajará, por defecto se utilizara el idioma predeterminado del servidor web. Para establecer el idioma hay que ejecutar la siguiente sentencia

`````php
App::getInstance()->getTranslator()->setLanguage("pt");
`````

Finalmente para obtener los textos traducidos, hay que escribir sentencias como las siguientes

`````php
App::getInstance()->getTranslator()->getText("car");  //Buscara "car" en el archivo *resources/default.ini*
App::getInstance()->getTranslator()->getText("general.firstname");  //Buscara "firstname" en el archivo *resources/general.ini*
App::getInstance()->getTranslator()->getText("views.aboutus.welcome");  //Buscara "welcome" en el archivo *resources/views/aboutus.ini*
`````

2.4. Sesión

Para usar sesión tenes que usar la clase Session, se usa de la siguiente manera.
 
Para iniciar sesión
`````php
App::getInstance()->getSession()->startSession();
App::getInstance()->getSession()->userName = "pepech";
App::getInstance()->getSession()->firstName = "pepe";
App::getInstance()->getSession()->lastName = "paredes";
`````

Para acceder a datos de sesion
`````php
echo App::getInstance()->getSession()->userName;
`````

Para cerrar sesión
`````php
App::getInstance()->getSession()->destroy();
`````

2.5. Base de datos

Para base de datos se usan las clases "Connection" y "DataObject"
Para crear una nueva conexión a base de datos tenes que crear una clase "blablaConnection" que extienda de Connection con ciertos parametros. Utiliza PDO por consiguiente no importa con que base de datos te estes conectando. Podes crear mas de 1 conexión a base de datos, por ejemplo podrías tener una conexión a una base de datos en producción y o otra a una de pruebas, e sea podrias tener 2 clases asi

ProductionConnection (quedaría en app->connections->ProductionConnection.php)
DevelopmentConnection (quddaria en app->connections->DevelopmentConnection.php)

Por ejemplo, el archivo de conexión a la de producción sería de una base de datos mysql sería de la siguiente manera:

`````php
<?php
class ProductionConnection extends Connection
{
    public function __construct ()
    {
        parent::__construct ("mysql:host={hostname};dbname=mysql", "{username}", "{password}", array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
    }
}
?>
`````

Para acceder a dichas conecciones se hace de la siguiente manera

`````php
App::getInstance()->getConnection("production");
App::getInstance()->getConnection("development");
`````

2.5.1. Consultas SQL

Si quisieramos hacer "SELECT * FROM User" e iterar por los resultados deberíamos hacer lo siguientes

`````php
$connection = App::getInstance()->getConnection("production");
$doUser = $connection->getDataObject("User");
$doUser->find();
while ($doUser->fetch())
{
    echo $doUser->username;
    echo "<br>";
    echo $doUser->password;
}
`````

Tambien podrías obtener el resultSet en forma de array con el método fetchAll
$resultSet = $doUser->fetchAll();

Si quisieramos hacer "SELECT username FROM user WHERE username="pepech" AND password="123"

`````php
$connection = App::getInstance()->getConnection("production");
$doUser = $connection->getDataObject("User");
$doUser->addSelectField ("username");
$doUser->addWhereCondition("username='pepech'");
$doUser->addWhereCondition("password='123'");
$doUser->find(true); //El true indica que hace un fetch automatico
echo $doUser->username;
`````

Si quisieramos hacer por ejemplo "SELECT user.username AS user_username, user.password AS user_password, person.firstname AS person_firstname, person.lastname AS person_lastname FROM user INNER JOIN person ON user.personid = person.personid"

`````php
$connection = App::getInstance()->getConnection("production");
$doUser = $connection->getDataObject("User");
$doPerson = $connection->getDataObject("Person");
$doUser->addSelectFields (array("username", "password"), "user_%s", "user");
$doUser->addSelectFields (array("firstname", "lastname"), "person_%s", "person");
$doUser->addJoin($doPerson, DataObject::JOINTYPE_INNER, "personid");
$doUser->find()
{
    echo $doUser->user_username;
    echo "<br>";
    echo $doUser->person_firstname;
}
`````

Si quisieramos hacer por ejemplo un insert "INSERT INTO User (username, password) VALUES ("pepe", "123")" sería de la siguiente manera

`````php
$connection = App::getInstance()->getConnection("production");
$doUser = $connection->getDataObject("User");
$doUser->username = "pepe";
$doUser->password = "123";
$doUser->insert();
`````

Con PDO::lastInsertId() podes obtener cual fue el indice que se utilizo para el último insert

Si quisieramos "UPDATE User SET password = "456" WHERE username = "pepe"" sería

`````php
$connection = App::getInstance()->getConnection("production");
$doUser = $connection->getDataObject("User");
$doUser->password = "456";
$doUser->addWhereStatement("username='pepe'");
$doUser->update();
`````

<h3>3. Como comenzar a utilizarlo</h3>
Solo se tiene que copiar la carpeta "trunk" al raiz de un proyecto nuevo y ya está, de ahi en más ya se puede empezar a crear controladores propios y vistas dentro del mismo.

Es posible que en entornos Windows haya que configurar en el archivo de configuración de apache (httpd.conf) el DocumentIndex para que apunte a index.php en lugar de index.html

Es recomendado utilizar ciertas configuración en el php.ini (no obligatorias), estas son:
  - session.auto_start = 1
  - session.use_cookies = 1
  - session.use_trans_sid = 0;

