<?php
// Copyright Vincent Wartelle & Oklin.com 2016-2019
// EntryField framework - Open sourced under MIT license  

// The list view is the component used to render a F_List 
class F_ListView 
{
    
    // header and footer will precede and succede the rendering
    // linesep and colsep are line separators and column separators in the rendering ;
    // they are defined as an array of two elements (the begin separator and the end separator)
    // headcolsep are the separators for the head column
    // fieldeditsep are the separators for an edited field
    // fielderrorsep are the separators for a field in error    
    // firstlinesep are the separators around the first line
    // headrowcolsep are the separators for the head column if it exists inside the rows    

	protected $variant;
	protected $linesep = array();
    protected $firstlinesep = array(); // 001690 - Allow first line separator
	protected $colsep = array();
	protected $headcolsep = array();
    protected $headrowcolsep = array(); // 001710 - Allow different col separator inside row
	protected $header = '';
	protected $footer = '';
	protected $fieldeditsep = array();
	protected $fielderrorsep = array();
    protected $renderfunc;
    protected $styleinfo = array();
    
    protected $fielddisplayname = array();
    protected $fieldheadcolsep = array();
    protected $fieldheadrowcolsep = array(); // 001710 - Allow different col separator inside row 
    protected $fieldcolsep = array();

        
    // 001690 - Allow first line separator        
	public function setFirstLineSep($argbegin, $argend) 
    {
		$this->firstlinesep = array ($argbegin, $argend);	
	}
	public function setLineSep($argbegin, $argend) 
    {
		$this->linesep = array ($argbegin, $argend);	
	}
	public function setColSep($argbegin, $argend) 
    {
		$this->colsep = array ($argbegin, $argend);	
	}
	public function setHeadColSep($argbegin, $argend) 
    {
		$this->headcolsep = array ($argbegin, $argend);	
	}
    // 001710 - Allow different col separator inside row
    public function setHeadRowColSep($argbegin, $argend)
    {
        $this->headrowcolsep = array($argbegin, $argend);
    }     
	public function setHeader($argheader) 
    {
	    $this->header = $argheader;
	}
	public function setFooter($argfooter) 
    {
	    $this->footer = $argfooter;
	}
	public function setFieldEditSep($argbegin, $argend) 
    {
	 	$this->fieldeditsep = array ($argbegin, $argend);
	}
	public function setFieldErrorSep($argbegin, $argend) 
    {
	 	$this->fielderrorsep = array ($argbegin, $argend);
	}
    public function setStateStyleInfo($state,$styleinfo)
    {
        $this->styleinfo[$state] = $styleinfo;
    }
    public function  getStateStyleInfo($state) 
    {
        if (isset($this->styleinfo[$state])) {
            return $this->styleinfo[$state];
        } else {
            return '';
        } 
    }
    
    // Define (specialize) the render function
    public function setRenderFunc($renderfunc) 
    {
        $this->renderfunc = $renderfunc;
    }
    
    // Define various rendering styles
	public function setVariantHtml($argvariant = 'simplehtmltable', $parms=array()) 
    {
		$this->variant = $argvariant;
		// Ef_Log::htmlEcho ($argvariant, '_variant');
		switch ($argvariant) {
		    case 'simplehtmltable':
                $this->setHeader ("<table border='1'>\n\n");
                $this->setLineSep ("<tr>\n", "</tr>\n\n");
                $this->setColSep ("<td>", "</td>\n");
                $this->setHeadColSep ("<th>", "</th>\n");
                $this->setFooter ("</table>\n\n\n");			
				// $this->setFieldEditSep ('<div class="alert alert-success">','</div>');				
				$this->setFieldEditSep ('<div>','</div>');
				$this->setFieldErrorSep ('<div> error','</div>');		
		        break;

		    case 'simplehtmllist':
				$this->setHeader ("<span>\n");
                $this->setLineSep ("&nbsp;", "<br>\n\n");
                $this->setColSep (" ", "\n");
                $this->setHeadColSep (" ", "\n");
				$this->setFooter ("</span>\n\n");	
		        break;

		    case 'simplehtmlform':
                // rowtitle is mandatory !! todo 
				$this->setHeader ("\n\n");
                $this->setLineSep ("<table border style=\"border-collapse: collapse; border: 1px solid black;\">\n", "</table><br>\n\n ");
                $this->setColSep ('<td style="border: 1px solid gray;"> ', "</td></tr>\n");
                $this->setHeadColSep ('<tr><th style="border: 1px solid gray;">', "</th>\n");
				$this->setFooter ('');
				// $this->setFieldEditSep ('<div class="alert alert-success">','</div>');
				$this->setFieldEditSep ('<div>','</div>');								
				$this->setFieldErrorSep ('<div> error','</div>');		
                /// $this->setFooter ("</pre>");			
		        break;
		        
		    default:
		        throw new Exception ("F_ListView cannot set variant,  variant : $argvariant does not exist");
		        break;
		}		
	}	
    
