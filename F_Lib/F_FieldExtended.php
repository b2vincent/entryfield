<?php
// Copyright Vincent Wartelle & Oklin.com 2016-2019
// EntryField framework - Open sourced under MIT license  

// This source file contains extensions to the base frameworks

// Extension to render engine F_ListView, more complete and realistic
class F_ListViewExtended extends F_ListView 
{

	public function setVariantHtml ($argvariant = 'simplehtmltable', $parms=array()) 
    {
		$this->variant = $argvariant;
		switch ($argvariant) {
		    case 'simplehtmltable':
                $this->setHeader ("<div class=\"table-responsive\"> <table class=\"table table-striped table-responsive table-bordered table-hover table-condensed\"> \n\n");
                $this->setLineSep ("<tr>\n", "</tr>\n\n");
                $this->setColSep ("<td>", "</td>\n");
                $this->setHeadColSep ("<th>", "</th>\n");
                $this->setFooter ("</table></div>\n\n\n");			
				$this->setFieldEditSep ('<div>','</div>');				
				$this->setFieldErrorSep ('<div class="alert alert-danger">','</div>');		
		        break;

		    case 'simplehtmllist':
                // $this->setHeader ("<pre>");
				$this->setHeader ("<span>\n");
                $this->setLineSep ("&nbsp;", "<br>\n\n");
                $this->setColSep (" ", "\n");
                $this->setHeadColSep (" ", "\n");
				$this->setFooter ("</span>\n\n");	
                /// $this->setFooter ("</pre>");			
		        break;

		    case 'simplehtmlform':
                // rowtitle is mandatory !! todo
                /* 
				$this->setHeader ("\n\n");
                $this->setLineSep ("<table class=\"table table-striped table-responsive table-bordered table-hover table-condensed\"> \n\n"
                    , "</table><br>\n\n ");
                $this->setColSep ('<td style="border: 1px solid gray;"> ', "</td></tr>\n");
                $this->setHeadColSep ('<tr><th style="border: 1px solid gray;">', "</th>\n");
				$this->setFooter ('');
				$this->setFieldEditSep ('<span>','&nbsp;</span>');								
				$this->setFieldErrorSep ('<span class="alert alert-danger">','</span>');		
                */
				$this->setHeader ("\n\n");
                $this->setLineSep ("<table class=\"table table-striped table-responsive table-bordered table-hover table-condensed\"> \n\n"
                    , "</table><br>\n\n ");
                // $this->setLineSep ('<div>', '</div><div class="row"><hr></div>');
                $this->setHeadColSep ('<tr><th>', "</th>\n");
                $this->setColSep ('<td colspan="3"> ', "</td></tr>\n");
                // $this->setHeadColSep ('<div class="col-md-12"><span class="col-md-3">', "</span>\n");
                // $this->setColSep ('<span class="col-md-9">', "</span></div> \n");
				$this->setFooter ('');
				$this->setFieldEditSep ('<span>','&nbsp;</span>');								
				$this->setFieldErrorSep ('<span class="alert alert-danger">','</span>');		
                			
		        break;

            case 'templatedlist':
                $this->setLineSep ("", "");
                $this->setRenderFunc('renderThroughTemplate');
                break;                     
		        
            case 'templatedlistadvanced':
                $this->setLineSep ("", "");
                $this->setRenderFunc('renderTemplateOrSequence');
                break;                     
		        
                
		    default:
		        throw new Exception ("news_F_ListView cannot set variant,  variant : $argvariant does not exist");
		        break;
		}		
	}	

