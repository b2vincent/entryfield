<?php

// local config file : multi-configuration

//  This file is intended to keep the same application code in various environments
//  It will specifically be written to detect 
//  -   configmode : a specific configuration mode for the application
//  -   configlevel :  Dev or Test or Production
//  It will configure the application according to configmode / configlevel 

global $configmode, $configlevel, $language;

$configmode = '';
$language = 'en';
$configlevel = 'Dev';

if ($configlevel == 'Dev') {
    // define('FORMTARGET', '');
    define('FORMTARGET', 'action="Tdl_FormProc.php"');
} else {
    define('FORMTARGET', 'action="Tdl_FormProc.php"');
}

// Ef_Db::dbOpen('tdlist');

 
//  Exemple of what could be written here
/*
$hostname = php_uname("n");

// position $language, $configleve, $configmode 
$language = 'en';
switch ($hostname) {
    case 'agamemnon':
    case 'osiris':
        $configlevel = 'Dev';
        $configmode='Customer1';
        $mailmode = 'printtofile';
        Ef_Config::set('baseurl','http://localhost/www/thisapp/');
        break;
    case 'toutankhamon':
        $configlevel = 'Test';
        $configmode='Standard';
        $mailmode = 'smtp';
        Ef_Config::set('baseurl','http://testserver/thisapp/');
        break;
    case 'thoutmosis':
        $configmode = 'Prod';
        $configmode='Customer1';
        $mailmode = 'smtp';
        Ef_Config::set('baseurl','http://theprodserver/thisapp/');
        break;
}

// position database access
switch ($hostname) {
    case 'agamemnon':
    case 'osiris':
    case 'toutankhamon':
        Ef_Config::set('f_db_database','db253','thisdb');
        Ef_Config::set('f_db_dbtype','mysql','thisdb');
        Ef_Config::set('f_db_host', 'testserv', 'thisdb');
        Ef_Config::set('f_db_user', 'user', 'thisdb');
        Ef_Config::set('f_db_pass', 'pass', 'thisdb');
        Ef_Db::dbOpen('thisdb');
        break;    
    case 'thoutmosis':
        Ef_Config::set('f_db_database','dbprod','thisdb');
        Ef_Config::set('f_db_dbtype','mysql','thisdb');
        Ef_Config::set('f_db_host', 'prodserv', 'thisdb');
        Ef_Config::set('f_db_user', 'user', 'thisdb');
        Ef_Config::set('f_db_pass', 'pass', 'thisdb');
        Ef_Db::dbOpen('thisdb');
        break;
}
*/

?>