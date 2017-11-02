<?php

// Tdl page part : update application parameters

// Common required
require_once('Tdl_Config.php');	
include_once('Tdl_Includes.php');
	
class Tdl_ApplicationUpdatePart extends Ef_PagePart 
{
    protected $listreq;

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
        $this->listreq = new Ef_List('apupd', "
        			select %fieldlist% from tdlapplication tdlap			
        			%where%
        			%orderby%
        		",'tdlist');
        $this->listreq->buildSelectReq();
        
        $this->listreq->setAllFieldState('edit');
        $this->listreq->setFieldState('tdlap.apid','hidden');
        
        // register controls : no control here
        
        // build update request
        $this->listreq->setUpdateTable('tdlapplication');
        $this->listreq->buildUpdateReq();           
    }

    public function doProcess()
    {
    	// Ef_Log::htmlDump($_POST,'_POST');
        if (isset($_POST)) {
    		if ($this->listreq->processControl()) {	
    			$this->listreq->processUpdate();
    		}	
    	}    
    }           
    
    public function doDisplay(&$thispage=null)
    {
        $tmppage = new Ef_Page();    
        $tmppage->replaceVar ('%errormsgs%', $this->listreq->getErrorText());
            
        $render = ($this->listreq->getRenderRows(array('variant'=>'simplehtmlform','coltitle'=>'1')));
        
        $tmppage->addTemplate("tpl_fullcol.html");
        $tmppage->replaceVar ('%coltitle%', (Ef_Lang::get('Settings')));
        $tmppage->replaceVar ('%coltext%', $render);  
        	
        $tmppage->addText('<input type="submit" class="btn" 
            name="but_update_application" value="'.Ef_Lang::get("Submit updates").'">');
        
        // $tmppage->addText('</form>');    
                
        return $tmppage->getContent();
                
    }                       
}
	  

