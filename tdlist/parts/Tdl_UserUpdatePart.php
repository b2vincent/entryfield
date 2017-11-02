<?php

// Tdl page part : update users

// Common required
require_once('Tdl_Config.php');	
include_once('Tdl_Includes.php');
	
class Tdl_UserUpdatePart extends Ef_PagePart 
{
    protected $listreq, $adduser, $deluser;

    // page part execution : declare, process, display
    public function doRun(&$thispage=null)
    {
        $this->doDeclare();
        
        // processing input data and make the controls and so on
        if (count($_POST) > 0) {
            $this->doProcess();
            // end of $_POST processing : return 
            return;
        }       
        // process display
        return $this->doDisplay($thispage);           
    }        

    // declare requests and business logic
    public function doDeclare() 
    {
        // creating a list request 
        $this->listreq = new Ef_List('usupd', "
        			select %fieldlist% from tdluser tdlus			
        			%where%
        			%orderby%
        		",'tdlist');
        $this->listreq->buildSelectReq();
        
        $this->listreq->setAllFieldState('edit');
        $this->listreq->setFieldState('tdlus.usid','hidden');
        
        // row button : to add record
        $this->adduser = Ef_Field::construct('virtual.btn_add_user',
                    array('type'=>'rowbutton','buttonprefix'=>'btn_add_user',
                    'buttontext'=>Ef_Lang::get('Add'),'rowidname'=>'tdlus_usid'));
        $this->listreq->insertVirtualFieldAtEnd ('virtual.btn_add_user');
        $this->listreq->setFieldState('virtual.btn_add_user','edit');
                
        // row button : to delete record 
        $this->deluser   = Ef_Field::construct('virtual.btn_del_user',
                        array('type'=>'rowbutton','buttonprefix'=>'btn_del_user',
                            'buttontext'=>Ef_Lang::get('Delete'),'rowidname'=>'tdlus_usid'));
        $this->listreq->insertVirtualFieldAtEnd ('virtual.btn_del_user');
        $this->listreq->setFieldState('virtual.btn_del_user','edit');
        
        // register controls : no control here
        
        // build update request
        $this->listreq->setUpdateTable('tdluser');
        $this->listreq->buildUpdateReq();           
    }

    public function doProcess()
    {
    	// Ef_Log::htmlDump($_POST,'_POST');
        // in any case, do update before adding or suppress
        if (isset($_POST)) {
    		if ($this->listreq->processControl()) {	
    			$this->listreq->processUpdate();
    		}	
    	}
    
    	$postedrow = $this->adduser->getPostedRow();
    	if ($postedrow !== false) {
            $newlineid = Ef_TableUtil::getNewId('tdluser');
        
    		$insertreq = new Ef_SqlReq(	"
    			insert into tdluser(usid, us_login, us_password ) \n 
    			values ('$newlineid', '', '' ) \n 
    		",'tdlist');
    		$insertreq->execute();
    	}
    	
    	$postedrow = $this->deluser->getPostedRow();
    	if ($postedrow !== false) {    		
    		$delreq = new Ef_SqlReq("
    			delete from tdluser  \n 
    			where usid = '$postedrow' \n 
    		",'tdlist');
    		$delreq->execute();	
    	}	
    }           
    
    public function doDisplay(&$thispage=null)
    {
        $tmppage = new Ef_Page();    
        $tmppage->replaceVar ('%errormsgs%', $this->listreq->getErrorText());
            
        $render = ($this->listreq->getRenderRows(array('variant'=>'simplehtmltable','rowtitle'=>'1')));
        
        $tmppage->addTemplate("tpl_fullcol.html");
        $tmppage->replaceVar ('%coltitle%', (Ef_Lang::get('Users')));
        $tmppage->replaceVar ('%coltext%', $render);  
        	
        $tmppage->addText('<input type="submit" class="btn" 
            name="but_update_user" value="'.Ef_Lang::get("Submit updates").'">');
        
        // $tmppage->addText('</form>');    
                
        return $tmppage->getContent();
                
    }                       
}
	  

