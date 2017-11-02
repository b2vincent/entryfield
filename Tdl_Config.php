<?php

// General config file
global $efpath, $basepath, $sqlitepath, $templatepath, $baserelpath, $logdebugdir; 

// $efpath is the path to entryfield, must contain the F_Lib directory
// $basepath is the path to directories parts, helpers, models, pages
if (!isset($efpath) || !isset($basepath)) {
    throw new Exception('$efpath and $basepath variables must be set');
}

ini_set('include_path',$basepath.'/pages'.PATH_SEPARATOR.'.'.PATH_SEPARATOR.$basepath.'/parts'.PATH_SEPARATOR
        ."$efpath/F_Lib".PATH_SEPARATOR.get_include_path());

// Error handler because we want to see all errors
require_once($basepath.'/helpers/ErrorHandler.php');  

// Access to entryfield and extensions 
require_once('F_Field.php');
require_once('F_FieldExtended.php');
require_once($basepath.'/extends/Tdl_Extends.php');

Ef_Config::set('f_path_basepath',$basepath);

// Set date format
date_default_timezone_set('Europe/Paris');
Ef_Application::setDateFormat('Y-m-d');

// Database configuration
$sqlitepath = $basepath.'/sqlitedb';     
Ef_Config::set('f_sqlitedb_path',$sqlitepath);
Ef_Config::set('f_db_database','tdlist','tdlist');
Ef_Config::set('f_db_dbtype','sqlite','tdlist');
Ef_Config::set('f_db_host', 'localhost','tdlist');

// Template path : server path to the templates            
$templatepath = $basepath.'/templates/united';
// $templatepath = $basepath.'/templates/cerulean'; // au moins ça pète sympa
// $templatepath = $basepath.'/templates/spacelab'; // sympa sauf gris dégradé du menu  
// $templatepath = $basepath.'/templates/simplex'; // couleur de fond bofoss
// $templatepath = $basepath.'/templates/paper'; // mochos (titres trop grands)
// $templatepath = $basepath.'/templates/journal'; // mochos (mauvaises proportions)
// $templatepath = $basepath.'/templates/superhero'; // pas si mal sauf tables mais blackos 
Ef_Config::set('f_template_path',$templatepath);

// Design relative path : relative client path to the URL of the design pages and templates
Ef_Config::set('f_design_relpath',$templatepath);

// relative base path : relative client path to the URL of client resources (used in templates)
$baserelpath = $basepath;
Ef_Config::set('f_base_relpath',$baserelpath);

// log configuration
Ef_Config::set('f_log_debug',true);
$logdebugdir = $basepath.'/logs';

Ef_Config::set('f_log_debugfile',$logdebugdir.'/log_ef.txt');
Ef_Config::set('f_log_errorlog',$logdebugdir.'/log_errors.txt');
Ef_Config::set('errorlog','do'); 

// List rendering configuration : the specialized class which extends Ef_ListView 
Ef_Config::set('listview', 'Ef_ListViewExtended');

// Read template configuration : the function which reads templates
Ef_Config::set('readtemplatefunc', 'tdlReadTemplate');

// Greeting page
Ef_Config::set('greetingpage', 'tdl-item-list');
Ef_Config::set('loginpage', 'tdl-login');
 
// Local configuration file
include_once('Tdl_ConfigLocal.php');

?>