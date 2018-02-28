# Messages & translation

The base path for translation messages can be found in the directory "resources/messages". 
The following is an example of a bundles file
```PHP
<?php

return [
    "save_user" => "Save user",
    "delete_user" => "Really delete user %d",
    "show_user_name" => "The name of the user is %s !!"
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