	protected function buildFieldValues($fieldarray, $resultrow) 
    {	
		$fieldvalues = array();
		
		foreach ($fieldarray as $ifield => $fieldname) {
			$varfieldname = Ef_Field::getVarnameFromName($fieldname);
			$fieldvalues[$varfieldname] = $resultrow[$ifield];
		} 		
		return $fieldvalues;	
	}
    
    protected function getFieldEditSep($f_list,$fieldname,$irow,$i) 
    {
        // Ef_Log::log($fieldname, "irow $iwo, i $i, fieldname in getFieldEditSep"); 
        if ($f_list->isFieldInError($fieldname,$irow)) {
            return $this->fielderrorsep[$i];
        } else {
            return $this->fieldeditsep[$i];        
        }
    }

    public function setFieldDisplaySep($fieldname, $displayname, $hcolsep0, $hcolsep1, $colsep0, $colsep1) 
    {
        // Ef_Log::log($fieldname, 'fieldname in setFieldDisplaySep');
        if ($displayname != '-') {
            $this->fielddisplayname[$fieldname] = $displayname;
        }
        if ($hcolsep0 != '-') {
            $this->fieldheadcolsep[$fieldname][0] = $hcolsep0;
        }
        if ($hcolsep1 != '-') {
            $this->fieldheadcolsep[$fieldname][1] = $hcolsep1;
        }
        if ($colsep0 != '-') {
            $this->fieldcolsep[$fieldname][0] = $colsep0;
        }
        if ($colsep1 != '-') {        
            $this->fieldcolsep[$fieldname][1] = $colsep1;
        }
    }

    protected function setFieldsNamesAndSeps($fieldarray) 
    {
        foreach ($fieldarray as $fieldname) {
            if (!isset($this->fielddisplayname[$fieldname])) {
                // 2016-12-14
                // $this->fielddisplayname[$fieldname] = Ef_Lang::get($fieldname);
                $field = Ef_Field::findByName($fieldname);
                $this->fielddisplayname[$fieldname] = $field->getDisplayName();
            }
            if (!isset($this->fieldheadcolsep[$fieldname][0])) {
                $this->fieldheadcolsep[$fieldname][0] = $this->headcolsep[0];
            }
            if (!isset($this->fieldheadcolsep[$fieldname][1])) {
                $this->fieldheadcolsep[$fieldname][1] = $this->headcolsep[1];
            }
            // 001710 - Allow different col separator inside row
            //          Default value is head col separator 
            if (count($this->headrowcolsep) == 0) {
                $this->headrowcolsep = $this->headcolsep;
            }
            if (!isset($this->fieldheadrowcolsep[$fieldname][0])) {
                $this->fieldheadrowcolsep[$fieldname][0] = $this->headrowcolsep[0];
            }            
            if (!isset($this->fieldheadrowcolsep[$fieldname][1])) {
                $this->fieldheadrowcolsep[$fieldname][1] = $this->headrowcolsep[1];
            }
            // 001710 - End
            if (!isset($this->fieldcolsep[$fieldname][0])) {
                $this->fieldcolsep[$fieldname][0] = $this->colsep[0];
            }
            if (!isset($this->fieldcolsep[$fieldname][1])) {
                $this->fieldcolsep[$fieldname][1] = $this->colsep[1];
            }
        }    
    } 

