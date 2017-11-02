<?php
         
// common required
require_once('Tdl_Config.php');	
require_once('Tdl_Includes.php');

require_once('Tdl_MyMenuPart.php');
require_once('Tdl_SimpleFieldsPart.php'); 

// Process
tdlStartSession();

$myMenuPart = new Tdl_MyMenuPart();
$myMenuContent = $myMenuPart->doRun($thispage);

$simpleFieldsPart = new Tdl_SimpleFieldsPart();
$simpleFieldsContent = $simpleFieldsPart->doRun($thispage);

if (count($_POST) > 0) return; 

// Display 
$thispage = tdlNewPage(); 
$thispage->replaceVar ('%pagetitle%', (Ef_Lang::get('Simple fields')));

$thispage->addTemplate("tpl_fullcol.html");

$thispage->replaceVar ('%menu%', $myMenuContent);
$thispage->replaceVar ('%coltitle%', (Ef_Lang::get('Simple fields')));
$thispage->replaceVar ('%coltext%', $simpleFieldsContent);  

// $thispage->addText($simpleFieldsContent);

tdlClosePage($thispage);    
     
