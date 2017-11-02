<?php

class Ef_ListViewExtended extends Ef_ListView {

	public function setVariantHtml ($argvariant = 'simplehtmltable', $parms=array()) {
		$this->variant = $argvariant;
		switch ($argvariant) {
		    case 'simplehtmltable':
                $this->setHeader (
                    "<div class=\"container\">
                        <div class=\"row\">
                            <div class=\"table-responsive\" style=\"width:70%;\"> 
                            <table class=\"table table-striped table-responsive table-bordered table-hover table-condensed\"> \n\n");
                $this->setLineSep ("<tr>\n", "</tr>\n\n");
                $this->setColSep ("<td>", "</td>\n");
                $this->setHeadColSep ("<th>", "</th>\n");
                $this->setFooter ("
                            </table>
                            </div>
                        </div>
                    </div>\n\n\n");			
				$this->setFieldEditSep ('<div>','</div>');				
				$this->setFieldErrorSep ('<div class="alert alert-danger">','</div>');
                $this->setStateStyleInfo('hidden','style="display:none"');		
		        break;

		    case 'simplehtmllist':
                // $this->setHeader ("<pre>");
				$this->setHeader ("<span>\n");
                $this->setLineSep ("&nbsp;", "<br>\n\n");
                $this->setColSep (" ", "\n");
                $this->setHeadColSep (" ", "\n");
				$this->setFooter ("</span>\n\n");	
				$this->setFieldEditSep ('<span>','&nbsp;</span>');								
				$this->setFieldErrorSep ('<span class="alert alert-danger">','</span>');		
                $this->setStateStyleInfo('hidden','style="display:none"');		
                /// $this->setFooter ("</pre>");			
		        break;


		    case 'simplehtmlform':
				$this->setHeader ("\n\n");
                $this->setLineSep ("<div class=\"table-responsive\" style=\"width:70%;\"> 
                    <table class=\"table table-striped table-responsive table-bordered table-hover table-condensed\"> \n\n"
                    , "</table><br>\n</div>\n ");
                $this->setHeadColSep ('<tr><th>', "</th>\n");
                $this->setColSep ('<td> ', "</td></tr>\n");
				$this->setFooter ('');
				$this->setFieldEditSep ('<span>','&nbsp;</span>');								
				$this->setFieldErrorSep ('<span class="alert alert-danger">','</span>');		
                $this->setStateStyleInfo('hidden','style="display:none"');		
                			
		        break;
                       
            case 'bootstrapform':
                $this->setHeader("<div class=\"bs-component\"><form target=\"Tdl_FormProc\" class=\"form-horizontal\"><fieldset>");
                $this->setLineSep ("<div>", "</div>\n ");
                $this->setHeadColSep ('', '');
                $this->setColSep ('<div class="form-group">', "</div>\n");
				$this->setFooter ("</form></div>");
				$this->setFieldEditSep ('<span>','&nbsp;</span>');								
				$this->setFieldErrorSep ('<span class="alert alert-danger">','</span>');		
                $this->setStateStyleInfo('hidden','style="display:none"');
                 
                 
      // <form class="form-horizontal">	
                
                break;	
                
            case 'templatedlist':
                $this->setLineSep ("", "");
                // simple
                $this->setRenderFunc('renderThroughTemplate');
                break;                     


            case 'bootlist':   /* gloops */
                $this->setHeader ('<div class="container">'."\n\n");
                $this->setFooter ("</div>\n\n");			
                $this->setLineSep ('<div class="row">'."\n\n", "</div>\n\n");
                $this->setColSep ('<span>'."\n\n", "</span>\n\n");
                $this->setHeadColSep ('<span>'."\n\n", "</span>\n\n");
				$this->setFieldEditSep ('<span>','</span>');				
				$this->setFieldErrorSep ('<span class="alert alert-danger">','</span>');
                $this->setStateStyleInfo('hidden','style="display:none"');		
                break;                     
		        
		        
		    default:
		        throw new Exception ("news_Ef_ListView cannot set variant,  variant : $argvariant does not exist");
		        break;
		}		
	}	

    // Render through template : 
    // -    either template file name is given in $parms['templatefile'],
    // -    either template file name is given in a column identified by $parms['templatefield']
    // -    either template content is given in $parms['templatecontent']
    public function renderThroughTemplate (&$Ef_List, $parms=array()) {
    		 
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
    	$fieldarray = $Ef_List->getFieldArray();
        $listedarray = $Ef_List->getListedArray();
        $completedarray = $Ef_List->extractCompletedFieldArray();
        // Ef_Log::log($fieldarray,'fieldarray');
        // Ef_Log::log($completedarray,'completedarray');
            
    	while (true) {
    		$resultrow = $Ef_List->extractCompletedRow($irow);	

    		if ($resultrow === false )
    			break;

            $rowvalues = array();
            $rowshortnames = array();
            foreach ($completedarray as $key => $fieldname) {
                $shortname = Ef_Field::getShortnameFromName ($fieldname);
                // ${$varname} = $resultrow[$key];
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
                    $viewvalue = $this->renderField($Ef_List, $field, $fieldvalue, $irow, $fieldvalues);								
                } else {
                    $viewvalue = $fieldvalue;	
                }
                // replace %colname% by $viewvalue
                // Ef_Log::log($shortname
                $renderresult = str_replace ('%'.$shortname.'%', $viewvalue, $renderresult);
                $icol++;                
            }
            $renderresult .= $this->linesep[1];               
            $irow++;
            
        }
        
        $renderresult .= $this->footer;
        return $renderresult;
    			
    }
        
}


?>