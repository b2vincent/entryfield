<?php

// Tdl page part : list of items

// Common required
require_once('Tdl_Config.php');	
require_once('Tdl_Includes.php');


// Common declarations : class and functions
	
// A specific control    
class Tdl_ItemListControl extends Ef_Control {

	function controlRow ($oldrow, $newrow) {
		extract($oldrow, EXTR_PREFIX_ALL, 'old');
        // Ef_Log::log ($newrow, 'newrow in control row');
		extract($newrow);
		if ($tdlit_it_title_0 == '') {
			$this->msgerr .= Ef_Lang::get('title mandatory');
			$this->gravity = 10;
            $this->fieldnames[] = 'tdlit.it_title'; 
			return false;
		}
		return true;
	}
}
    
class Tdl_ItemListPart extends Ef_PagePart 
{
    protected $listreq, $delitem;

    // page part execution : declare, process, display
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

    // declare requests and business logic
    public function doDeclare() 
    {
        // creating a list request 
        $this->listreq = new F_List('TdList', "
        			select %fieldlist% from tdlitem tdlit	%where% %orderby%
        		",'tdlist');
        $this->listreq->setUpdateTable('tdlitem');
        // $this->listreq->setOrderBy('order by tdlit.itid desc');
        $this->listreq->setWhere("where tdlit.it_title != 'boundary'"); 
        $this->listreq->setOrderBy("order by tdlit.itid desc");
        $this->listreq->buildSelectReq();
        // F_Log::htmlDump($this->listreq->getSqlQuery(),'sqlQuery : ');
        $this->listreq->setAllFieldState('view');
        $this->listreq->setFieldState('tdlit.it_projid','disabled');
        $this->listreq->setFieldState('tdlit.it_statusid','disabled');
        $this->listreq->setFieldState('tdlit.it_assignedtoid','disabled');
        

        
        // row buttons : to update and delete item        
        $this->upditem = F_Field::construct('virtual.btn_upditem',
                    array('type'=>'rowbutton','buttonprefix'=>'btn_upditem',
                    'buttontext'=>F_Lang::get('Update item'),'rowidname'=>'tdlit_itid'));
        $this->listreq->insertVirtualFieldAtEnd ('virtual.btn_upditem');
        $this->listreq->setFieldState('virtual.btn_upditem','edit');
                        
        $this->delitem   = F_Field::construct('virtual.btn_delitem',
                        array('type'=>'rowbutton','buttonprefix'=>'btn_delitem',
                            'buttontext'=>F_Lang::get('Del item %1', array('%row%')),'rowidname'=>'tdlit_itid'));
        $this->listreq->insertVirtualFieldAtEnd ('virtual.btn_delitem');
        $this->listreq->setFieldState('virtual.btn_delitem','edit');
        
        // $this->listreq->setAllFieldState('view');
        // link to one item update
        $this->listreq->setFieldState('tdlit.itid','link');
        Ef_Config::set('tdlit.itid_linkpage','tdl-item-update');
        Ef_Config::set('tdlit.itid_linkcols','tdlit.itid');
        Ef_Config::set('tdlit.itid_linkargs','itid');        

                
        $this->listreq->buildUpdateReq();    
    }

    public function doProcess()
    {
    	// F_Log::htmlDump($_POST,'_POST');
    	if (isset($_POST['but_addnew'])) {
            $newlineid = F_TableUtil::getNewId('tdlitem');    
    		$insertreq = new F_SqlReq ("
                insert into tdlitem (itid, it_title) \n  values ($newlineid, '' ) \n 
    		",'tdlist');
    		$insertreq->execute();
            
            tdlGotoPage('tdl-item-update?itid='.$newlineid);            
    	}
         
        /* to_delete_
    	if (isset($_POST['but_update'])) {
    		if ($this->listreq->processControl()) {	
    			$this->listreq->processUpdate();
    		}	        
    	}
        */
        
    	$postedrow = $this->upditem->getPostedRow();
    	if ($postedrow !== false) {
            tdlGotoPage('tdl-item-update?itid='.$postedrow);    
    	}
       	
    	$postedrow = $this->delitem->getPostedRow();
    	if ($postedrow !== false) {		
    		$delreq = new F_SqlReq ("delete from tdlitem  \n  where itid = '$postedrow' \n 
    		",'tdlist');
    		$delreq->execute();	
    	}	
    }
    
    
    public function doDisplay(&$thispage=null)
    {
        $tmppage = new Ef_Page();
        $tmppage->addTemplate("tpl_fullcol.html");
        $updatetxt = Ef_Lang::get("Update");
        $submit = ('<div>  
        <input type="submit" class="btn" name="but_addnew" value="'.F_Lang::get('Add a new item').'">
        </div>');
        // to_delete_ <input type="submit" class="btn" name="but_update" value="'.$updatetxt.'">

        // $tmppage->replaceVar ('%coltitle%', '<br>'.Ef_Lang::get(""));
        $tmppage->replaceVar ('%coltitle%', $submit);
        $tmppage->replaceVar ('%coltext%', "<p style=\"color:red\">%errormsgs%</p>");
        $tmppage->replaceVar ('%errormsgs%', $this->listreq->getErrorText());
        

        // $render = ($this->listreq->getRenderRows(array('variant'=>'simplehtmltable','rowtitle'=>'1')));
        $render = ($this->listreq->getRenderRows(array('variant'=>'templatedlist',
                'templatefile'=>'tpl_listitem.html')));        
                  
        $tmppage->addTemplate("tpl_fullcol.html");
        $tmppage->replaceVar ('%coltitle%', (Ef_Lang::get('Item List')));

        $addnew = '<input type="submit"name="but_addnew" value="'.F_Lang::get('Add new').'">';

        $tmppage->replaceVar ('%coltext%', $render.$submit);  
        
        return $tmppage->getContent();
    }                       
}
	  
