<?php

// Tdl page part : menu

// Common required
require_once('Tdl_Config.php');	
include_once('Tdl_Includes.php');


class Tdl_MyMenuPart extends Ef_PagePart 
{
    
    // Inner function to add a link to the menu
    function myMenuAddLink (&$page, $linktarget, $linkname, $targetmenu, $classinfo='' )
    {
        $link = '<a href="'.$linktarget.'">'.$linkname.'</a>';
        $addlink = "<li $classinfo>".$link."</li>\n";
        $textcontent = $addlink;
        
        $page->replaceVarNext($targetmenu, $textcontent);
    }
    
    // Page part execution : declare, process, display
    public function doRun(&$thispage=null)
    {
        $this->doDeclare();        
        // processing input data and make the controls and so on
        if (count($_POST) > 0) {
            $this->doProcess();
            // end of $_POST processing : return for display
            return;
        }
        // process display
        return $this->doDisplay($thispage);           
    }        

    // Declare requests and business logic
    public function doDeclare() 
    {
    }

    // Process POST input
    public function doProcess()
    {              
    }    
    
    // Display
    public function doDisplay(&$thispage=null)
    {        
        
        $tmppage = new Ef_Page();
        $tmppage->addTemplate('tpl_headmenu.html');
        $tmppage->replaceVar('%apptitle%',appGetTitle());        
        // Display the menus         
        
        $this->myMenuAddLink($tmppage,'tdl-login',Ef_Lang::get('Login / Logout'),'%leftmenu%');
        $this->myMenuAddLink($tmppage,'tdl-item-list',Ef_Lang::get('List of items'),'%leftmenu%');
        $this->myMenuAddLink($tmppage,'tdl-admin',Ef_Lang::get('Administration'),'%leftmenu%');
        $this->myMenuAddLink($tmppage,'tdl-simple-fields',Ef_Lang::get('Simple fields'),'%leftmenu%');
        $this->myMenuAddLink($tmppage,'tdlist/pages/TdlSimpleList.php',Ef_Lang::get('Simplified list'),'%leftmenu%');
        $this->myMenuAddLink($tmppage,'tdl-login',tdlUserGetWelcomeMessage(),'%rightmenu%');
        // Close the menus
        $tmppage->replaceVar('%leftmenu%','');
        $tmppage->replaceVar('%rightmenu%','');
        
        return $tmppage->getContent();

    }                       
}