    // Render a field value according to its state
    // States are : edit, disabled, readonly, hidden, view, none (show nothing)
    public function renderField ($f_list, $field, $fieldvalue='', $irow=0, $fieldvalues = array()) 
    {
        if (!is_object ($f_list)) {
            throw new Exception ("Not a valid list in F_ListView::renderField");
        }
    	$fieldstatearray = $f_list->getFieldStateArray();    			 
    
        if (!is_object ($field)) {
            throw new Exception ("Not a valid field in F_ListView::renderField");
        }
        $fieldname = $field->getName();
        $renderresult = '';
        
        switch ($fieldstatearray[$fieldname]) {
            case 'edit': 
                // $renderresult .= $this->getFieldEditSep($f_list,$fieldname,$irow,0);
                $renderresult .= $field->memToEditHtml ($fieldvalue, 
						array('irow'=>$irow,'fieldvalues'=>$fieldvalues));                            
                // $renderresult .= $this->getFieldEditSep($f_list,$fieldname,$irow,1);
                break;
            case 'disabled':
                $renderresult .= $field->memToDisabledHtml ($fieldvalue, 
						array('irow'=>$irow,'fieldvalues'=>$fieldvalues));
                break;
            case 'readonly':
                $renderresult .= $field->memToReadonlyHtml ($fieldvalue, 
						array('irow'=>$irow,'fieldvalues'=>$fieldvalues));
                break;
            case 'hidden':
                $renderresult .= $field->memToHiddenHtml ($fieldvalue, 
						array('irow'=>$irow,'fieldvalues'=>$fieldvalues));
                break;
            case 'none':
                $renderresult .= $field->memToNoneHtml ($fieldvalue, 
						array('irow'=>$irow,'fieldvalues'=>$fieldvalues));
				break;
            case 'link':
                $renderresult .= $field->memToLinkHtml ($fieldvalue, 
						array('irow'=>$irow,'fieldvalues'=>$fieldvalues));
                break;                                                                   
            case 'function':
                $renderresult .= $field->memToFunctionHtml ($fieldvalue, 
						array('irow'=>$irow,'fieldvalues'=>$fieldvalues));
                break;                                                                                                         
			case 'view':
            default:
                $renderresult .= $field->memToViewHtml ($fieldvalue,
						array('irow'=>$irow,'fieldvalues'=>$fieldvalues));
                break;
        }
        return $renderresult;
    }
    
                    
    public function render($f_list, $parms=array()) 
    {

        // Ef_Log::log($f_list, 'DEBUG $f_list in '.__FUNCTION__);

        if (array_key_exists ('variant', $parms)) 
            $this->setVariantHtml($parms['variant'], $parms);
        else
            $this->setVariantHtml('', $parms);
        
		// Delegate render to another function        
        if (isset($this->renderfunc)) {
            $methodvariable = array ($this, $this->renderfunc);
            if ( ! is_callable ($methodvariable)) {
		        throw new Exception ("F_ListView renderfunc is not callable : $this->renderfunc ");
            }
            $renderresult =  (call_user_func_array ($methodvariable, array(&$f_list, &$parms)));
            // Ef_Log::log ($renderresult,'renderresult');
            return $renderresult; 
        } 

        if (array_key_exists('displaysep', $parms)) {
            $displayseparray = $parms['displaysep'];
            foreach ($displayseparray as $fieldname=>$dsarray) {
                $this->setFieldDisplaySep($fieldname, $dsarray[0], $dsarray[1], $dsarray[2], $dsarray[3], $dsarray[4]);
            }
        }

    	// $listedarray, $changedarray, $fieldarray, $fieldstatearray, $parms=array()
    	$listedarray = $f_list->getListedArray();
    	$changedarray = $f_list->getChangedArray();
    	// $fieldarray = $f_list->getFieldArray();
    	$fieldarray = $f_list->extractCompletedFieldArray();
		// Ef_Log::log ($fieldarray, 'extracted fieldarray ');
		
        $this->setFieldsNamesAndSeps($fieldarray); 
        
    	$fieldstatearray = $f_list->getFieldStateArray();    			 

        $renderresult = $this->header;
        $irow = 0;
    
        // 001690 - Allow first line separator - default value to line separator
        if (count($this->firstlinesep) == 0) {
            $this->firstlinesep = $this->linesep;
        }        
        
    
    	while (true) {
    		$resultrow = $f_list->extractCompletedRow($irow);	
            
            // Ef_Log::log($resultrow, "DEBUG resultrow for $irow in ".__FUNCTION__);

    		if ($resultrow === false )
     			break;
            
        	// Change state array according to a given function 
           	// we save fieldstatearray and we restore when we have finished to process this row
            $changestatefunction = $f_list->getChangeStateFunction();
            if ($changestatefunction) {
                $fieldstatearray = $f_list->getFieldStateArray(); // init to avoid taking from previous row
                // 001810 - 2020-06-04 - Begin                         
                // $fieldstatearray = call_user_func_array ($changestatefunction, array (&$f_list, $resultrow, $fieldstatearray));
                $fieldstatearray = call_user_func_array ($changestatefunction, array (&$f_list, &$resultrow, $fieldstatearray));
                // 001810 - 2020-06-04 - End                         
            }
            
    		$fieldvalues = $this->buildFieldValues ($fieldarray, $resultrow);

            //  001690 - $renderresult .= $this->linesep[0];                                                                 
    		
            // Title row (in general, if display is a list)
            if (array_key_exists('rowtitle',$parms) && $parms['rowtitle'] == '1' && $irow == 0) {

                // 001690 - Allow first line separator        
                // $renderresult .= $this->linesep[0]; 
                // $renderresult .= "<!-- begin of first line TEST -->\n";                                                                        
                $renderresult .= $this->firstlinesep[0];     

                $icol = 0;
                foreach ($resultrow as $fieldvalue) {

                    $fieldname = $fieldarray[$icol];    

					if ($fieldstatearray[$fieldname] != 'none') {
                        $field = Ef_Field::findByName ($fieldname);
                        $headcol0 = $this->fieldheadcolsep[$fieldname][0];
                        // apply style relative to state : example style="visibility:hidden" for state hidden
                        $headcol0 = str_replace('>',' '.$this->getStateStyleInfo($fieldstatearray[$fieldname]).'>', $headcol0);                         
                        $renderresult .= $headcol0;
                        $renderresult .= $this->fielddisplayname[$fieldname];  
                        $renderresult .= $this->fieldheadcolsep[$fieldname][1];
					}
                    $icol++;
                }
                // 001690 - Allow first line separator        
                // $renderresult .= $this->linesep[1];        
                // $renderresult .= "<!-- end of first line TEST -->\n";                                                     
                $renderresult .= $this->firstlinesep[1];                          
                $renderresult .= $this->linesep[0];                                                     
            } 
            // 001720 - must have a line separator if no row title, you know - begin            
            else {
                $renderresult .= $this->linesep[0];                                                                 
            } 
            // 001720 - end            
    	
            $icol = 0;
            foreach ($resultrow as $fieldvalue) {

                $fieldname = $fieldarray[$icol];    
                        	
                // Title Columns (in general, if display is a form)  
                if (array_key_exists('coltitle',$parms) && $parms['coltitle'] == '1') {
    				if ($fieldstatearray[$fieldname] != 'none') {
	                    $field = Ef_Field::findByName ($fieldname);
                        // 001710 - Allow different col separator inside row - begin 
                        // $headcol0 = $this->fieldheadcolsep[$fieldname][0];
                        $headcol0 = $this->fieldheadrowcolsep[$fieldname][0];
                        // 001710 - end 
                        // apply style relative to state : example style="visibility:hidden" for state hidden                        
                        $headcol0 = str_replace('>',' '.$this->getStateStyleInfo($fieldstatearray[$fieldname]).'>', $headcol0);                         
                        $renderresult .= $headcol0;
                        $renderresult .= $this->fielddisplayname[$fieldname];
                        // 001710 - Allow different col separator inside row - begin 
                        // $renderresult .= $this->fieldheadcolsep[$fieldname][1];
                        $renderresult .= $this->fieldheadrowcolsep[$fieldname][1];
                        // 001710 - end 
    				}
                }                

                // access to field definition
                $field = Ef_Field::findByName ($fieldname);
                if ($fieldstatearray[$fieldname] != 'none') {
                    $head0 = $this->fieldcolsep[$fieldname][0];
                    // apply style relative to state : example style="visibility:hidden" for state hidden
                    $head0 = str_replace('>',' '.$this->getStateStyleInfo($fieldstatearray[$fieldname]).'>', $head0);                         
                    $renderresult .= $head0;
				}

                
				$changedvalue = Ef_List::extractChangedValue($fieldname, $changedarray, $irow); 
				if ($changedvalue !== false) {
					// Ef_Log::htmlEcho($changedvalue,"field $fieldname is changed in $irow to ");
					// Ef_Log::log ($changedvalue, "field $fieldname is changed in $irow ");
					$fieldvalue = $changedvalue; // TODO alert in red
				} 
                
                // 2019-11-20 if transient value is set, use it
                //            this is to redisplay the field value in error
                $ivarname = Ef_Field::getIvarnameFromName($fieldname);
                if (Ef_Session::checkKey('Transient__'.$ivarname.'__'.$irow)) {
                    $fieldvalue = Ef_Session::getVal('Transient__'.$ivarname.'__'.$irow); 
                }
                
                if (is_object ($field)) {
                    switch ($fieldstatearray[$fieldname]) {
                        case 'edit': 
                            $renderresult .= $this->getFieldEditSep($f_list,$fieldname,$irow,0);
                            $renderresult .= $field->memToEditHtml ($fieldvalue, 
									array('irow'=>$irow,'fieldvalues'=>$fieldvalues));                            
                            $renderresult .= $this->getFieldEditSep($f_list,$fieldname,$irow,1);
                            break;
                        case 'disabled':
                            $renderresult .= $field->memToDisabledHtml ($fieldvalue, 
									array('irow'=>$irow,'fieldvalues'=>$fieldvalues));
                            break;
                        case 'readonly':
                            $renderresult .= $field->memToReadonlyHtml ($fieldvalue, 
									array('irow'=>$irow,'fieldvalues'=>$fieldvalues));
                            break;
                        case 'hidden':
                            $renderresult .= $field->memToHiddenHtml ($fieldvalue, 
									array('irow'=>$irow,'fieldvalues'=>$fieldvalues));
                            break;
                        case 'none':
                            $renderresult .= $field->memToNoneHtml ($fieldvalue, 
									array('irow'=>$irow,'fieldvalues'=>$fieldvalues));
							break;
                        case 'link':
                            $renderresult .= $field->memToLinkHtml ($fieldvalue, 
									array('irow'=>$irow,'fieldvalues'=>$fieldvalues));
                            break;                                                                   
                        case 'function':
                            $renderresult .= $field->memToFunctionHtml ($fieldvalue, 
									array('irow'=>$irow,'fieldvalues'=>$fieldvalues));
                            break;                                                                   
						case 'view':
                        default:
                            $renderresult .= $field->memToViewHtml ($fieldvalue,
									array('irow'=>$irow,'fieldvalues'=>$fieldvalues));
                            break;
                    }
                } else {
                    $renderresult .= ('-'.$fieldvalue.'-');	
                }
                
				if ($fieldstatearray[$fieldname] != 'none') {
					// $renderresult .= $this->colsep[1];
                    $renderresult .= $this->fieldcolsep[$fieldname][1];
				}
                $icol++;                
            }
            $renderresult .= $this->linesep[1];   
            $irow++;
             
        }
        
        $renderresult .= $this->footer;
        return $renderresult;
    			
    }
        
}
	
