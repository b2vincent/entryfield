<?php

// helper related to display function 

// this function is specialized with 'readtemplatefunc' in Ef_Config
function tdlReadTemplate($filepath) 
{
    $templatecontent = file_get_contents($filepath);

    $designrelpath = Ef_Config::get('f_design_relpath');        
    $baserelpath = Ef_Config::get('f_base_relpath');
    
    $newcontent = str_replace('%designrelpath%', $designrelpath, $templatecontent);
    $newcontent = str_replace('%baserelpath%', $baserelpath, $newcontent);    
    return $newcontent;		

}


function tdlNewPage($withForm=true) 
{
	$GLOBALS['utime1'] = timeNow();
    
	$thispage = new Ef_Page();
    
    $thispage->addTemplate('tpl_header.html');
    
    $thispage->replaceVar('%pagetitle%', appGetTitle());
    $thispage->replaceVar('%apptitle%', appGetTitle());
    $thispage->replaceVar('%appsubtitle%', appGetSubTitle());

    
    $thispage->replaceVar('%baserelpath%',Ef_Config::get('f_base_relpath'));
    $thispage->replaceVar('%designrelpath%',Ef_Config::get('f_design_relpath'));

    $formtext = '<form role="form" id="myForm" '.FORMTARGET.' method="POST">'."\n<br>\n ";
    if ($withForm) {
        if ($thispage->findVar('%beginform%') !== false) {
            $thispage->replaceVar('%beginform%', $formtext);         
        } else {
            $thispage->addText($formtext);
        }

    }
    
    
    return $thispage;
}

// function to close the current page 
function tdlClosePage(&$thispage, $withForm=true) 
{
    if ($withForm) {
        $thispage->addText('</form>');
    }
    $utime2 = timeNow();
    $pagetime = $utime2-$GLOBALS['utime1'];
	
    
    $thispage->addTemplate("tpl_footer.html");
    $thispage->replaceVar ('%copyright%', '(c) V.Wartelle & Oklin.com ');
    $thispage->replaceVar ('%version%', ' version 1.31 ');
	$thispage->replaceVar ('%servertime%', "- Server time : ".sprintf('%01.3f',$pagetime));  
    $thispage->replaceVar ('%hostname%'," -".php_uname("n"));
    $thispage->render();  
    Ef_Db::dbCloseAll();       
}

// Go to some page
function tdlGotoPage($urlsuffix) 
{
    // Ef_Log::log($urlsuffix,'call tdlGotoPage with this urlsuffix');
    
    // translate urlsuffix to route name if url suffix is a file name
    $newurlsuffix = Ef_Route::getUrlFromFile($urlsuffix);
    
    if ($newurlsuffix) {
        $urlsuffix = $newurlsuffix;
    }

    Ef_Db::dbCloseAll();

    if (ini_get('session.use_cookies')) {  
        $nexturl=$urlsuffix;
    } else { 		               
        $sessname = Ef_Config::get('sessname');
        $sessionid = Ef_Session::getSessionId();
        $nexturl="$urlsuffix?$sessname=$sessionid";
    }
    // Ef_Log::log ($nexturl, "tdlGotoPage goes to this url"); 
    
    // Avoid the error "headers already sent" and be able to analyze messages produced
    if (headers_sent()) {
        die("<br><br>
            Some messages were produced (and shouldn't). <br>
            Redirection is stopped. <br>
            To process to redirection please click on this : <a href=$nexturl>link </a>"
         );
    }
             
    header("Location: $nexturl");
    exit;
}

function tdlShowGreeting() 
{
    $showpage = Ef_Config::get('greetingpage');
    tdlGotoPage($showpage);
}

function tdlShowLogin()
{
    $showpage = Ef_Config::get('loginpage');
    tdlGotoPage($showpage);
}


// render a message with alert level of bootstrap
// success, info, warning, danger
function tdlRenderMessage($message, $messageLevel='danger')
{
    // return '<p style="color:red; font-size:120%;" >'. $message.'</p>';
    return '<div class="alert alert-'.$messageLevel.'">'.$message.'</div>';        
}

// check that the user is connected
function tdlCheckConnected()
{
    if (!tdlUserIsConnected()) {
        Ef_Session::appendMessage('TdlLogin', Ef_Lang::get('You must be connected to see this page'));
        tdlGotoPage(Ef_Config::get('loginpage'));
    }
}

?>