    // render through template : 
    // -    either template file name is given in $parms['templatefile'],
    // -    either template file name is given in a column identified by $parms['templatefield']
    // -    either template content is given in $parms['templatecontent']
    public function renderThroughTemplate (&$F_List, $parms=array()) 
    {
    		 
        $renderresult = $this->header;
        $irow = 0;
        
        if (!isset($parms['templatecontent'])) {
            $templatepath = Ef_Config::get('f_template_path');  
            if (!is_dir($templatepath) || !is_readable($templatepath)) {    
                throw new Exception ("cannot get templates array,  directory not readable");
                return null;
            }
            // $loadedtemplates = array();
                    
            if (!isset($parms['templatefield']) && !isset($parms['templatefile'])) {
                throw new Exception ("cannot identify template, please set templatefield or templatefile parameter");
                return null;            
            }
        }
        // templates are $templatepath/$templatename
    	$fieldarray = $F_List->getFieldArray();
        $listedarray = $F_List->getListedArray();
        $completedarray = $F_List->extractCompletedFieldArray();
        // Ef_Log::log($completedarray,'completedarray');
            
    	while (true) {
    		$resultrow = $F_List->extractCompletedRow($irow);	

    		if ($resultrow === false )
    			break;

            $rowvalues = array();
            $rowshortnames = array();
            foreach ($completedarray as $key => $fieldname) {
                $shortname = Ef_Field::getShortnameFromName ($fieldname);
                $rowvalues[$shortname] = $resultrow[$key];
                $rowshortnames[] = $shortname;
                // Ef_Log::log ($varname, "set the var to $resultrow[$key]"); 
            }                 

            if (isset($parms['templatecontent'])) {
                $templatecontent = $parms['templatecontent'];
            } else {
                if (isset($parms['templatefile'])) {
                    $templatefile = $parms['templatefile'];
                } else {
                    $templatefile = $rowvalues[$parms['templatefield']];
                }
                $templatecontent = Ef_Page::getTemplateContent($templatepath.'/'.$templatefile);                                                        
            }                          
            $renderresult .= $this->linesep[0];            
            // load the template in the render
            $renderresult .= $templatecontent;                 		
    		// $fieldvalues = $this->buildFieldValues ($fieldarray, $resultrow);
            $fieldvalues = $this->buildFieldValues ($completedarray, $resultrow);
    		    	
            $icol = 0;
            foreach ($resultrow as $fieldvalue) {
                                    	
                $fieldname = $completedarray[$icol];
                $shortname = $rowshortnames[$icol];    

                $field = Ef_Field::findByName ($fieldname);
                
                if (is_object ($field)) {
                    // TODO : replace viewvalue by edit/or/view according to state
                    // $viewvalue =  $field->memToViewHtml ($fieldvalue,
					//			array('irow'=>$irow,'fieldvalues'=>$fieldvalues));
                    $viewvalue = $this->renderField($F_List, $field, $fieldvalue, $irow, $fieldvalues);								
                } else {
                    $viewvalue = $fieldvalue;	
                }
                // replace %colname% by $viewvalue
                $renderresult = str_replace ('%'.$shortname.'%', $viewvalue, $renderresult);
                $icol++;                
            }
            $renderresult .= $this->linesep[1];               
            $irow++;
            
        }
        
        $renderresult .= $this->footer;
        return $renderresult;
    			
    }

    
    // Render through a template or through a sequence
    public function renderTemplateOrSequence (&$f_list, $parms=array()) {
		 
    	$fieldarray = $f_list->getFieldArray();
        $listedarray = $f_list->getListedArray();
        $completedarray = $f_list->extractCompletedFieldArray();
        
        $renderresult = $this->header;
        $irow = 0;

        $templatepath = Ef_Config::get('f_template_path');  
        if (!is_dir($templatepath) || !is_readable($templatepath)) {    
            throw new Exception ("cannot get templates array,  directory not readable");
            return null;
        }        
        if (!isset($parms['templatefield'])) {
            throw new Exception ("cannot identify template field, please set templatefield parameter");
            return null;            
        }    
        $templatefield = $parms['templatefield'];
        if (isset($parms['sequencefield'])) {
            $sequencefield = $parms['sequencefield'];      
        }

        $lasttemplatefile = '';
        $lastsequencefile = '';            

    	while (true) {
        
    		$resultrow = $f_list->extractCompletedRow($irow);	
            $fieldvalues = $this->buildFieldValues($fieldarray, $resultrow);
            $rowvalues = array();
            $rowshortnames = array();
            foreach ($completedarray as $key => $fieldname) {
                $shortname = Ef_Field::getShortnameFromName($fieldname);
                $rowvalues[$shortname] = $resultrow[$key];
                $rowshortnames[] = $shortname;
            }                 
            // Ef_Log::log($rowvalues, 'rowvalues in renderTemplateOrSequence');

            // load template file if its name is new
            $templatefile = $rowvalues[$templatefield];  
            if ($templatefile != $lasttemplatefile) {                  
                $templatecontent = '';
                if ($templatefile != '' && is_readable("$templatepath/$templatefile")) {                        
                    $templatecontent = Ef_Page::getTemplateContent($templatepath.'/'.$templatefile);
                } else {
                    $templatecontent =  Ef_Lang::get("missing file $templatefile in  $templatepath<br>\n "); 
                }
            }                            

            // process sequence rendering
            $sequencefile = $rowvalues[$sequencefield];

            // finish sequence if we were in a sequence
            if ($lastsequencefile != '' && $sequencefile != $lastsequencefile) { 
                $lastsequenceprefix = substr($lastsequencefile, 0 , (strrpos($lastsequencefile, '.')));
                $funcname = $lastsequenceprefix.'_finish';
                if (is_callable($funcname))
                    $renderrow = call_user_func($funcname);
                $renderresult .= $renderrow;
            }

            // process a sequence row
            if ($sequencefile != '') {
                // process if this row is first in a sequence 
                if ($sequencefile != $lastsequencefile) {
                    // $sequencefile = $rowvalues[$sequencefield]; 
                    // sequence prefix : file without extension
                    if ( !is_readable("$templatepath/$sequencefile")) {
                        $renderresult .= Ef_Lang::get("missing file $sequencefile in  $templatepath<br>\n "); 
                    }
                    else {
                        include_once("$templatepath/$sequencefile");                        
                    } 
                    $sequenceprefix = substr($sequencefile, 0 , (strrpos($sequencefile, '.')));
                    $firstinsequence = true;                                              
                }
                // process for each sequence row                   
                $funcname = $sequenceprefix.'_processrow'; 
                if (is_callable($funcname))
                    call_user_func ($funcname, $fieldvalues, $firstinsequence);
                $firstinsequence = false;
            }

            // process template rendering            
            if ($resultrow !== false && $templatefile != '') {
                $renderrow = $this->renderRowTemplateOrSequence( 
                        $templatecontent, $fieldarray, $resultrow, $rowshortnames, $fieldvalues, $irow);
                $renderresult .= $renderrow;
            } 
            // Ef_Log::log ($renderresult,'renderresult in renderTemplateOrSequence');
               
            $irow++;
            $lasttemplatefile = $templatefile;
            $lastsequencefile = $sequencefile;

    		if ($resultrow === false )
    			break;

        }
        
        $renderresult .= $this->footer;
        return $renderresult;
    			
    }
    
