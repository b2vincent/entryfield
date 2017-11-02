<?php
// This script is the processing target for forms    
// Necessary to avoid 'your data has expired from cache' : the display URL is not the same as the POST url
$efpath = '.';
$basepath = 'tdlist';
   
require_once('Tdl_Config.php');	      
require_once('Tdl_Includes.php');


$scriptpath = Ef_Route::getScriptPathFromUrl($_SERVER['HTTP_REFERER']);
// Ef_Log::log ($scriptpath, 'scriptpath in Bk_FormProc.php');

if (count($_POST) > 0) {
    // if (is_readable($scriptpath)) 
    // {
        Ef_Route::setGetParamsFromUrl($_SERVER['HTTP_REFERER']);
        include($scriptpath);
    // }
}

Ef_Log::log($_SERVER['HTTP_REFERER'],'HTTP_REFERER in Tdl_FormProc');
Ef_Log::log($_GET,'$_GET in Tdl_FormProc');

$nexturl = Ef_Route::updateUrlParamsFromGet($_SERVER['HTTP_REFERER'], $_GET);
Ef_Log::log($nexturl, 'next url in Tdl_FormProc');  

// Uncomment these lines if you want to debug the Process section of the part
// echo("End of processing. Please click on this : <a href=$nexturl>link </a>");
// return; 

tdlGotoPage($nexturl);

/* To delete
if (headers_sent()) {
    die("<br>
        Some messages were produced (and shouldn't). <br>
         Redirection is stopped. <br>
         Please click on this : <a href=$nexturl>link </a>");
} else {
    header("Location: ".$nexturl);
}
*/


?>