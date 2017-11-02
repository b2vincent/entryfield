<?php

// common required
require_once('Tdl_Config.php');	
require_once('Tdl_Includes.php');

// a specific control    
class Tdl_SimpleFieldsControl extends Ef_Control {

	function controlRow ($oldrow, $newrow) {
		extract($oldrow, EXTR_PREFIX_ALL, 'old');
        // Ef_Log::log ($newrow, 'newrow in control row');
		extract($newrow);

		if ($vsimp_name_0 == '') {
			$this->msgerr .= Ef_Lang::get('name mandatory');
			$this->gravity = 10;
            $this->fieldnames[] = 'vsimp.name'; 
			return false;
		}
		return true;
	}
}

    
class Tdl_SimpleFieldsPart extends Ef_PagePart 
{
    protected $v_simplefields;

    // page part execution : declare, process, display
    public function doRun(&$thispage=null)
    {
        $this->doDeclare();
        
        // go back
        if (isset($_POST['vsimp-but_return-0'])) {
            tdlGotoPage(Ef_Config::get('greetingpage')); 
        }
        
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
        $this->v_simplefields = new Ef_VirtualTable ('v_simplefields','vsimp');
        $vsimp_name = Ef_Field::construct ('vsimp.name', array('type'=>'string','len'=>10));                     
        $vsimp_street = Ef_Field::construct ('vsimp.street', array('type'=>'string','len'=>25));
        
        $vsimp_checked = Ef_Field::construct ('vsimp.checked', array('type'=>'specific', 'class'=>'Ef_FieldChecked'));
        $vsimp_visible = Ef_Field::construct('vsimp.visible', array('type'=>'select','keyvals'=>array('none'=>'None','connected'=>'Connected','public'=>'Public')));
        $vsimp_amount = Ef_Field::construct('vsimp.amount', array('type'=>'amount','nbdec'=>2,'decpoint'=>','));
        $vbut_update = Ef_Field::construct('vsimp.btn_update',
                    array('type'=>'rowbutton','buttonprefix'=>'btn_update','buttontext'=>'Update'));
        
        $this->v_simplefields->buildFieldArray();                                         
    }

    public function doProcess()
    {
        // Ef_Log::log ($_POST, '_POST in Tdl_SimpleFields.php');    

        // we get POST variables of the form vsimp-name-0 into their memory form vsimp_name_0  
        $oldrow = $this->v_simplefields->getMemRowFromSession();    
        $editedrow = $this->v_simplefields->getPostedFieldsInMemRow();
        $this->v_simplefields->setMemRowInSession($editedrow);
        
        // activate controls 
        $_SESSION['errormsgs'] = '&nbsp;';
        $ctrl = new Tdl_SimpleFieldsControl();
        if ($ctrl->controlRow($oldrow, $editedrow) === false) {
            $_SESSION['errormsgs'] .= $ctrl->getMsgErr();
            return;            
        }
    
        //  here is the place to implement complementary actions : 
        //      updating, going to another page, and so on
                 
        // Ef_Log::log($vsimp_name_0,'vsimp_name_0 in Tdl_SimpleFields in process phase ');
        // $memrow = $this->v_simplefields->getMemRowFromSession();
        // Ef_Log::log($memrow,'memrow in Tdl_SimpleFields in display phase');        
    }
    
    
    public function doDisplay(&$thispage=null)
    {
        $tmppage = new Ef_Page();
        // $tmppage->replaceVar('%pagetitle%',Ef_Lang::get("Simple fields"));
        $tmppage->addTemplate("tpl_fullcol.html");
        $tmppage->replaceVar ('%coltitle%', '<br>'.Ef_Lang::get("Please fill this form"));
        $tmppage->replaceVar ('%coltext%', " <p style=\"color:red\">%errormsgs%</p>
        <p>".Ef_Lang::get('updating some simple fields')."</>");
        if (isset($_SESSION['errormsgs']))  
            $tmppage->replaceVar('%errormsgs%',$_SESSION['errormsgs']);
        else 
            $tmppage->replaceVar('%errormsgs%','&nbsp;');
            
        $render = $tmppage->getContent();
        
        // display input form
        // $tmppage->addText('<form role="form" action="Tdl_FormProc.php" method="POST">');
        
        // get the data to display from the session
        $memrow = $this->v_simplefields->getMemRowFromSession();
        $editrow = $this->v_simplefields->getEditRowFromSession();
        
        // In memrow we have 
        // Ef_Log::log($memrow,'memrow in Tdl_SimpleFields in display phase');
        // extract($memrow);
        
        // Ef_Log::log($editrow,'editrow in Tdl_SimpleFields in display phase');
        // Ef_Log::log($vsimp_name_0,'vsimp_name_0 in Tdl_SimpleFields in display phase');        
        
        $render = $editrow['vsimp_name_0']; 
        $render .= $editrow['vsimp_street_0'];
        $render .= $editrow['vsimp_visible_0'];
        $render .= $editrow['vsimp_checked_0'];
        $render .= $editrow['vsimp_amount_0'];
        
        // $render .= $editrow['vsimp_btn_update_0'];

        
        $updatetxt = Ef_Lang::get("Update");
        $submit = ('
        <div>  
        <input type="submit" class="btn" name="vsimp-but_update-0" value="'.$updatetxt.'">
        </div>'
        );
        
        $returntxt = Ef_Lang::get("Return");
        $return = ('
        <br>
        <div>  
        <input type="submit" class="btn" name="vsimp-but_return-0" value="'.$returntxt.'">
        </div>'
        );

        $dumppart = '';
        /*
        $dumpvars = Ef_Log::htmlDump($memrow);
        $dumppart = ("
        <br>
        <div>
        $dumpvars
        </div>
        ");
        */
                  
        $tmppage->addTemplate("tpl_fullcol.html");
        $tmppage->replaceVar ('%coltitle%', (Ef_Lang::get('Simple fields')));
        $tmppage->replaceVar ('%coltext%', $render.$dumppart.$submit.$return);  
        
        return $tmppage->getContent();
        // $tmppage->addText('</form>');    
    }                       
}
	  