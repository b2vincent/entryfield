<?php

// Tdl Item update part

// common required
require_once('Tdl_Config.php');	
require_once('Tdl_Includes.php');

require_once('Tdl_MyMenuPart.php');
require_once('Tdl_ItemUpdatePart.php'); 

// Process
tdlStartSession();
if (!tdlUserIsConnected()) tdlGotoPage(Ef_Config::get('loginpage'));

$myMenuPart = new Tdl_MyMenuPart();
$myMenuContent = $myMenuPart->doRun($thispage);

$itemUpdatePart = new Tdl_ItemUpdatePart();
$itemUpdateContent = $itemUpdatePart->doRun($thispage);

if (count($_POST) > 0) return;
/*
if (count($_POST) > 0 && FORMTARGET) { 
    return;
} else {
    tdlGotoPage('tdl-item-update');
} 
*/

// Display 
$thispage = tdlNewPage(); 

$thispage->addTemplate("tpl_fullcol.html");

$thispage->replaceVar ('%menu%', $myMenuContent);
$thispage->replaceVar ('%coltitle%', (''));
$thispage->replaceVar ('%coltext%', $itemUpdateContent);  

// $thispage->addText($ListContent);

tdlClosePage($thispage);    
     