// A control that may be associated to a F_List    
class F_Control 
{
    protected $msgerr;
    protected $gravity;
    protected $fieldnames = array();

	public function getMsgErr() 
    {
		return $this->msgerr;
	} 

	public function getGravityErr() 
    {
		return $this->gravity;
	} 

	public function getFieldNames() 
    {
		return $this->fieldnames;
	} 
	
	public function reset() 
    {
		$this->msgerr = '';
		$this->gravity = '';
        $this->fieldnames = array();
	}	
	
}

// F_Page is a template engine
// it defines template variables like %thisisavariable% 
// it may define template blocks like 
// <!-- %bname:begin% --> 
// <!-- %bname:end% -->
class F_Page 
{
	protected $templatetext;
	protected $truetext;
    protected $blockmark = array();
    protected $blockcontent = array();
		
	public function render($clear=true) 
    {
		echo $this->truetext;		
		if ($clear==true) {
			$this->clearContent();
		}
	}

    public function addText($text) 
    {
        $this->templatetext .= $text;
        $this->truetext .= $text;
    }
    		
    public static function getTemplateContent($filepath) 
    {
        $funcname = Ef_Config::get('readtemplatefunc');
        if (function_exists($funcname)) {
            $content = call_user_func_array($funcname, array($filepath));
        } else {
            $content = file_get_contents($filepath);
        }
        return $content;
    }
     
