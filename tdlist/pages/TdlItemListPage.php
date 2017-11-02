<?php

// Tdl List of items page

// common required
require_once('Tdl_Config.php');	
require_once('Tdl_Includes.php');

require_once('Tdl_MyMenuPart.php');
require_once('Tdl_ItemListPart.php'); 

// Process
tdlStartSession();
tdlCheckConnected();

$myMenuPart = new Tdl_MyMenuPart();
$myMenuContent = $myMenuPart->doRun($thispage);

// Ef_Log::htmlDump($myMenuContent,'myMenuContent');

$ListPart = new Tdl_ItemListPart();
$ListContent = $ListPart->doRun($thispage);

if (count($_POST) > 0) return; 

// Display 
$thispage = tdlNewPage(); 

$thispage->addTemplate("tpl_fullcol.html");

$thispage->replaceVar ('%menu%', $myMenuContent);
$thispage->replaceVar ('%coltitle%', (''));
$thispage->replaceVar ('%coltext%', $ListContent);  

// $thispage->addText($ListContent);

tdlClosePage($thispage);    
     
