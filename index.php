<?php

$requestedurl =  strtolower($_SERVER['REQUEST_URI']);

if (strpos($requestedurl, 'tdl-') !== false) {

    $efpath = '.';
    $basepath = 'tdlist';
    require_once('Tdl_Config.php');	      
    require_once('Tdl_Includes.php');

    // relative root path of the site
    $pathfromwww = pathFromWwwGet();
    
    // Ef_Log::log($pathfromwww,'pathfromwww');
    
    // get path without relative root path of the site 
    $relativepath = str_replace(strtolower($pathfromwww), '', strtolower($_SERVER['REQUEST_URI']));
    
    Ef_Log::log($relativepath,'relativepath');
    
    if (trim($relativepath) == '') {
        tdlShowGreeting();
    }
    
    $includeFile = Ef_Route::getScriptPathFromUrl($relativepath);
    
    Ef_Log::log($includeFile,'includeFile');
    
    if ($includeFile && $includeFile != basename(__FILE__)) {
        include($includeFile);
        return;
    } else {
        header('HTTP/1.1 404 Not Found');
        echo ('<html>Error 404 </html>');
    }

} else {

    // header('HTTP/1.1 404 Not Found');
    // echo ('<html>Error 404 </html>');
    header("Location: tdl-login");
}
// ?>