<?php

// Tdl Administration Web page

// common required
require_once('Tdl_Config.php');	
require_once('Tdl_Includes.php');

require_once('Tdl_MyMenuPart.php');
require_once('Tdl_ApplicationUpdatePart.php'); 
require_once('Tdl_UserUpdatePart.php'); 
require_once('Tdl_ProjectUpdatePart.php');
require_once('Tdl_StatusUpdatePart.php');


tdlStartSession();
tdlCheckConnected();

$thispage = tdlNewPage(); 
$thispage->replaceVar('%pagetitle%', (Ef_Lang::get('Administration')));


$myMenuPart = new Tdl_MyMenuPart();
$myMenuContent = $myMenuPart->doRun($thispage);

$applicationUpdatePart = new Tdl_ApplicationUpdatePart();
$applicationUpdateContent = $applicationUpdatePart->doRun($thispage);

$userUpdatePart = new Tdl_UserUpdatePart();
$userUpdateContent = $userUpdatePart->doRun($thispage);

$projectUpdatePart = new Tdl_ProjectUpdatePart();
$projectUpdateContent = $projectUpdatePart->doRun($thispage);

$statusUpdatePart = new Tdl_StatusUpdatePart();
$statusUpdateContent = $statusUpdatePart->doRun($thispage);

// transfer tab number to get parameters
// Ef_Log::log($_POST,'_POST in tdladminpage');

if (isset($_POST['but_update_application'])) {
    $_GET['tabnum'] = 1;
}
if (isset($_POST['but_update_user']) || pregArrayKeyExists('/btn_add_user/',$_POST)
        || pregArrayKeyExists('/btn_del_user/',$_POST)) {
    $_GET['tabnum'] = 2;
}
if (isset($_POST['but_update_project']) || pregArrayKeyExists('/btn_add_proj/',$_POST)
        || pregArrayKeyExists('/btn_del_proj/',$_POST)) {
    $_GET['tabnum'] = 3;
}
if (isset($_POST['but_update_status']) || pregArrayKeyExists('/btn_add_stat/',$_POST)
        || pregArrayKeyExists('/btn_del_stat/',$_POST)) {
    $_GET['tabnum'] = 4;
}
if (!isset($_GET['tabnum'])) {
    $_GET['tabnum'] = 1;
}

if (count($_POST) > 0) return; 

// Display 
$thispage->replaceVar ('%menu%', $myMenuContent);

$admintype = appGetAdminType();

// Display as a list
if ($admintype == 'list') {
    $thispage->addTemplate("tpl_fullcol.html");    
    $thispage->replaceVar ('%coltitle%', Ef_Lang::get('App administration'));
    $thispage->replaceVar ('%coltext%', $applicationUpdateContent.$projectUpdateContent
            .$userUpdateContent.$statusUpdateContent);
    tdlClosePage($thispage);    
    return;
}

// Default : admintype = tabs

// activate the tab mentioned in GET variable
$thispage->addTemplate("tpl_fourtabs.html");

if (isset($_GET['tabnum'])) {
    $tabnum = $_GET['tabnum'];
    for ($i = 1; $i <= 4; $i++) {
        if ($i == $tabnum) {
            $thispage->replaceVar("%active$i%",'active');
        } else {
            $thispage->replaceVar("%active$i%",'inactive');        
        }
    } 
}


$thispage->replaceVar('%tab1title%',Ef_Lang::get('Settings'));
$thispage->replaceVar('%tab1content%',$applicationUpdateContent);
$thispage->replaceVar('%tab2title%',Ef_Lang::get('Users'));
$thispage->replaceVar('%tab2content%',$userUpdateContent);
$thispage->replaceVar('%tab3title%',Ef_Lang::get('Projects'));
$thispage->replaceVar('%tab3content%',$projectUpdateContent);
$thispage->replaceVar('%tab4title%',Ef_Lang::get('Status'));
$thispage->replaceVar('%tab4content%',$statusUpdateContent);

tdlClosePage($thispage);    
     
