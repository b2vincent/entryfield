<?php

// Tdl page part : update item

// Common required
require_once('Tdl_Config.php');	
include_once('Tdl_Includes.php');
	
class Tdl_ItemUpdatePart extends Ef_PagePart 
{
    protected $listreq, $addproj, $delproj;

    // Page part execution : declare, process, display
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

    // Declare requests and business logic
    public function doDeclare() 
    {
        // creating a list request 
        $this->listreq = new Ef_List('itupd', "
        			select %fieldlist% from tdlitem tdlit			
        			%where%
        			%orderby%
        		",'tdlist');

        if (!isset($_GET['itid'])) {
            throw new Exception('Missing item var in _GET');
        }

        $itid = $_GET['itid'];
        
        $this->listreq->setWhere("where tdlit.itid = '$itid'");
        $this->listreq->buildSelectReq();
        
        $this->listreq->setAllFieldState('edit');
        $this->listreq->setFieldState('tdlit.itid','readonly');
        
        // row button : to add record
        $this->addproj = Ef_Field::construct('virtual.btn_add_item',
                    array('type'=>'rowbutton','buttonprefix'=>'btn_add_item',
                    'buttontext'=>Ef_Lang::get('Add'),'rowidname'=>'tdlit_itid'));
        $this->listreq->insertVirtualFieldAtEnd ('virtual.btn_add_item');
        $this->listreq->setFieldState('virtual.btn_add_item','edit');
                
        // row button : to delete record 
        $this->delproj   = Ef_Field::construct('virtual.btn_del_item',
                        array('type'=>'rowbutton','buttonprefix'=>'btn_del_item',
                            'buttontext'=>Ef_Lang::get('Delete'),'rowidname'=>'tdlit_itid'));
        $this->listreq->insertVirtualFieldAtEnd ('virtual.btn_del_item');
        $this->listreq->setFieldState('virtual.btn_del_item','edit');
        
        // register controls : no control here
        
        // build update request
        $this->listreq->setUpdateTable('tdlitem');
        $this->listreq->buildUpdateReq();           
    }

    // Do processing of inputs
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
            $newlineid = Ef_TableUtil::getNewId('tdlitem');
        
    		$insertreq = new Ef_SqlReq(	"
    			insert into tdlitem(itid, it_title) values ('$newlineid', '' ) \n 
    		",'tdlist');
    		$insertreq->execute();
    	}
    	
    	$postedrow = $this->delproj->getPostedRow();
    	if ($postedrow !== false) {    		
    		$delreq = new Ef_SqlReq("
    			delete from tdlitem where itid = '$postedrow' \n 
    		",'tdlist');
    		$delreq->execute();	
    	}	
        
        if (isset($_POST['but_update_goback'])) {
            tdlGotoPage('tdl-item-list'); 
        }
        // echo ("should not echo something here");
    }           
    
    // Display the part
    public function doDisplay(&$thispage=null)
    {
        $tmppage = new Ef_Page();    
        $tmppage->replaceVar ('%errormsgs%', $this->listreq->getErrorText());
            
        // $render = ($this->listreq->getRenderRows(array('variant'=>'simplehtmlform','coltitle'=>'1')));
        $render = ($this->listreq->getRenderRows(array('variant'=>'templatedlist',
                'templatefile'=>'tpl_updateitem.html')));        
        
        
        $tmppage->addTemplate("tpl_fullcol.html");
        $tmppage->replaceVar ('%coltitle%', (Ef_Lang::get('Update item')));
        $tmppage->replaceVar ('%coltext%', $render);  
        	
        $tmppage->addText('<input type="submit" class="btn" 
            name="but_update_goback" value="'.Ef_Lang::get("Update and go back to list").'"> ');
        $tmppage->addText(' <input type="submit" class="btn" 
            name="but_update_item" value="'.Ef_Lang::get("Update and stay here").'">');

        return $tmppage->getContent();                

    }                       
}
	  