    private function renderRowTemplateOrSequence ( 
            $templatecontent, $fieldarray, $resultrow, $rowshortnames, $fieldvalues, $irow)
    {
        $renderrow = '';
        $renderrow .= $this->linesep[0];            
        // load the template in the render
        $renderrow .= $templatecontent;             		
		
        // Ef_Log::log ($fieldvalues,'fieldvalues in renderTemplateOrSequence');   
        // fieldvalues in renderRowTemplateOrSequence: Array (
        //   [efit_id] => 3   [efit_title] => What is EntryField
        //  [...] =>          [efit_sequence] =>  seq_carousel.php   [efit_template] => tplnews_twocols.html
		    	
        $icol = 0;
        foreach ($resultrow as $fieldvalue) {
                                	
            $fieldname = $fieldarray[$icol];
            $shortname = $rowshortnames[$icol];    

            $field = Ef_Field::findByName ($fieldname);
            
            if (is_object($field)) {
                $viewvalue =  $field->memToViewHtml ($fieldvalue,
							array('irow'=>$irow,'fieldvalues'=>$fieldvalues));
            } else {
                $viewvalue = $fieldvalue;	
            }
            // in the template, replace %colname% by $viewvalue
            $renderrow = str_replace ('%'.$shortname.'%', $viewvalue, $renderrow);
            $icol++;                
        }
        $renderrow .= $this->linesep[1];
        return $renderrow;
    }    
}

// Table utilities
class F_TableUtil 
{