	public function addTemplate($filepath) 
    {
		$F_Config = Ef_Config::getVars();		
		extract ($F_Config);
		if ($f_template_path) {
			$filepath = $f_template_path .'/'. $filepath;
		}	
        $readtext = Ef_Page::getTemplateContent($filepath); 
        $this->templatetext .= $readtext;
        $this->truetext .= $readtext;
	}
    
    // 2018-07-04
    public function addTemplateText($text)
    {
        $this->templatetext .= $text;
        $this->truetext .= $text;    
    }
    
    
    
	// Find a variable name or something else in the template
    public function findVar($varname, $offset=0) 
    {
        return strpos($this->truetext, $varname, $offset);    
    }
    
    
	// Replace variable name by value
	public function replaceVar($varname, $value) 
    {
        
		$this->truetext = str_replace( $varname, $value, $this->truetext );
        // Ef_Log::log($varname,"replaceVar was called to replace $varname");
        // Ef_Log::log($this->truetext, 'truetext in replaceVar');			
	}
    
    // Replace all remaining variables by blank - 2018-08-13
    public function clearPercentVars()
    {
        $pattern = '/\%[a-zA-Z0-9-_\'\s]+\%/';

        $replacedtext = preg_replace($pattern, '', $this->truetext);
        
        $this->truetext = $replacedtext;        
    }
	
