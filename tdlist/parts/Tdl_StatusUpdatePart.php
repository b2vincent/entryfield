<?php

// Tdl page part : update status

// Common required
require_once('Tdl_Config.php');	
include_once('Tdl_Includes.php');
	
class Tdl_StatusUpdatePart extends Ef_PagePart 
{
    protected $listreq, $addproj, $delproj;

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
        $this->listreq = new Ef_List('stupd', "
        			select %fieldlist% from tdlstatus tdlst			
        			%where%
        			%orderby%
        		",'tdlist');
        $this->listreq->buildSelectReq();
        
        $this->listreq->setAllFieldState('edit');
        $this->listreq->setFieldState('tdlst.stid','readonly');
        
        // row button : to add record
        $this->addproj = Ef_Field::construct('virtual.btn_add_stat',
                    array('type'=>'rowbutton','buttonprefix'=>'btn_add_stat',
                    'buttontext'=>Ef_Lang::get('Add'),'rowidname'=>'tdlst_stid'));
        $this->listreq->insertVirtualFieldAtEnd ('virtual.btn_add_stat');
        $this->listreq->setFieldState('virtual.btn_add_stat','edit');
                
        // row button : to delete record 
        $this->delproj   = Ef_Field::construct('virtual.btn_del_stat',
                        array('type'=>'rowbutton','buttonprefix'=>'btn_del_stat',
                            'buttontext'=>Ef_Lang::get('Delete'),'rowidname'=>'tdlst_stid'));
        $this->listreq->insertVirtualFieldAtEnd ('virtual.btn_del_stat');
        $this->listreq->setFieldState('virtual.btn_del_stat','edit');
        
        // register controls : no control here
        
        // build update request
        $this->listreq->setUpdateTable('tdlstatus');
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
    
    	$postedrow = $this->addproj->getPostedRow();
    	if ($postedrow !== false) {
            $newlineid = Ef_TableUtil::getNewId('tdlstatus');
        
    		$insertreq = new Ef_SqlReq(	"
    			insert into tdlstatus(stid, st_title, st_isactive) \n 
    			values ('$newlineid', '', '') \n 
    		",'tdlist');
    		$insertreq->execute();
    	}
    	
    	$postedrow = $this->delproj->getPostedRow();
    	if ($postedrow !== false) {    		
    		$delreq = new Ef_SqlReq("
    			delete from tdlstatus  \n 
    			where stid = '$postedrow' \n 
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
        $tmppage->replaceVar ('%coltitle%', (Ef_Lang::get('Status')));
        $tmppage->replaceVar ('%coltext%', $render);  
        	
        $tmppage->addText('<input type="submit" class="btn" 
            name="but_update_status" value="'.Ef_Lang::get("Submit updates").'">');
        
        // $tmppage->addText('</form>');    
                
        return $tmppage->getContent();
                
    }                       
}
	  