    // return an id for a new record, if table key is a single numeric id
    public static function getNewId($tablename, $numid='', $maxvalue='') 
    {
        $tableobj = Ef_SqlTable::findByName($tablename);
        return $tableobj->getNewId($numid, $maxvalue);
    }
	
	// replicate sql record changing data
	// $idkeys is a table of key=>value identifying the record to duplicate
	// $changedfields is a table of name=>value identifying fields to change
    // Ef_TableUtil::recordCopy('efitem', array('id'=>'28'), array('title'=>'test SA') );
	public static function recordCopy($tablename, $idkeys, $changedfields, $insertorder='insert') 
    {
        $tableobj = Ef_SqlTable::findByName($tablename);
        if (!isset($tableobj)) {
            throw new Exception ("update table unknown : $tablename ");
            return;
        }        

        $dbid = $tableobj->getDbid();
		// generate condition
		$sqlwhere = 'where ';
		foreach ($idkeys as $keyname => $keyvalue) {
			$sqlwhere .= " $keyname = '$keyvalue' and \n";
		}
		$sqlwhere = substr ($sqlwhere, 0, strlen($sqlwhere) -5);
        $sqlquery = "select * from ".$tablename.' '.$sqlwhere;
        
        // Ef_Log::log($sqlquery, 'sqlquery in recordCopy');
        
		$queryobj =  new Ef_SqlReq($sqlquery, $dbid);

		$resultrow = $queryobj->getRow();

		// generate insert order or replace order
		$sqlinsert = $insertorder . " into $tablename (\n ";
		// field names
		foreach ($resultrow as $fieldname => $fieldvalue) {
            // add field in insert if its value is empty, or if its value is changed
            if ($fieldvalue != '' || isset($changedfields[$fieldname])) {
                $sqlinsert .= " $fieldname ,\n";
            }
		}
		$sqlinsert = substr ($sqlinsert, 0, strlen($sqlinsert) -2);
		$sqlinsert .= ")\n";
		$sqlinsert .= " values (\n";
		// fieldvalues
        $parms['dbtype'] = Ef_Config::get('f_db_dbtype');
        $fieldpref = $tableobj->getAlias();
        		
        foreach ($resultrow as $fieldname => $fieldvalue) {
            if ($fieldvalue != '' || isset($changedfields[$fieldname])) {
    			if (isset($changedfields[$fieldname])) {
                    $fieldvalue = $changedfields[$fieldname]; 
                }
                $completefieldname = $fieldpref.'.'.$fieldname;
                // Ef_Log::log($completefieldname,'completefieldname in Ef_TableUtil::recordCopy');
                $field = Ef_Field::findByName($completefieldname);
                if (!$field) {
                    throw new Exception ("Ef_TableUtil::recordCopy $field not found, maybe schema error");
                }
                $sqlvalue = $field->memToSql($fieldvalue,$parms);
				$sqlinsert .= $sqlvalue .",\n";
            }
		}
		$sqlinsert = substr($sqlinsert, 0, strlen($sqlinsert) -2);
		$sqlinsert .= ")\n";

		// Ef_Log::log ($sqlinsert, 'sqlinsert in recordCopy');
        if (isset($sqlvalue)) { // avoid empty insert
            $sqlinsertobj= new Ef_SqlReq($sqlinsert, $dbid);
            $sqlinsertobj->execute();
        }
	}

