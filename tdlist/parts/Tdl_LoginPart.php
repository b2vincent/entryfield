<?php

// Tdl page part : login 

// Common required
require_once('Tdl_Config.php');	
include_once('Tdl_Includes.php');

// a specific control    
class LoginControl extends Ef_Control 
{

	function controlRow ($oldrow, $newrow) 
    {
		extract($oldrow, EXTR_PREFIX_ALL, 'old');
        // Ef_Log::log ($newrow, 'newrow in control row');
		extract($newrow);

		if ($vlog_login == '' || $vlog_password == '') {
			$this->msgerr .= Ef_Lang::get('Login and password are mandatory');
            Ef_Session::appendMessage('TdlLogin', $this->msgerr);
			return false;
		}

        if (tdlUserCheckPassword ($vlog_login, $vlog_password) == false) {
			$this->msgerr .= Ef_Lang::get('Inexisting login / password combination');
            Ef_Session::appendMessage('TdlLogin', $this->msgerr);
            $this->fieldnames = array('vlog.login','vlog.password');
			return false;        
        }		
		return true;
	}
}

class Tdl_LoginPart extends Ef_PagePart 
{
    protected $listreq, $connectUser, $disconnectUser;
     
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
        // declare virtual table for login
        $v_login = new Ef_VirtualTable ('v_login','vlog');
        $vlog_login = Ef_Field::construct ('vlog.login', array('type'=>'string','len'=>25));                     
        $vlog_password = Ef_Field::construct ('vlog.password', 
                array('type'=>'string','len'=>25,'password'=>true));
        $v_login->buildFieldArray();                                 
        
        // creating a session list request 
        $this->listreq = new Ef_SessionList('login');
        $this->listreq->buildFromFieldArray(array('vlog.login', 'vlog.password'));
        
        $this->listreq->setAllFieldState('edit');
        
        // row button : to connect
        $this->connectUser = Ef_Field::construct('virtual.btn_connect_user',
                array('type'=>'rowbutton','buttonprefix'=>'btn_connect_user','buttontext'=>'Connect'));
        $this->listreq->insertVirtualFieldAfter ('virtual.btn_connect_user', 'vlog.password');
        $this->listreq->setFieldState('virtual.btn_connect_user','edit');
        
        // and to disconnect
        $this->disconnectUser = Ef_Field::construct('virtual.btn_disconnect_user',
                array('type'=>'rowbutton','buttonprefix'=>'btn_disconnect_user','buttontext'=>'Disconnect'));
        $this->listreq->insertVirtualFieldAtEnd ('virtual.btn_disconnect_user');
        $this->listreq->setFieldState('virtual.btn_disconnect_user','edit');
        
        // register controls
        $this->listreq->registerControl(array('LoginControl','controlRow'));            
    }

    // process POST input
    public function doProcess()
    {     
    	$postedrow = $this->connectUser->getPostedRow();
    	if ($postedrow !== false) {
    	
    		if ($this->listreq->processControl()) {	
                $postedarray = $this->listreq->getPostedArray();
                $postedlogin = Ef_Field::getPostnameFromNameIrow('vlog.login', 0);
                tdlUserSetConnected($postedarray[$postedlogin]); 
                tdlGotoPage(Ef_Config::get('greetingpage'));          		               
    		} 	        
    	}
    	$postedrow = $this->disconnectUser->getPostedRow();
    	if ($postedrow !== false) {
            tdlUserDisconnect(); 
            Ef_Session::delete();
        }
        return;	
             
    }    
    
    // display
    public function doDisplay(&$thispage=null)
    {        
    
        $tmppage = new Ef_Page();

        $render = '';
        $errorMessage = Ef_Session::getMessages('TdlLogin');
        // Ef_Log::log($errorMessage,'errorMessage');
        if ($errorMessage != '') {
            // $tmppage->addText(tdlRenderMessage($errorMessage));
            $render .= tdlRenderMessage($errorMessage); 
            Ef_Session::clearMessages('TdlLogin');        
        }    
        
        // $render .= ($this->listreq->getRenderRows(array('variant'=>'simplehtmlform','coltitle'=>'0')));
        $render .= ($this->listreq->getRenderRows(array('variant'=>'bootstrapform')));
        
        $tmppage->addTemplate("tpl_midsizecol.html");
        $tmppage->replaceVar ('%coltitle%', '');  
        $tmppage->replaceVar ('%coltext%', Ef_Lang::get('Logintext'));  
        
        $tmppage->addTemplate("tpl_midsizecol.html");

        $tmppage->replaceVar ('%coltitle%', (Ef_Lang::get('Connect')));
        $tmppage->replaceVar ('%coltext%', Ef_Lang::get('Cheattext') . $render); 
        
        return ($tmppage->getContent());    
        
    }                       
}
