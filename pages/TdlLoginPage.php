<?php

// Tdl Login page

// common required
require_once('Tdl_Config.php');	
require_once('Tdl_Includes.php');

require_once('Tdl_MyMenuPart.php');
require_once('Tdl_LoginPart.php'); 

// Process
tdlStartSession();

$thispage = tdlNewPage(); 
$thispage->replaceVar('%pagetitle%', (Ef_Lang::get('Administration')));

$myMenuPart = new Tdl_MyMenuPart();
$myMenuContent = $myMenuPart->doRun($thispage);

$LoginPart = new Tdl_LoginPart();
$LoginContent = $LoginPart->doRun($thispage);

if (count($_POST) > 0) return; 

// Display 
$thispage = tdlNewPage(); 

$thispage->addTemplate("tpl_fullcol.html");

$thispage->replaceVar ('%menu%', $myMenuContent);
$thispage->replaceVar ('%coltitle%', (''));
$thispage->replaceVar ('%coltext%', $LoginContent);

// $thispage->addText($LoginContent);

tdlClosePage($thispage);    
     