	// replicate sql record changing data
	// $idkeys1 is a table of key=>value identifying the first record to compare
	// $idkeys2 is a table of key=>value identifying the first record to compare
    // $omitfields is a an array of fieldnames to omit from comparison
    // Ef_TableUtil::recordCompare('efitem', array('id'=>'28'), array('id'=>'33'), array(0=>'id',1=>'title'));
	public static function recordCompare($tablename, $idkeys1, $idkeys2, $omitfields) 
    {
        $tableobj = Ef_SqlTable::findByName($tablename);
        if (!isset($tableobj)) {
            throw new Exception ("compare table unknown : $tablename ");
            return;
        }        
        $dbid = $tableobj->getDbid();
        $tablealias = $tableobj->getAlias();

        // build restricted field list (list of fields to compare)        
        $fieldarray = $tableobj->getFieldArray();
        $restrictfieldarray = array();
        foreach ($fieldarray as $field) {
            $fieldpresent = true;
            foreach ($idkeys1 as $keyname => $keyvalue) {
                if (strpos ($field, $keyname) !== false) {
                    $fieldpresent = false;
                }            
            }
            foreach ($omitfields as $omitfield) {
                if (strpos($field, $omitfield) !== false) {
                    $fieldpresent = false;
                }
            }
            if ($fieldpresent) {
                $restrictfieldarray[] = $field;
            }
        }        
        $restrictfieldlist = Ef_SqlTable::fieldArrayToFieldList($restrictfieldarray);

		// generate query for key 1
		$sqlwhere1 = 'where ';
		foreach ($idkeys1 as $keyname => $keyvalue) {
			$sqlwhere1 .= " $keyname = '$keyvalue' and \n";
		}
		$sqlwhere1 = substr ($sqlwhere1, 0, strlen($sqlwhere1) -5);
        
        $sqlquery1 = 'select '.$restrictfieldlist;
        $sqlquery1 .= ' from '.$tablename.' '.$tablealias.' '.$sqlwhere1;           
        
        // Ef_Log::log($sqlquery1, 'sqlquery1 in recordCompare');
        
		$queryobj1 =  new Ef_SqlReq($sqlquery1, $dbid);
		$resultrow1 = $queryobj1->getRow();
        // Ef_Log::log($resultrow1, 'resultrow1 in recordCompare');
        
		// generate query for key 2
		$sqlwhere2 = 'where ';
		foreach ($idkeys2 as $keyname => $keyvalue) {
			$sqlwhere2 .= " $keyname = '$keyvalue' and \n";
		}
		$sqlwhere2 = substr ($sqlwhere2, 0, strlen($sqlwhere2) -5);
        
        $sqlquery2 = 'select '.$restrictfieldlist;
        $sqlquery2 .= ' from '.$tablename.' '.$tablealias.' '.$sqlwhere2;           
        
        // Ef_Log::log($sqlquery2, 'sqlquery2 in recordCompare');
        
		$queryobj2 =  new Ef_SqlReq($sqlquery2, $dbid);
		$resultrow2 = $queryobj2->getRow();
        // Ef_Log::log($resultrow2, 'resultrow2 in recordCompare');

        foreach ($resultrow1 as $key1=>$value1) {
            if (!isset($resultrow1[$key1]) && !isset($resultrow2[$key1])) {
                continue;
            }
            if (!isset($resultrow2[$key1]) || $resultrow2[$key1] != $resultrow1[$key1] ) {
                // Ef_Log::log($key1, "1 value : -".$resultrow2[$key1]."- differs from -$resultrow1[$key1]-");
                return false;
            } 
        }        
        foreach ($resultrow2 as $key2=>$value2) {
            if (!isset($resultrow1[$key1]) && !isset($resultrow2[$key1])) {
                continue;
            }
            if (!isset($resultrow1[$key2]) || $resultrow1[$key2] != $value2) {
                // Ef_Log::log($key2, "2 value -".$resultrow1[$key2]."- differs from -$value2-");
                return false;
            } 
        }        
        return true;        
	}
	
    // replicate a group of sql records, belonging to a table
    // getting new numeric ids for records and changing given fields
    // $condition is the sql selection 
    // $numid is the name of a numeric unique  key
	// $changedfields is a table of name=>value identifying fields to change
    // Example
    // $condition = "where arf_refart = '$art_refart'";
    // $ligchangedfields = array ("arf_refart"=>"$newrefart");
    // Ef_TableUtil::groupCopy('artfourn', $condition, "lineid", $ligchangedfields);
    public static function groupCopy($tablename, $condition, $numid, $changedfields) 
    {

        $dbid = $tableobj->getDbid();
        // getting the list of ids
        $sqlquery = ("select $numid from $tablename $condition");
        $sqlqueryobj = new Ef_SqlReq($sqlquery, $dbid);
        $resultrows = $sqlqueryobj->getRows($sqlquery);
        // Ef_Log::log ($sqlquery, 'sqlquery in Ef_TableUtil::GroupCopy');
        // Ef_Log::log ($resultrows, 'resultrows in Ef_TableUtil::GroupCopy');
        $newnumvalue = Ef_TableUtil::getNewId($tablename, $numid);

        foreach ($resultrows as $resultrow) {

            $numvalue = $resultrow[$numid];
            // Ef_Log::log ($numvalue, 'numvalue in Ef_TableUtil::GroupCopy');
            $idkeys = array ($numid=>$numvalue);

            $newchangedfields = $changedfields;
            $newchangedfields[$numid] = $newnumvalue;

            Ef_TableUtil::recordCopy($tablename, $idkeys, $newchangedfields);

            $newnumvalue++;
        }

    }    
    