	// Replace variable name by value, and append variable name at end of content
    // this is used when we want to iterate the variable several times 
	public function replaceVarNext($varname, $value, $append="\n" ) 
    {
		$value .= $append;
		$value .= $varname;
		$value .= $append;
		$this->replaceVar($varname, $value);
	
	}
	
	public function clearContent() 
    {
		$this->templatetext = '';
		$this->truetext = '';
	}
    
    public function getContent()
    {
        return $this->truetext;
    }
    
    // added 2018-07-03
    public function translateContent()
    {
        $resarray = array();
        $pattern = '/\%[a-zA-Z0-9-_\'\.\s\?]+\%/';     // Added ? 2020-04-24

        $nbresults = preg_match_all ($pattern, $this->truetext, $resarray);

        if ($nbresults) {
            $newtext = $this->truetext;
            foreach ($resarray[0] as $index=>$keyword) {
                // echo "$index : $keyword <br>\n";
                if (Ef_Lang::get($keyword)) {
                    $keyw = substr($keyword,1,-1);
                    $newtext = str_replace($keyword, Ef_Lang::get($keyw), $newtext);
                }                    
            }    
            $this->truetext = $newtext;    
        }        
    }
    
	
    // Define an iterative block
    // search begin and end of the block <!-- %bname:begin% --> <!-- %bname:end% -->
    // keep the content of the block (without begin/end) in $this->blockcontent[$bname]
    // in the text, transform (temporary) block content by <!-- %bname:block% -->
    public function blockDefine($blockStr)
    {
        $this->blockmark[$blockStr] = array();
        $this->blockmark[$blockStr]['begin'] = '<!--'.' %'.$blockStr.':begin% '.'-->';
        $this->blockmark[$blockStr]['end']   = '<!--'.' %'.$blockStr.':end% '.'-->';
        $this->blockmark[$blockStr]['block'] = '<!--'.' %'.$blockStr.':block% '.'-->';
        
        if (strpos($this->truetext, $this->blockmark[$blockStr]['begin']) === false) {
            return; // TODO throw the exception
            throw new Exception("Can't define block $blockStr, no block begin in text");            
        }
        // Ef_Log::log($blockStr, 'this block is found by blockDefine');
        
        if (strpos($this->truetext, $this->blockmark[$blockStr]['end']) === false) {
            throw new Exception("Can't define block $blockStr, no block end in text");            
        }
        $blockBeginPos = strpos($this->truetext, $this->blockmark[$blockStr]['begin']);
        $blockEndPos = strpos($this->truetext, $this->blockmark[$blockStr]['end']) 
                + strlen($this->blockmark[$blockStr]['end']); 
        // Ef_Log::log($blockBeginPos, 'blockBeginPos');
         
        $blockContent = substr($this->truetext, $blockBeginPos, $blockEndPos-$blockBeginPos+1);
        // Ef_Log::log($blockContent,"blockContent in blockDefine of $blockStr");
        
        $blockContent = str_replace($this->blockmark[$blockStr]['begin'], '', $blockContent);
        $blockContent = str_replace($this->blockmark[$blockStr]['end'], '', $blockContent);
        // Ef_Log::log($blockContent,"blockContent in blockDefine of $blockStr -> 2");
         
        $this->blockcontent[$blockStr] = $blockContent;
        
        // replace block by block marker 'block'
        $this->truetext = substr_replace($this->truetext, $this->blockmark[$blockStr]['block'],
            $blockBeginPos, $blockEndPos-$blockBeginPos+1 );
        // $this->truetext = substr_replace($blockContent, 

        // Ef_Log::log($this->truetext, '$this->truetext after blockDefine');            
        return;
                             
    }
    
