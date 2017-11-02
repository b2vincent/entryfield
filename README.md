# entryfield
Entryfield framework for PHP - build efficient lists

This is a short-coded but rather complete framework to write Web applications with SQL. 

*MySQL, SQLite, SQL Server are tested so far. Other RDBMS should work because the framework is based on PDO.*

You start by giving a prefix to your application, let say *App* ; and a directory, let say *appl*. 
Our example app, a shared to-do-list, has the prefix Tdl and the directory tdlist.

The MVC approach is built as described below.

#### Models
* Data structures and formats are described in `appl\models\App_Schema.php`
* Data procedures are written in `appl\models\App_Model.php` 
  
  In more complex apps, we would separate the model in several files.
#### Controllers
* Each web page has a simple script in  `appl\pages\App_PageName.php`

  The purpose of a page is to assemble some parts.
* The controllers are in `appl\parts\App_PartName.php`
* Each part has the following functions : 
  * `doRun` : execute the part 
  * `doDeclare` : declare lists and forms used by the part
  * `doProcess` : what the part does when something is posted to it
  * `doDisplay` : what the part displays  
#### Views
* Built upon the data description in the Schema, a powerful `Ef_List` class builds a form or a table to show or update your data.
* Entryfield includes a template engine for basic separation between code and HTML.



