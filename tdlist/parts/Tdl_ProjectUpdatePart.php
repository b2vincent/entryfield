<?php

// Tdl page part : update projects

// Common required
require_once('Tdl_Config.php');	
include_once('Tdl_Includes.php');
	
class Tdl_ProjectUpdatePart extends Ef_PagePart 
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
        $this->listreq = new Ef_List('prupd', "
        			select %fieldlist% from tdlproject tdlpr			
        			%where%
        			%orderby%
        		",'tdlist');
        $this->listreq->buildSelectReq();
        
        $this->listreq->setAllFieldState('edit');
        $this->listreq->setFieldState('tdlpr.prid','readonly');
        
        // row button : to add record
        $this->addproj = Ef_Field::construct('virtual.btn_add_proj',
                    array('type'=>'rowbutton','buttonprefix'=>'btn_add_proj',
                    'buttontext'=>Ef_Lang::get('Add'),'rowidname'=>'tdlpr_prid'));
        $this->listreq->insertVirtualFieldAtEnd ('virtual.btn_add_proj');
        $this->listreq->setFieldState('virtual.btn_add_proj','edit');
                
        // row button : to delete record 
        $this->delproj   = Ef_Field::construct('virtual.btn_del_proj',
                        array('type'=>'rowbutton','buttonprefix'=>'btn_del_proj',
                            'buttontext'=>Ef_Lang::get('Delete'),'rowidname'=>'tdlpr_prid'));
        $this->listreq->insertVirtualFieldAtEnd ('virtual.btn_del_proj');
        $this->listreq->setFieldState('virtual.btn_del_proj','edit');
        
        // register controls : no control here
        
        // build update request
        $this->listreq->setUpdateTable('tdlproject');
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
            $newlineid = Ef_TableUtil::getNewId('tdlproject');
        
    		$insertreq = new Ef_SqlReq(	"
    			insert into tdlproject(prid, pr_title) \n 
    			values ('$newlineid', '' ) \n 
    		",'tdlist');
    		$insertreq->execute();
    	}
    	
    	$postedrow = $this->delproj->getPostedRow();
    	if ($postedrow !== false) {    		
    		$delreq = new Ef_SqlReq("
    			delete from tdlproject  \n 
    			where prid = '$postedrow' \n 
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
        $tmppage->replaceVar ('%coltitle%', (Ef_Lang::get('Projects')));
        $tmppage->replaceVar ('%coltext%', $render);  
        	
        $tmppage->addText('<input type="submit" class="btn" 
            name="but_update_project" value="'.Ef_Lang::get("Submit updates").'">');
        return $tmppage->getContent();                
    }                       
}
	  