    // Once the block is defined, instanciate it
    // check the existence of the block
    // copy the content of the block into $instanciedContent
    // replace variables of the block (typically %blockElement% becomes $element, 
    //      %blockElement:attribute% becomes $attribute
    // in the text, replaces the block <!-- %bname:block% --> 
    //      by $instanciedContent.'<!-- %bname:block% -->'
    public function blockInstanciate($blockStr, $arrayValues, $addpctsign=false, $sep="\n")
    {
        if (!isset($this->blockcontent[$blockStr])) {
            return; // TODO throw the exception
            throw new Exception("Can't instanciate block $blockStr, block not defined");                    
        }        
        // Ef_Log::log($blockStr, 'instanciate this block');
        if (!is_array($arrayValues)) {
            throw new Exception("Can't instanciate block $blockStr, no array provided");                    
        }        
        $instanciedContent = $this->blockcontent[$blockStr];
        // Ef_Log::log($instanciedContent, "instanciedContent before replacement for block $blockStr");
        foreach ($arrayValues as $arrayKey=>$value) {
            if ($addpctsign) {
                $arrayKey = '%'.$arrayKey.'%';
            }
            // Ef_Log::log($value, " will replace $arrayKey in blockInstanciate ");
            $instanciedContent = str_replace($arrayKey, $value, $instanciedContent);            
        }
        $markerBlock = $this->blockmark[$blockStr]['block'];
        $this->truetext = str_replace($markerBlock, $instanciedContent.$sep.$markerBlock, $this->truetext);
        // Ef_Log::log($this->truetext, '$this->truetext after blockInstanciate'); 
        // Ef_Log::log($instanciedContent, "instanciedContent for block $blockStr");                            
    }
    
    // Close block
    // end of use of the block : remove the block marker <!-- %bname:block% -->
    public function blockClose($blockStr)
    {
        $markerBlock = $this->blockmark[$blockStr]['block'];
        $this->truetext = str_replace($markerBlock, '', $this->truetext);        
        // Ef_Log::log($this->truetext, '$this->truetext after blockClose');                             
    }
}


