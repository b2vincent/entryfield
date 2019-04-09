<?php

// This page is an all-in-one example of list management built using Entryfield
// 
// It has been written for didactic purposes, to explain different components used
// to build a complete page and their usual place (for an application named "App").

// INITIALIZE : usually in App_Config.php and other places

// Ensure that in case of an error we will see it
require_once('../helpers/ErrorHandler.php');

// Indicate where is EntryField then include it  : usually in App_Config.php
ini_set('include_path',"../../F_Lib".PATH_SEPARATOR.get_include_path());
include_once('F_Field.php');
include_once('F_FieldExtended.php'); // elements in this file are not considered in the kernel

// Framework extensions / specializations : these includes are usually in App_Include.php
include_once('../extends/Tdl_Extends.php');
include_once('../extends/Tdl_Field.php');
include_once('../extends/Tdl_ListViewExtended.php');

// Declare how to access to the database ; usually in App_Config.php
Ef_Config::set('f_sqlitedb_path', '../sqlitedb'); 
Ef_Config::set('f_db_database','tdlist','tdlist');
Ef_Config::set('f_db_dbtype','sqlite','tdlist');

// Build the database if it does not exist : usually in App_Schema.php, or somewhere outside application.
$dbfile = Ef_Config::get('f_sqlitedb_path').'/tdlist.sqlite';
if (!is_readable($dbfile)) {
    Ef_Db::dbOpen('tdlist');
    $createsqlreq = new Ef_SqlReq("
        create table if not exists tdlitem 
            ( itid integer primary key,
              it_projid int,         it_statusid int, 
              it_title text,         it_text text,
              it_emergency int,     it_datechanged text,   
              it_updatedbyid int,    it_assignedtoid int              
               )
    ",'tdlist');
    $createsqlreq->execute();
    
    $insertsqlreq = new Ef_SqlReq("
        insert into tdlitem (itid, it_title)  values (10000,'boundary'); 
    ",'tdlist');
    $insertsqlreq->execute();
    Ef_Db::dbClose('tdlist');          
} 

// DECLARATION
//     In this part of the script we will declare what we use    

// Start session - check connection 
Ef_Session::start();
if (!Ef_Session::getVal('login')) {
    Ef_Session::appendMessage('TdlLogin', Ef_Lang::get('You must be connected to see this page'));
    header("Location: ../../tdl-login");
}

// Starting database connection : usually in App_Config.php or App_Model.php
Ef_Db::dbOpen('tdlist');

// Declare schema : usually in App_Schema.php
$tdlitem = new Ef_SqlTable('tdlitem','tdlit','tdlist');
$tdlit_id = Ef_Field::construct('tdlit.itid', array('type'=>'int', 'keypos'=>'0'));
$tdlit_type = Ef_Field::construct('tdlit.it_title', array('type'=>'string','len'=>'40','maxlen'=>'100'));
$tdlit_type = Ef_Field::construct('tdlit.it_text', array('type'=>'text','cols'=>'40','rows'=>'5'));
$tdlitem->buildFieldArray();
 
// Declare translation names for columns : usually in App_Lang_xx.php
Ef_Lang::set('tdlit.itid','Id');
Ef_Lang::set('tdlit.it_title','Title');
Ef_Lang::set('tdlit.it_text','Description');
Ef_Lang::set('virtual.btn_upditem','Update item');
Ef_Lang::set('virtual.btn_delitem','Delete item');

// Create a list request : usually in App_ThisPagePart.php / method doDeclare()
$listreq = new Ef_List('SimpleList', " select %fieldlist% from tdlitem tdlit	%where% %orderby%",'tdlist');
$listreq->setWhere("where tdlit.it_title != 'boundary'");
$listreq->setUpdateTable('tdlitem');
$listreq->setOrderBy('order by tdlit.itid ');
$listreq->buildSelectReq();
// want to see sql select query ? uncomment next line 
// Ef_Log::htmlDump($listreq->getSqlQuery(),'sqlQuery : ');
$listreq->setAllFieldState('edit');
$listreq->setFieldState('tdlit.itid','readonly');

// Row buttons : to update and delete item
$upditem = Ef_Field::construct('virtual.btn_upditem',
            array('type'=>'rowbutton','buttonprefix'=>'btn_upditem',
            'buttontext'=>Ef_Lang::get('Update item'),'rowidname'=>'tdlit_itid'));
$listreq->insertVirtualFieldAtEnd ('virtual.btn_upditem');
$listreq->setFieldState('virtual.btn_upditem','edit');
        
$delitem   = Ef_Field::construct('virtual.btn_delitem',
                array('type'=>'rowbutton','buttonprefix'=>'btn_delitem',
                    'buttontext'=>Ef_Lang::get('Del item %1', array('%row%')),'rowidname'=>'tdlit_itid'));
$listreq->insertVirtualFieldAtEnd ('virtual.btn_delitem');
$listreq->setFieldState('virtual.btn_delitem','edit');

// Build update query
$listreq->buildUpdateReq();
// want to see the template of sql update query ? uncomment next line 
// Ef_Log::htmlDump($listreq->getUpdateQuery(),'update query : ');   


// PROCESS
//     Here we will process input data 
//     usually in App_ThisPagePart.php / method doProcess()   

// Processing input data
if (count($_POST) > 0) {
	// Ef_Log::htmlDump($_POST,'_POST');
	if (isset($_POST['but_addnew']) && $_POST['but_addnew'] != '') {
        $newlineid = Ef_TableUtil::getNewId('tdlitem');    
		$insertreq = new Ef_SqlReq (
            "insert into tdlitem (itid, it_title) \n  values ($newlineid, '' ) \n ",'tdlist');
		$insertreq->execute();
	}
	$postedrow = $upditem->getPostedRow();
	if ($postedrow !== false) {
		if ($listreq->processControl()) {	
			$listreq->processUpdate();
		}	        
	}	
	$postedrow = $delitem->getPostedRow();
	if ($postedrow !== false) {		
		$delreq = new Ef_SqlReq (
            "delete from tdlitem  \n  where itid = '$postedrow' \n ",'tdlist');
		$delreq->execute();	
	}
    if (isset($_POST['but_orient'])) {
        $_SESSION['orient'] = ($_SESSION['orient'] == 'vertical') ?  'horizontal' : 'vertical'; 
    }
}

// DISPLAY
//     Now we will display the result    
//     usually in App_ThisPage.php and in App_ThisPagePart.php / method doDisplay()   

// Where are the templates 
Ef_Config::set('f_template_path', '../templates/united');

// listview configuration : specialized, it is a bootstrap version
Ef_Config::set('listview', 'Ef_ListViewExtended');

$thispage = new Ef_Page();
// Insert header template
$thispage->addTemplate("tpl_header.html");

// Build menu inside header 
$tmppage = new Ef_Page();
$tmppage->addTemplate("tpl_headmenu.html");
$link = '<li><a href="../../tdl-item-list">Return</a></li>';
$tmppage->replaceVar ('%leftmenu%', $link);
$tmppage->replaceVar('%rightmenu%', '');
$thispage->replaceVar('%menu%', $tmppage->getContent());

// Build page content from template
$thispage->replaceVar('%baserelpath%', '../');
$thispage->replaceVar('%designrelpath%', Ef_Config::get('f_template_path'));
$thispage->replaceVar('%pagetitle%', Ef_Lang::get('A simple list'));
$thispage->replaceVar('%apptitle%', 'Tdlist');
$thispage->replaceVar('%appsubtitle%', 'Simple list management');
$thispage->addText('
        <body><center><form  method="POST">
        <div class="container"> <div class="row">
        <br><br>
        <h2>%coltitle% </h2> 
        %changeorient%  
        <p>%coltext% </p> 
        %addnew%
        </div></div>
');
$thispage->replaceVar ('%coltitle%', (Ef_Lang::get('An updating list showing as table or forms')));

// Initialize or change grid orientation
if (!isset($_SESSION['orient'])) {
    $_SESSION['orient'] = 'horizontal';
}	
if ($_SESSION['orient'] == 'vertical') {
    $render = ($listreq->getRenderRows(array('variant'=>'simplehtmlform','coltitle'=>'1')));
} else {    
    $render = ($listreq->getRenderRows(array('variant'=>'simplehtmltable','rowtitle'=>'1')));
}

$thispage->replaceVar ('%coltext%', $render);  

$thispage->replaceVar ('%addnew%','<input type="submit" class="btn" name="but_addnew" value="'.Ef_Lang::get('Add new element').'">');
$thispage->replaceVar ('%changeorient%','<input type="submit" class="btn" name="but_orient" value="'.Ef_Lang::get('Change orientation').'">');

$thispage->addText('</form></center></body</html>');    
$thispage->render();
     