    //  renumber a numeric column (not its numeric id) in a table, according a given order 
    public static function reNumber($tablename, $where, $order, $numcol, $idcol, $step=1000) 
    {                                       
        $liststr = " select $idcol, $numcol from $tablename \n $where \n $order \n";        
        $listreq = new Ef_SqlReq($liststr);
        
        $resultrows = $listreq->getRows();
        $num = $step;
        foreach ($resultrows as $resultrow) {
            $id = $resultrow[$idcol];            
            $updstr = "update $tablename set $numcol = '$num' where $idcol = '$id' ";
            $updreq = new Ef_SqlReq($updstr);
            $updreq->execute();
            $num += $step;
        }      
    }
    
    //  get the 'next' numeric value to insert after a numeric column (not its numeric id)
    //  of a given row in a table      
    public static function getNumToInsertAfter($tablename, $where, $order, $numcol, $idcol, $id, $step=1000) 
    {
        $liststr = " select $idcol, $numcol from $tablename \n $where \n $order \n";        
        $listreq = new Ef_SqlReq($liststr);
        
        $resultrows = $listreq->getRows();
        $nextnum = 0;
        $num = 0;
        // identify num = numcol of the searched id and nextnum : the next
        foreach ($resultrows as $resultrow) {
            if ($nextnum == 0 && $num != 0) {
                $nextnum = $resultrow[$numcol];
                break;                
            }            
            if ($resultrow[$idcol] == $id) {
                $num = $resultrow[$numcol];
            }
        }
        if ($nextnum != 0) {
            $newnum = floor(($nextnum+$num)/2);
        } else {
            $newnum = $num+$step;
        }
        /*
        $updstr = "update $tablename set $numcol = '$newnum' where $idcol = '$id' ";
        $updreq = new Ef_SqlReq($updstr); 
        $updreq->execute();
        */                                             
        if ($newnum == $num || $newnum == $nextnum) {
           Ef_TableUtil::reNumber($tablename, $where, $order, $numcol, $idcol, $step);
           return Ef_TableUtil::getNumToInsertAfter($tablename, $where, $order, $numcol, $idcol, $step);
        } else {
            return $newnum;
        }
        
    }
    
    // in a table ordered by a given numeric column, push a record one row to the up
    public static function pushUp($tablename, $where, $order, $numcol, $idcol, $id) 
    {
        $liststr = " select $idcol, $numcol from $tablename \n $where \n $order \n";        
        $listreq = new Ef_SqlReq($liststr);
        
        $resultrows = $listreq->getRows();
        $prevnum = 0;
        $num = 0;
        // identify num = numcol of the searched id and prevnum : the previous
        foreach ($resultrows as $resultrow) {
            // Ef_Log::log($resultrow, 'resultrow in pushUp');
            if ($resultrow[$idcol] == $id) {
                $num = $resultrow[$numcol];
                break;
            }
            $previd = $resultrow[$idcol];
            $prevnum = $resultrow[$numcol];                
        }
        // Ef_Log::log($prevnum,'prevnum in pushUp');
        // Ef_Log::log($num,'num in pushUp');
        
        if ($prevnum == 0) {
            return;
        }
        $updstr = "update $tablename set $numcol = '$num' where $idcol = '$previd' ";
        // Ef_Log::log($updstr,'updstr 1 in F_FieldExtended / pushUp');
        $updreq = new Ef_SqlReq($updstr);  $updreq->execute();
        $updstr = "update $tablename set $numcol = '$prevnum' where $idcol = '$id' ";
        // Ef_Log::log($updstr,'updstr 2 in F_FieldExtended / pushUp');
        $updreq = new Ef_SqlReq($updstr);  $updreq->execute();
        
    }