// Page part is a component used inside a page
// It is built to contain the application logic : controllers
class F_PagePart 
{
    public function doRun(&$page=null)
    {
    
    
    }
    
}

// Route is the component to change long-significative-url-path to inner path
class F_Route
{
    protected static $routes = array();
    protected $filename;
    protected $urlname;
    

    public function __construct($argfile, $argurl) 
    {
        $this->filename = $argfile;
        $this->urlname = $argurl;
        self::$routes[] = $this;
    }
    
    public static function getUrlFromFile($argfile)
    {
        foreach (self::$routes as $route) {  
            if ($route->filename == $argfile)
                return $route->urlname;
        }
        return null;            
    }

    public static function getFileFromUrl($argurl)
    {
        foreach (self::$routes as $route) {  
            if ($route->urlname == $argurl)
                return $route->filename;
        }
        return null;            
    }

    // Main function : find the script associated to an URL 
    public static function getScriptPathFromUrl ($url) 
    {
        // http://localhost/dropwww/entryfield/bootik/test-route --> gets test-route 
        // $endofurl = end((explode('/', $url)));
        // Ef_Log::log($url, 'url in getScriptPathFromUrl');
        $urlarray = explode('/',$url);   
        $endofurl = end($urlarray);
        // eliminates get parameters
        if (strpos($endofurl, '?') !== false) {
            $urlarray = explode('?', $endofurl);
            $endofurl = reset($urlarray);
        }

        // Ef_Log::log($endurl, 'endurl in getScriptPathFromUrl');
         
       $script = F_Route::getFileFromUrl($endofurl);
        // Ef_Log::log($script, 'script in getScriptPathFromUrl');
        
        if ($script) {    
            return $script;
        } else {
            return $endofurl;
        }    
    }

    // Feed the GET array from an URL
    public static function setGetParamsFromUrl($url)
    {
        if (strpos($url, '?') !== false) {
            $urlarray = explode('?', $url);        
            $getparms =  end($urlarray);
            $getparmsarray = explode('&', $getparms);
            foreach ($getparmsarray as $keyvalue)
            {
                $keyvaluearr = explode('=',$keyvalue); 
                $key = reset($keyvaluearr);
                $value = end($keyvaluearr);
                // Ef_Log::log($value, "setting _GET key $key to value :");
                $_GET[$key] = $value;             
            }        
        }    
    }

    // Remove a given var from an URL
    public static function removeVariableFromUrl($url, $keytodel)
    {
        $newurl = $url;
        if (strpos($url, '?') !== false) {
            $urlarray = explode('?', $url);
            $urlstart = reset($urlarray);   // means first element       
            $getparms =  end($urlarray);    // means last element
            $getparmsarray = explode('&', $getparms);
            $newurl = $urlstart.'?';
            foreach ($getparmsarray as $keyvalue)
            {
                $keyvaluearr = explode('=',$keyvalue); 
                $key = reset($keyvaluearr);
                $value = end($keyvaluearr);
                if ($key != $keytodel) {
                    // 2019-11-03 - ticket 001620
                    // $newurl .= $getparmsarray;  
                    $newurl .= $keyvalue;
                }
            }        
        }        
        // if last char is ? remove it
        if (substr($newurl, -1) == '?') {
            $newurl = substr($newurl, 0, -1);
        } 
        return $newurl;
    }    
                    
    // Complete the URL, adding the variables from a GET array or similar
    public static function updateUrlParamsFromGet($url, $getarray)
    {
        $nextsign = '?';
        if (strpos($url, '?') !== false) {
            $nextsign = '&';
        }    
        foreach ($getarray as $key=>$value) {
            if (strpos($url, $key) !== false ) {
                $url = Ef_Route::removeVariableFromUrl($url, $key);
                $nextsign = '?';
                if (strpos($url, '?') !== false) {
                    $nextsign = '&';
                }
            }            
            $url .= $nextsign.$key.'='.$value;
            $nextsign = '&';                                
        }
        return $url;
    }
    
}

// ?>