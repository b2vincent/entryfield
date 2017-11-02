<?php

// Build the database if it does not exist
$dbfile = Ef_Config::get('f_sqlitedb_path').'/tdlist.sqlite';
if (!is_readable($dbfile)) {
    Ef_Db::dbOpen('tdlist');
    // Items
    $createsqlreq = new Ef_SqlReq("
        create table if not exists tdlitem 
            ( itid integer primary key,
              it_projid int,
              it_statusid int, 
              it_title text, 
              it_text text,
              it_emergency int,
              it_difficulty int,
              it_datechanged text,
              it_updatedbyid int,
              it_assignedtoid int              
               )
    ",'tdlist');
    $createsqlreq->execute();

    Ef_Log::log($createsqlreq, 'createsqlreq');
    
    $insertsqlreq = new Ef_SqlReq("
        insert into tdlitem (itid, it_title)  values (10000,'boundary');
    ",'tdlist');
    $insertsqlreq->execute();    

    $insertsqlreq = new Ef_SqlReq("
        insert into tdlitem (itid, it_title, it_emergency, it_difficulty, it_projid, it_statusid,
                it_datechanged, it_text)
        values (10001, 'Requirement to do sth', 1, 1, 100, 200, 
                '2016-06-19', 'Please do something, 
                about that, you know. 
                Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry''s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.')                 
    ",'tdlist');
    $insertsqlreq->execute();    

    Ef_Log::log($insertsqlreq, 'insertsqlreq');
    
    // Users
    $createsqlreq = new Ef_SqlReq("
        create table if not exists tdluser  
            ( usid integer primary key,
              us_login text,
              us_password text,
              us_isadmin int 
               )
    ",'tdlist');
    $createsqlreq->execute();
    
    $insertsqlreq = new Ef_SqlReq("
        insert into tdluser (usid, us_login, us_password, us_isadmin) 
         values (50,'entryfield','abcdef','1');",'tdlist');
    $insertsqlreq->execute();    
    $insertsqlreq = new Ef_SqlReq("
        insert into tdluser (usid, us_login, us_password, us_isadmin) 
         values (51,'roger','abcdef','0');",'tdlist');
    $insertsqlreq->execute();    
    $insertsqlreq = new Ef_SqlReq("
        insert into tdluser (usid, us_login, us_password, us_isadmin) 
         values (52,'anna','abcdef','0');",'tdlist');
    $insertsqlreq->execute();    
    
    // Actions
    $createsqlreq = new Ef_SqlReq("
        create table if not exists tdlaction 
            ( acid integer primary key,
              ac_title text, 
              ac_text text,
              ac_date text,
              ac_duration int,
              ac_updatedbyid int,
              ac_assignedtoid int              
               )
    ",'tdlist');
    $createsqlreq->execute();
    
    $insertsqlreq = new Ef_SqlReq("
        insert into tdlaction (acid, ac_title) 
        values (20000,'boundary');
    ",'tdlist');
    $insertsqlreq->execute();
    
    // Application
    $createsqlreq = new Ef_SqlReq("
        create table if not exists tdlapplication 
            ( apid integer primary key,
              ap_title text,
              ap_subtitle text,
              ap_admintype int 
               )
    ",'tdlist');
    $createsqlreq->execute();
    
    $subtitle = Ef_Lang::get("Think, process, solve, Enjoy !");

	// $subtitle = addslashes($subtitle); not working
		
    $insertsqlreq = new Ef_SqlReq("
        replace into tdlapplication (apid, ap_title, ap_subtitle, ap_admintype) 
            values (1,'Tdlist', '$subtitle', 'tabs');
    ",'tdlist');
    $insertsqlreq->execute();

    // Projects
    $createsqlreq = new Ef_SqlReq("
        create table if not exists tdlproject 
            ( prid integer primary key,
              pr_title text 
               )
    ",'tdlist');
    $createsqlreq->execute();
    
    $insertsqlreq = new Ef_SqlReq("
        insert into tdlproject (prid, pr_title) values (100,'main');
    ",'tdlist');
    $insertsqlreq->execute();
    
    // Statuses
    $createsqlreq = new Ef_SqlReq("
        create table if not exists tdlstatus 
            ( stid integer primary key,
              st_title text,
              st_icon text,
              st_isactive int 
               )
    ",'tdlist');
    $createsqlreq->execute();
    
    $stid = 200;
    
    $statrecords = array(array('New', 'fa-bolt', 1),
                         array('In Process', 'fa-gears', 1),
                         array('Done', 'fa-check', 0));
                         
    foreach ($statrecords as $statrecord) {
        $sttitle = $statrecord[0];
        $sticon = $statrecord[1];
        $stisactive = $statrecord[2];        
        $insertsqlreq = new Ef_SqlReq("
        insert into tdlstatus (stid, st_title, st_icon, st_isactive)  
            values ('$stid','$sttitle','$sticon','$stisactive');    
    ",'tdlist');
        $insertsqlreq->execute();
        $stid += 10;        
    }               
    /*          
    foreach (array('New','In Process') as $sttitle) { 
        $insertsqlreq = new Ef_SqlReq("
        insert into tdlstatus (stid, st_title, st_isactive)  values ('$stid','$sttitle',1);    
    ",'tdlist');
        $insertsqlreq->execute();
        $stid += 10;
    }
    foreach (array('Done','Canceled','Parked') as $sttitle) {
        $insertsqlreq = new Ef_SqlReq("
        insert into tdlstatus (stid, st_title, st_isactive)  values ('$stid','$sttitle',0);    
    ",'tdlist');
        $insertsqlreq->execute();
        $stid += 10;    
    } 
    */
    
    Ef_Db::dbClose('tdlist');
    Ef_Db::dbOpen('tdlist');          
} else {
    Ef_Db::dbOpen('tdlist');
}
                   

// Schema

// To do items
$projectarray = tdlGetProjects();
$statusarray = tdlGetStatus();
$userarray = tdlGetUsers();
$tdlitem = new Ef_SqlTable('tdlitem','tdlit','tdlist');
$tdlit_itid = Ef_Field::construct('tdlit.itid', array('type'=>'int', 'keypos'=>'0'));
$tdlit_projid = Ef_Field::construct('tdlit.it_projid',  array('type'=>'select','keyvals'=>$projectarray));
$tdlit_statusid = Ef_Field::construct('tdlit.it_statusid', array('type'=>'select','keyvals'=>$statusarray));
$tdlit_title = Ef_Field::construct('tdlit.it_title', array('type'=>'string','len'=>'40','maxlen'=>'100'));
// ,'withlabel'=>'1'
$tdlit_text = Ef_Field::construct('tdlit.it_text', array('type'=>'text','cols'=>'40','rows'=>'5'));
$tdlit_emergency = Ef_Field::construct('tdlit.it_emergency', array('type'=>'int'));
$tdlit_datechanged = Ef_Field::construct('tdlit.it_datechanged', array('type'=>'date'));
$tdlit_updatedbyid = Ef_Field::construct('tdlit.it_updatedbyid',  array('type'=>'select','keyvals'=>$userarray));
$tdlit_assignedtoid = Ef_Field::construct('tdlit.it_assignedtoid',  array('type'=>'select','keyvals'=>$userarray));
$tdlitem->buildFieldArray();

// Users
$tdluser = new Ef_SqlTable ('tdluser','tdlus','tdlist');
$tdlus_id = Ef_Field::construct ('tdlus.usid', array('type'=>'int', 'keypos'=>'0','len'=>5));
$tdlus_login = Ef_Field::construct ('tdlus.us_login', array('type'=>'string','len'=>12));
$tdlus_password = Ef_Field::construct ('tdlus.us_password', array('type'=>'string','len'=>12,'password'=>1));
$tdluser->buildFieldArray();

// Application
$tdlapplication = new Ef_SqlTable ('tdlapplication','tdlap','tdlist');
$tdlap_id = Ef_Field::construct ('tdlap.apid', array('type'=>'int', 'keypos'=>'0','len'=>5));
$tdlap_title = Ef_Field::construct ('tdlap.ap_title', array('type'=>'string','len'=>24));
// $tdlap_subtitle = Ef_Field::construct ('tdlap.ap_subtitle', array('type'=>'string','len'=>40));
$tdlap_subtitle = Ef_Field::construct('tdlap.ap_subtitle', array('type'=>'text','cols'=>'60','rows'=>'6'));
$tdlap_admintype = Ef_Field::construct('tdlap.ap_admintype', array('type'=>'radio',
        'keyvals'=>array('list'=>Ef_Lang::get('Admin page as a list'),
                         'tabs'=>Ef_Lang::get('Admin page with tabs'))));
$tdlapplication->buildFieldArray();

// Projects
$tdlproject = new Ef_SqlTable ('tdlproject','tdlpr','tdlist');
$tdlpr_id = Ef_Field::construct ('tdlpr.prid', array('type'=>'int', 'keypos'=>'0','len'=>5));
$tdlpr_title = Ef_Field::construct ('tdlpr.pr_title', array('type'=>'string','len'=>24));
$tdlproject->buildFieldArray();

// Statuses
$tdlstatus = new Ef_SqlTable ('tdlstatus','tdlst','tdlist');
$tdlst_id = Ef_Field::construct ('tdlst.stid', array('type'=>'int', 'keypos'=>'0','len'=>4));
$tdlst_title = Ef_Field::construct ('tdlst.st_title', array('type'=>'string','len'=>16));
$tdlst_icon = Ef_Field::construct ('tdlst.st_icon', array('type'=>'string','len'=>16));
$tdlst_isactive = Ef_Field::construct('tdlst.st_isactive', array('type'=>'specific', 'class'=>'Ef_FieldChecked'));
$tdlstatus->buildFieldArray();


 
?>