    // in a table ordered by a given numeric column, push a record one row to the down
    public static function pushDown($tablename, $where, $order, $numcol, $idcol, $id) 
    {
        $liststr = " select $idcol, $numcol from $tablename \n $where \n $order \n";        
        // Ef_Log::log($liststr, 'listStr in pushDown');
        $listreq = new Ef_SqlReq($liststr);
        
        $resultrows = $listreq->getRows();
        $nextnum = 0;
        $num = 0;
        // identify num = numcol of the searched id and nextnum : the next
        foreach ($resultrows as $resultrow) {
            // Ef_Log::log($resultrow, 'resultrow in pushDown');
            if ($num != 0) {
                $nextid = $resultrow[$idcol];
                $nextnum = $resultrow[$numcol];                
                break;
            }
            if ($resultrow[$idcol] == $id) {
                $num = $resultrow[$numcol];
            }
        }
        // Ef_Log::log($nextnum,'nextnum in pushDown');
        // Ef_Log::log($num,'num in pushDown');        
        if ($nextnum == 0) {
            return;
        }
        $updstr = "update $tablename set $numcol = '$num' where $idcol = '$nextid' ";
        // Ef_Log::log($updstr,'updstr 1 in F_FieldExtended / pushUp');
        $updreq = new Ef_SqlReq($updstr);  $updreq->execute();
        $updstr = "update $tablename set $numcol = '$nextnum' where $idcol = '$id' ";
        // Ef_Log::log($updstr,'updstr 2 in F_FieldExtended / pushUp');
        $updreq = new Ef_SqlReq($updstr);  $updreq->execute();
        
    }    

    // Gets alias from table name 
    public static function getAliasFromTableName($table)
    {
        $tableid = Ef_SqlTable::findByName($table);
        return $tableid->getAlias();
    }
    
    // Gets id field from table name, if table structure is made as usual 
    public static function getIdFieldFromTableName($table)
    {
        $tableid = Ef_SqlTable::findByName($table);
        $fieldKeyArray = $tableid->getFieldKeyArray();
        // Ef_Log::log($fieldKeyArray, 'fieldKeyArray');
        if (count($fieldKeyArray) ==1) {
            $fieldname = $fieldKeyArray[0];
            $fieldobj = Ef_Field::findByName($fieldname);
            return $fieldobj->getShortName();             
        }
        
    }
    
}

// A specific field : row button with a glyph icon
class F_FieldRowIconButton extends F_FieldRowButton {

    protected $glyphicon;
    
	public function __construct($argname, $argattrib) 
    {
		parent::__construct($argname,$argattrib);
		
		if (array_key_exists('glyphicon',$argattrib)) 
        {
		    $this->glyphicon = $argattrib['glyphicon'];
		}
		return $this;
	}	
    
    public function memToEditHtml ($value, $parms=array()) 
    {
        $rowid = $this->getRowid($parms);

        $fieldname = $this->buttonprefix.$rowid;

        $buttontext = str_replace ( '%row%', $rowid, $this->buttontext );
        
        $glyphicon = $this->glyphicon;
        
        // set readonly if $parms['readonly'] is set
        $disabled = isset($parms['disabled']) ? 'disabled' : ''; 
        $readonly = isset($parms['readonly']) ? 'readonly' : '';
        
        $inputfield= "         
            <label for=\"$fieldname\" class=\"btn btn-default\">
                <span class=\"glyphicon $glyphicon\" 
                    style=\"font-size: 2em;\" aria-hidden=\"true\"></span>$buttontext
            </label>
            <input $readonly $disabled class=\"hidden\"  type=\"submit\" 
                    id=\"$fieldname\" name=\"$fieldname\">            
            </input>";        
        	
		return $inputfield;
    }

	public function memToViewHtml ($value, $parms=array()) 
    {
        $parms['disabled'] = true;
        return $this->memToEditHtml ($value, $parms);
    }

}


// ?>