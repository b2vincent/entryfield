<?php


// Do something more than starting session, if needed
function tdlStartSession()
{
    Ef_Session::start();
}

// Application model...  

function appGetTitle()
{
    $selectsqlreq = new F_SqlReq("
        select ap_title from  tdlapplication 
    ",'tdlist');
    return $selectsqlreq->getValue();    
}  

function appGetSubTitle()
{
    $selectsqlreq = new F_SqlReq("
        select ap_subtitle from  tdlapplication 
    ",'tdlist');
    $appsubtitle = $selectsqlreq->getValue();
    Ef_Log::log($appsubtitle, 'appsubtitle'); 
    return $appsubtitle;     
}  

function appGetAdminType() 
{
    $selectsqlreq = new F_SqlReq("
        select ap_admintype from  tdlapplication 
    ",'tdlist');
    return $selectsqlreq->getValue();    
}

// User model...

function tdlUserCheckPassword($login, $password) 
{
    $checkpassreq = new Ef_SqlReq("
        select us_login from tdluser where us_login  = '$login' and us_password = '$password'
    ",'tdlist');
    // Ef_Log::log ($checkpasssql, ' checkpasssql in cmUserCheckPassword');
    
    $checkpassvalue = $checkpassreq->getValue();
    // Ef_Log::log ($checkpassvalue, ' checkpassvalue in cmUserCheckPassword');

    if ($checkpassvalue == $login) {
        return true;
    } else {
        return false;
    }
}

function tdlUserSetConnected($login)
{
    Ef_Session::setVal('login', $login);
}

function tdlUserDisconnect()
{
    Ef_Session::delKey('login');
}

function tdlUserIsConnected()
{
    if (Ef_Session::getVal('login')) {
        return true;
    } else {
        return false;
    }
}

function tdlUserGetWelcomeMessage()
{
    if (Ef_Session::getVal('login')) {
        $login = Ef_Session::getVal('login'); 
        return Ef_Lang::get('Hi, %1 !', array($login));    
    } else {
        return Ef_Lang::get('You are not connected');
    }
}

function tdlGetUsers()
{
    $sqlreq = new Ef_SqlReq("
        select tdlus.usid, tdlus.us_login from tdluser tdlus
                order by tdlus.usid                
    ",'tdlist');
    $rows = $sqlreq->getRows();
    $userarray = array('');
    foreach ($rows as $row) {
        $code = $row['usid'];
        $name = Ef_Lang::get($row['us_login']);        
        $userarray[$code] = $name;
    } 
    // test : big number of users
    // for ($i = 1; $i < 100; $i++) {
    //     $userarray['user'.$i] = 'name of user'.$i;
    // }
    return $userarray;
}

// Project model...

function tdlGetProjects()
{
    $sqlreq = new Ef_SqlReq("
        select tdlpr.prid, tdlpr.pr_title from tdlproject tdlpr
                order by tdlpr.prid
    ",'tdlist');
    $rows = $sqlreq->getRows();
    $projectarray = array('');
    foreach ($rows as $row) {
        $code = $row['prid'];
        $name = Ef_Lang::get($row['pr_title']);        
        $projectarray[$code] = $name;
    } 
    return $projectarray;
}

// Status model...

function tdlGetStatus()
{
    $sqlreq = new Ef_SqlReq("
        select tdlst.stid, tdlst.st_title from tdlstatus tdlst
                order by tdlst.stid                
    ",'tdlist');
    $rows = $sqlreq->getRows();    
    $statusarray = array('');
    foreach ($rows as $row) {
        $code = $row['stid'];
        $name = Ef_Lang::get($row['st_title']);        
        $statusarray[$code] = $name;
    } 
    return $statusarray;
}


?>
