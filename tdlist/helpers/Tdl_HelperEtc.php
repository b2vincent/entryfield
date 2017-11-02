<?php

// misc functions
function timeNow() 
{
	list($msec, $sec) = explode(' ', microtime());
    return (float)$sec + (float)$msec;	
}

function dateTimeNow()
{
    $date = date('Y-m-d H:i:s', time());
    return $date;    
}

function dateNow()
{
    $date = date('Y-m-d', time());
    return $date; 
}


function urlContainsString($str) 
{
    return strpos(urlGetCurrent(),$str);
}

function urlGetCurrent() 
{
    // may be specific to configuration
    $functionname = __FUNCTION__.'_'.$GLOBALS['configmode'];     
    if (function_exists ($functionname)) {
        return call_user_func_array( $functionname , array()); 
    }
    $pageURL = 'http';
    if (isset($_SERVER["HTTPS"])) {
        $pageURL .= "s";
    }
    $pageURL .= "://";
    if ($_SERVER["SERVER_PORT"] != "80") {
        $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
    } else {
        $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
    }
    return $pageURL;
}

// return relative path from website home
function pathFromWwwGet() 
{
    // allow to set it by hand
    if (Ef_Config::get('pathfromwww')) {
        return Ef_Config::get('pathfromwww'); 
    } 
    $self = $_SERVER['PHP_SELF'];
    $lastslash = strrpos($self, '/');
    return substr($self, 0, $lastslash+1);
}

/*
function urlRemoveGetVar($url, $varname) 
{
    $newurl = preg_replace('/([?&])'.$varname.'=[^&]+(&|$)/','$1',$url);
    $lastchar = substr($newurl, -1);     
    if ($lastchar == '?' || $lastchar == '&') {
        $newurl = substr($newurl, 0, strlen($newurl)-1);
    } 
    return $newurl;
}

function urlAddGetVar($url, $varname, $varvalue) 
{

    $query = parse_url($url, PHP_URL_QUERY);
    if ($query) {
        $url .= '&'.$varname.'='.$varvalue;
    } else {
        $url .= '?'.$varname.'='.$varvalue;
    }
    return $url;
}
*/

function logError($var, $msg) 
{
    Ef_Log::conditionLog($var, $msg,'errorlog','f_log_errorlog');
}

function pregArrayKeyExists($pattern, $array) 
{
    $keys = array_keys($array);    
    return (int) preg_grep($pattern,$keys);
}

?>