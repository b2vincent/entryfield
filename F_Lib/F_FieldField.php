<?php
// Copyright Vincent Wartelle & Oklin.com 2016-2019
// EntryField framework - Open sourced under MIT license  

// Field : mainly, column of a database table
class F_Field 
{

	protected $name;
	protected $tbl;
	protected $attributes;

	protected static $fields = array();
	
	public function __construct($argname, $argattrib) 
    {
		if (!$argname) {
			throw new Exception ("field name is mandatory");	
		}         
        /*                                 
        // with this variant refuse two times the same field name - can't clone tables
        if (Ef_Field::findByName($argname) !== false) {
            throw new Exception ("Refused ! the field exists !!!!");
        } 
        */                               
		if (strpos($argname, '.') === 0) {
			throw new Exception ("field name must be of the form tab.name ");				
		}
		$this->name = $argname;		
		$posdot = strpos($argname, '.');
		$this->tbl = substr($this->name, 0, $posdot);
        
        // building this table is in process - foolproof control
        Ef_SqlTable::tblAliasInProgress($this->tbl);
		
		$this->attributes = $argattrib;
        
        // we allow overrding existing field - if unknown field, append it 
		foreach (self::$fields as $ifield=>$field) {
			$fieldname = $field->getName();
			if ($fieldname == $argname) {
                self::$fields[$ifield] = $this;
                return;
			}
		}        
		self::$fields[] = $this;
	}
	
	public function getTbl() 
    {
		return $this->tbl;
	}
	
    public function getShortName() 
    {
        $pos = strpos($this->name, '.');
        return substr ($this->name, $pos+1);
    }
	// Return the name, example of name : com.news_id
	public function getName() 
    {
		return $this->name;
	}
    
    // Return the translated name - 2016-12-14
    public function getDisplayName()
    {
        // find a translation     
        $trname = Ef_Lang::get($this->getName());
        if ($trname != $this->getName()) {
            return $trname;
        } 
        // find a translation associated to another alias of the table
        $tbl = $this->getTbl();
        $table = Ef_SqlTable::findByAlias($tbl);
        // 2018-08-10 - avoid crash when using non translated fields in virtual tables
        if (!$table) {
            return $trname;
        }
        $aliases = $table->getAliases();
        $shortname = $this->getShortName();
        foreach ($aliases as $alias) {
            if ($alias == $tbl) {
                continue;
            }
            $trname = Ef_Lang::get($alias.'.'.$shortname);        
            if ($trname != $alias.'.'.$shortname) {
                return $trname;
            }             
        }
        // no translation found
        return $this->getName();
    }         
    
    // Get post name for a given row
    public function getPostnameIrow($irow)
    {
        return Ef_Field::getPostnameFromNameIrow($this->name, $irow);     
    }
	
	// Find field (object) from its name, e.g. "com.new_id"
	public static function findByName($argname) 
    {
		foreach (self::$fields as $field) {
			$fieldname = $field->getName();
            // Ef_Log::log($fieldname,'fieldname in Ef_Field::findByName');
			if ($fieldname == $argname) {
				return $field;
			}
		}
		return false;
	}
	
	// Get name from edit name : example get "com.news_id" from "com-news_id-0" 
    // or "comview.com.news_id" from "comview-com-news_id-0" 
	public static function getNameFromEditname($postkey) 
    {    
        // replace  '-' by '.' but only once
        $nbdot = 1;
        $fieldname = str_replace('-','.',$postkey,$nbdot);              
        // remove suffix
        $lasthyph = strrpos($postkey,'-');
        $fieldname = substr($fieldname, 0, $lasthyph);
        return $fieldname;               
	}

    // Get postname from name and irow : get "com-news_id-0" from "com.news_id" and 0 
    public static function getPostnameFromNameIrow($argname, $irow) 
    {        
        $postkeyname = str_replace('.','-',$argname);
        $postname = ($postkeyname.'-'.$irow);
        return $postname;  
    }

    // Get ivarname from name and irow : get "com_news_id_0" from "com.news_id" and 0
    public static function getIvarnameFromNameIrow($argname, $irow) 
    {
        $postkeyname = str_replace('.','_',$argname);
        $postkeyname = str_replace('-','_',$postkeyname);         
        $postname = ($postkeyname.'_'.$irow);
        return $postname;    
    }

    // Get ivarname from name  : get "com_news_id" from "com.news_id"  
    public static function getIvarnameFromName($argname) 
    {
        $postkeyname = str_replace('.','_',$argname);
        $postkeyname = str_replace('-','_',$postkeyname);         
        return $postkeyname;    
    }
	
	// Get the row id from the post name (get 0 from "com-news_id-0" )
	public static function getRowFromEditName($postkey) 
    {	
		$lasthyph = strrpos($postkey,'-');
		$fieldrow = substr($postkey,$lasthyph+1);
		return $fieldrow;	
	}
	
	// Get variable name : example "com_news_id" from name "com.news_id"
	public static function getVarnameFromName($argname) 
    {	
		$fieldvarname = str_replace('.','_',$argname);
        $fieldvarname = str_replace('-','_',$fieldvarname); // 2015-12-31
		return $fieldvarname; 
	}
    
    // Get value name : example %efpa-title-value% from name "efpa.title"
    public static function getValuenameFromName($argname) 
    {
        $editname = str_replace('.', '-', $argname);
        $valuename = ('%'.$editname.'-value'.'%');    
        return $valuename;
    }

    // Get session value from a variable and a row
    public static function getSessValueFromNameIrow($argname, $irow=0)
    {        
        $sessionkey = Ef_Field::getIvarnameFromNameIrow($argname, $irow);
        // Ef_Log::log($sessionkey, '$sessionkey  in F_FieldField!!');
        $sessionval = (isset($_SESSION[$sessionkey])) ? $_SESSION[$sessionkey] : null;
        return $sessionval;
    }                  
        
    // Get short variable name : example "news_id" from "com.news_id"
    public static function getShortnameFromName($argname) 
    {
        $pos = strpos($argname, '.');
        return substr ($argname, $pos+1);    
    }
	
    // Get tbl attribute from name : example "com" from "com.news_id"
    public static function getTblFromName($argname) 
    {
        $pos = strpos($argname, '.');
        return substr($argname, 0, $pos);
    }
        
	public function getAttributes() 
    {
		return $this->attributes;	
	}

	public function getAttribute($attributename) 
    {
		if (array_key_exists($attributename, $this->attributes)) {
			return $this->attributes[$attributename];
		} else {
			return false;
		}			
	}
	
    // Get edit name : example "com-news_id-0" from "com.news_id" for row 0
	public function getEditname($parms=array()) 
    {
	    // Ef_Log::log ($parms, 'parms in getEditname');
		$fieldname = $this->getName();
        // dots are changed to hyphens
		$fieldname = str_replace('.', '-', $fieldname); 
	    if (array_key_exists('irow',$parms)) { 
	        // $fieldname = $tblprefix .'-'. $fieldname . '-'. $parms['irow'];
			$fieldname = $fieldname . '-'. $parms['irow'];
	    }
        return $fieldname;	    
	}

	// Add label to a field value of a field
    public function addLabel($fieldname, $stringvalue)
    {
        $fieldlabel = Ef_Lang::get($this->getName());
        
        return "$fieldlabel : $stringvalue";
        return $labeledstring;
    }

    // Show the value ready for html format
	public function memToViewHtml($value, $parms=array()) 
    {
        if ($this->getAttribute('translate')=='do') {
            return Ef_Lang::get($value);
        }    
		return htmlspecialchars($value);	
	}

    // Build a link associating the field and its value to a given target
    public function memToLinkHtml($value, $parms=array())
    {
        $fieldname = $this->getName();
        $linkpage = Ef_Config::get($fieldname.'_linkpage');
        if (!$linkpage) {
            return $this->memToViewHtml($value, $parms);
        }
        $linkcols = Ef_Config::get($fieldname.'_linkcols'); 
        $linkargs = Ef_Config::get($fieldname.'_linkargs');
        $linkcolsarr = explode(',',$linkcols);
        $linkargsarr = explode(',',$linkargs);
        $target = $linkpage;
        // if target page already has get parameters, use ampersand
        $sep = (strpos ($target, '?') === false) ? '?' : '&'; 
        $ilink = 0;        
        foreach ($linkcolsarr as $linkcol) {
            // Ef_Log::log($linkcol,'linkcol in memToLinkHtml');
            $target .= $sep;
            $sep = '&';
            $target .= $linkargsarr[$ilink];     
            $target .= '=';
            $fieldIvarname = Ef_Field::getIvarnameFromName($linkcol);
            
            $target .= Ef_Util::getArrayValue($parms['fieldvalues'], $fieldIvarname);            
            $ilink++;        
        }

        // with linkvalue we can replace the value by a symbol (for instance fa-magnify)
        $linkvalue = Ef_Config::get($fieldname.'_linkvalue'); 
        if (!$linkvalue) {
            $linkvalue = $this->memToViewHtml($value, $parms);  
        }
        return '<a href='.$target.'>'.$linkvalue.'</a>';
    }  

    // Calls a given function to render the field
    public function memToFunctionHtml($value, $parms=array())
    {
        $fieldname = $this->getName();
        $function = Ef_Config::get($fieldname.'_function');
        if (!$function || !function_exists($function)) {
            return $this->memToViewHtml($value, $parms);
        }
        $funccols = Ef_Config::get($fieldname.'_funccols'); 
        $funccols = str_replace(' ','', $funccols);
        $funccolsarr = explode(',',$funccols);
        // Ef_Log::log($funccolsarr,'funccolsarr in memToFunctionHtml');
        foreach ($funccolsarr as $funccol) {
            // Ef_Log::log($funccol,'funccol in memToFunctionHtml');
            $fieldIvarname = Ef_Field::getIvarnameFromName($funccol);
            
            $argarray[] = Ef_Util::getArrayValue($parms['fieldvalues'], $fieldIvarname);            
        }
        // Ef_Log::log($argarray, 'argarray in memToFunctionHtml');
        return call_user_func_array($function, $argarray);
    }  
    
    // Show editable value of the field         
	public function memToEditHtml($value, $parms=array()) 
    {
	    // Ef_Log::log($parms, 'parms dans memToEditHtml');
	    $fieldname = $this->getEditname ($parms);
	    $fieldvalue = htmlspecialchars($value);
        $fieldlength = $this->getAttribute('len');
        if ($fieldlength !== false) {
            if ($fieldlength <= 36) {        
                $size = "size=\"$fieldlength\"";
            } else {
                $size = "size=\"36\"";            
            }    
        } else {
            $fieldlength = 20;  // 001740
            $size = "size=\"20\"";
        }
        $maxlength = $this->getAttribute('maxlen');
        if ($maxlength !== false) {
            $max = "maxlength=\"$maxlength\"";    
        } else {
            // $max = "maxlength=\"20\"";  // 001740
            $max = "maxlength=\"$fieldlength\"";
        }        
        if (isset($parms['disabled'])) {
            $disabled = 'disabled';
        } else {
            $disabled = '';
        }
        if (isset($parms['readonly'])) {
            $readonly = 'readonly';
        } else {
            $readonly = '';
        }
        if (isset($parms['aligninput'])) {
            $alignstyle="style=\"text-align:".$parms['aligninput']."\"";
        } else {
            $alignstyle = '';
        }
        $passwordtype = $this->getAttribute('password');
        if ($passwordtype !== false) {
            $inputtype = "type=\"password\"";
        } else {        
            $inputtype = "type=\"text\"";
        }
        // 2019-12-10 - 001680 - manage $max for maxlength
		// $inputfield = "<input $inputtype $readonly data-dummy=\"dummy\" $disabled $alignstyle name=\"$fieldname\" id=\"$fieldname\" $size value=\"$fieldvalue\" />";
        $inputfield = "<input $inputtype $readonly data-dummy=\"dummy\" $disabled $alignstyle name=\"$fieldname\" id=\"$fieldname\" $size $max value=\"$fieldvalue\" />";
        
        // 2019-06-18 - 00600 - 'withlabel' : replace a view param by a field attribute - removed
        // $labeledfield = ($this->getAttribute('withlabel')) ? $this->addLabel($fieldname,$inputfield) : $inputfield;
        $labeledfield = (isset($parms['withlabel'])) ? $this->addLabel($fieldname,$inputfield) : $inputfield;
        return $labeledfield;        
	}
	
	
    // "Show" the value in hidden html form         
    public function memToHiddenHtml($value, $parms=array()) 
    {
	    $fieldname = $this->getEditname ($parms);
	    $fieldvalue = htmlspecialchars($value);
		return "<input type=\"hidden\" name=\"$fieldname\" id=\"$fieldname\" value=\"$fieldvalue\" />";	
	}

    // "Show" nothing of the value
	public function memToNoneHtml($value, $parms=array()) 
    {
		return '';
	}
	
	// Show the value in read only form
    public function memToReadonlyHtml($value, $parms=array()) 
    {
        $parms['readonly'] = true;
        return $this->memToEditHtml($value, $parms);
	}
    
	// Show the value in disabled form
	public function memToDisabledHtml($value, $parms=array()) 
    {
        $parms['disabled'] = true;
        return $this->memToEditHtml($value, $parms);
	}

	// Translate memory form to SQL string
	public function memToSql($value, $parms=array()) 
    {
		$nohtmlvalue = html_entity_decode($value);
		if (isset($parms['dbtype']) && $parms['dbtype'] == 'sqlite') {
            // if needed (php>=5.4), sqlite_escape_string is defined in F_FieldConfig.php
			$dbvalue = 	"'".sqlite_escape_string($nohtmlvalue)."'";    
		} else {		
			$dbvalue = "'".addslashes($nohtmlvalue)."'";
		}
		// $dbvalue = addslashes($value);
		return $dbvalue;
	}
	
    // Translate from POST format to memory format
	public function postHtmlToMem($value, $parms=array()) 
    {
		// $nohtmlvalue = html_entity_decode($value);
		// return $nohtmlvalue;
		return $value;
	}
    
    // Translate from SQL data format to memory data
    public function sqlToMem($value, $parms=array()) 
    {
		$retvalue = stripslashes($value);
		return $retvalue;
	}

	// List of all fields
    public static function getList() 
    {
		return self::$fields;
	}	
	
    // Static factory of fields
	public static function construct($argname, $argattrib) 
    {
	    // we build a f_field_$type according to the $type attribute
		if (!array_key_exists ( 'type' , $argattrib )) {
			$type = 'string';
		} else {
			$type = $argattrib['type'];
		}

		switch ($type) {
		    case 'string':
		        return new Ef_FieldString($argname, $argattrib);
		        break;
		    case 'select':
		        return new Ef_FieldSelect($argname, $argattrib);
		        break;
		    case 'date':
		        return new Ef_FieldDate($argname, $argattrib);
		        break;
		    case 'text':
		        return new Ef_FieldText($argname, $argattrib);
		        break;
		    case 'int':
		        return new Ef_FieldInt($argname, $argattrib);
		        break;
		    case 'amount':
		        return new Ef_FieldAmount($argname, $argattrib);
		        break;                
		    case 'button':
		        return new Ef_FieldButton($argname, $argattrib);
		        break;
		    case 'rowbutton':
		        return new Ef_FieldRowButton($argname, $argattrib);
		        break;
		    case 'radio':
		        return new Ef_FieldRadio($argname, $argattrib);
		        break;
		    case 'specific': 
		    	$fieldclass = $argattrib['class'];
		    	if (!$fieldclass) {
					throw new Exception ("missing attribute class");		    	
		    	}
		    	if (!class_exists($fieldclass)) {
					throw new Exception ("specific field type $type: '$fieldclass' is not a class ");		    	
		    	}
		    	if (!is_subclass_of($fieldclass, 'F_Field'))  {
		    		throw new Exception ("specific field type $type: '$fieldclass' is not a subclass of F_Field ");
		    	}
		    	return new $fieldclass($argname, $argattrib);
		        break;
		    default:
		        throw new Exception ("field type unknown : $type ");
		        break;
		}				
	}

    // Duplicate a field under the same name, but not the same alias		
    public static function cloneWithAlias($origField, $newalias) 
    {
        $shortname = $origField->getShortName();
        $newname = $newalias.'.'.$shortname;
        // hard clone
        $newField = unserialize(serialize($origField));
        $newField->tbl = $newalias;
        $newField->name = $newname;
        
        Ef_Field::$fields[] = $newField;

        return $newField;                
    }

}

// Specialize Ef_Field to allow inherit from it - 2019-11-19 - 001630
if (isset($basepath) && is_readable($basepath.'/extends/Ef_Field_Extends.php')) {
    require_once($basepath.'/extends/Ef_Field_Extends.php');
} else {
    class Ef_Field extends F_Field {}
}


// String field
class F_FieldString extends Ef_Field 
{

	protected $len;
	protected $maxlen;
 
	public function __construct($argname, $argattrib) {
		parent::__construct($argname,$argattrib);
		
		if (array_key_exists('len',$argattrib)) {
		    $this->len = $argattrib['len'];
		}
		if (array_key_exists('maxlen',$argattrib)) {
		    $this->maxlen = $argattrib['maxlen'];
		}
		return $this;
	}	
}

// Select field 
class F_FieldSelect extends Ef_Field 
{

	protected $len;
	protected $keyvals;
 
	public function __construct($argname, $argattrib) 
    {
		parent::__construct($argname,$argattrib);
		
		if (array_key_exists('len',$argattrib)) {
		    $this->len = $argattrib['len'];
		}
		if (array_key_exists('keyvals',$argattrib)) {
			$this->keyvals = $argattrib['keyvals'];		
		}
		return $this;
	}	

	public function setKeyVals($argkeyvals) 
    {
		$this->keyvals = $argkeyvals;	
	}					

    // Show the value ready for html format - 2018-06-29
	public function memToViewHtml($value, $parms=array()) 
    {
        $keyvalue = htmlspecialchars($value);
        $fieldvalue = '';
        if (isset($this->keyvals[$keyvalue])) {
            $fieldvalue = $this->keyvals[$keyvalue];                 
        } 
        if ($this->getAttribute('translate')=='do') {
            return Ef_Lang::get($fieldvalue);
        } else {
            return $fieldvalue;
        }    
	}
    

	public function memToEditHtml($value, $parms=array()) 
    {
	    // Ef_Log::htmlEcho($parms, 'parms dans memToEditHtml');
	    $fieldname = $this->getEditname ($parms);
	    $fieldvalue = htmlspecialchars($value);
        if (isset($parms['disabled'])) {
            $disabled = 'disabled';
        } else {
            $disabled = '';
        }        
        if (isset($parms['readonly'])) {
            $readonly = 'readonly';
        } else {
            $readonly = '';
        }        
		$inputfield = "<select $readonly $disabled data-dummy=\"dummy\" name=\"$fieldname\" id=\"$fieldname\">\n";
		foreach ($this->keyvals as $key=>$val) {
			$inputfield .= "<option value=\"$key\"";
			if ($key == $fieldvalue ) {
				$inputfield .= " selected";
			}
            // translate values of select fields - 2016-12-15
            $libVal = $val;
            if ($this->getAttribute('translate')=='do') {
                $libVal = Ef_Lang::get($value);
            }    
			$inputfield .= ">$libVal</option>\n";
		}
		$inputfield .= "</select>\n";
        $labeledfield = (isset($parms['withlabel'])) ? $this->addLabel($fieldname,$inputfield) : $inputfield;
        return $labeledfield;
		// example of select
		// <select name='type'>
		// <option value='edit' selected>Edit </option>
		// <option value='delete'>Delete</option>
		// </select>		
	}					
}

// Radiobutton field
class F_FieldRadio extends F_FieldSelect 
{

	public function __construct($argname, $argattrib) 
    {
		parent::__construct($argname,$argattrib);
	}	

    public function addLabel($fieldname, $stringvalue)
    {
        return $stringvalue;
    }

	public function memToEditHtml($value, $parms=array()) 
    {
	    // Ef_Log::htmlEcho($parms, 'parms dans memToEditHtml');
	    $fieldname = $this->getEditname($parms);
	    $fieldvalue = htmlspecialchars($value);
        if (isset($parms['disabled'])) {
            $disabled = 'disabled';
        } else {
            $disabled = '';
        }        
        if (isset($parms['readonly'])) {
            $readonly = 'readonly';
        } else {
            $readonly = '';
        }        

        $inputfield = "\n";
        // 001760 - inline is an attribute 'valuesep', not an inaccessible parm
        // if (!isset($parms['inline'])) 
        //     $inputfield .= "<br/>\n";        
        if ($this->getAttribute('valuesep')) { 
            $inputfield .= $this->getAttribute('valuesep'); 
        } else { 
            $inputfield .= "<br/>\n";                    
        }                
        foreach ($this->keyvals as $key=>$val) {
            $fieldnamekey = $fieldname.'-'.$key;
            $inputfield .= "<input $readonly $disabled data-dummy=\"dummy\" type=\"radio\" name=\"$fieldname\" id=\"$fieldnamekey\" ";
            $inputfield .= "value=\"$key\" ";
            if ($key == $fieldvalue) {
                $inputfield .= " checked";
            } 
            $inputfield .= "> ".$val;
            // 001760 - inline is an attribute 'valuesep', not an inaccessible parm
            // if (!isset($parms['inline'])) 
            //     $inputfield .= "<br/>\n";        
            if ($this->getAttribute('valuesep')) { 
                $inputfield .= $this->getAttribute('valuesep'); 
            } else { 
                $inputfield .= "<br/>\n";        
            }                
        }
        $inputfield .= "\n";
        $labeledfield = (isset($parms['withlabel'])) ? $this->addLabel($fieldname,$inputfield) : $inputfield;
        return $labeledfield;
        // example of radio (we can add id attribute, identical to value) 
        // <input type="radio" name="sex" value="male" checked>Male
        // <input type="radio" name="sex" value="female">Female        
	}					
}


// Date field
class F_FieldDate extends Ef_Field 
{

    public function memToViewHtml ($value, $parms=array()) 
    {
        $dateformat = Ef_Application::getDateFormat();
        
        if ($value) {
            $date = new DateTime($value);
            return $date->format($dateformat);
        } else {
            return '';
        }
    }
	
	public function memToEditHtml ($value, $parms=array()) 
    {
	    $fieldname = $this->getEditname ($parms);
	    $fieldvalue = $this->memToViewHtml($value, $parms);
		$inputfield = "<input name=\"$fieldname\" id=\"$fieldname\" size=\"10\" data-dummy=\"dummy\" value=\"$fieldvalue\" />";	
        $labeledfield = (isset($parms['withlabel'])) ? $this->addLabel($fieldname,$inputfield) : $inputfield;
        return $labeledfield;
	}

    public function memToReadonlyHtml ($value, $parms=array()) 
    {
	    $fieldname = $this->getEditname ($parms);
	    $fieldvalue = $this->memToViewHtml($value, $parms);
		$inputfield =  "<input disabled name=\"$fieldname\" id=\"$fieldname\" size=\"10\" value=\"$fieldvalue\" />";
        $labeledfield = (isset($parms['withlabel'])) ? $this->addLabel($fieldname,$inputfield) : $inputfield;
        return $labeledfield;        	
	}
	
	// Convert local date to iso date
	// admit complete date with /, or limit format without separator
	public function postHtmlToMem ($date, $parms=array()) 
    {
		$strlendate = strlen($date);

		$dateformat = Ef_Application::getDateFormat();
		$defaultcentury = Ef_Application::getDefaultCentury();
		
		// date without / : process lengths 6 or 8
		if (strpos($date,'/') === false) {
			if ($strlendate == 6) {
				switch ($dateformat) {
					case 'd/m/Y':
						$mday = substr ($date, 0, 2); $mmonth = substr ($date, 2, 2); $myear = substr ($date, 4, 2);
						break;
					case 'm/d/Y':
						$mmonth = substr ($date, 0, 2); $mday = substr ($date, 2, 2); $myear = substr ($date, 4, 2);
						break;
					case 'Y/m/d':
					default:
						$myear = substr ($date, 0, 2); $mmonth = substr ($date, 2, 2); $mday = substr ($date, 4, 2);
						break;					
				}
				// year on 2 is changed to year on 4
				$myear = $defaultcentury . $myear;           
			} 
			elseif ($strlendate == 8) {
				switch ($dateformat) {
					case 'd/m/Y':
						$mday = substr ($date, 0, 2); $mmonth = substr ($date, 2, 2); $myear = substr ($date, 4, 4);
						break;
					case 'm/d/Y':
						$mmonth = substr ($date, 0, 2); $mday = substr ($date, 2, 2); $myear = substr ($date, 4, 4);
						break;
					case 'Y/m/d':
					default:
						$myear = substr ($date, 0, 4); $mmonth = substr ($date, 4, 2); $mday = substr ($date, 6, 2);						
						break;					
				}       
			}
			else return false;
			
		} else {
			// date with / : break in three parts
			switch ($dateformat) {
				case 'd/m/Y':
				default:
					list($mday, $mmonth, $myear) = explode('/', $date); 
					break;
				case 'm/d/Y':
					list($mmonth, $mday, $myear) = explode('/', $date); 
					break;
				case 'Y/m/d':
					list($myear, $mmonth, $mday) = explode('/', $date); 
					break;				
			}
			// length 1 or 2 are set to 2
			if (strlen($mday)==1) 
				$mday = ('0'.$mday);		
			if (strlen($mmonth)==1) 
				$mmonth = ('0'.$mmonth);
			if (strlen($myear)==1) 
				$myear = ('0'.$myear);
			// year on 2 is changed to 4
			if (strlen($myear)==2) {
				$myear = ($defaultcentury.$myear);
			}
		}
		if (checkdate ($mmonth, $mday, $myear)) {
			$retdate = $myear .'-'. $mmonth .'-'. $mday;
			// Ef_Log::htmlEcho($retdate,'retdate');
			return $retdate;
		} else {
			return false;
		}
	}
}

// Text field with text area
class F_FieldText extends F_FieldString 
{

	protected $cols;
	protected $rows;
 
	public function __construct($argname, $argattrib) 
    {
		parent::__construct($argname,$argattrib);
		
		if (array_key_exists('cols',$argattrib)) {
		    $this->cols = $argattrib['cols'];
		}
		if (array_key_exists('rows',$argattrib)) {
		    $this->rows = $argattrib['rows'];
		}
		return $this;
	}	

	public function memToViewHtml($value, $parms=array())
	{
		if (isset($GLOBALS['textparser']) && is_object($GLOBALS['textparser'])) {
			$textparser = $GLOBALS['textparser']; 			
			$viewvalue =  $textparser->text($value);
			return $viewvalue;					
		} else {
			$viewvalue =  parent::memToViewHtml($value, $parms);
			return $viewvalue;
		}			
	}
	
    // 001790 - better managing readonly and disabled - 2020-04-28
    // public function memToReadonlyHtml($value, $parms=array()) 
    // {
	//     $fieldname = $this->getEditname ($parms);
	//     $fieldvalue = htmlspecialchars($value);
	// 	$colsize = '';
	//     if ($this->cols) {
	//     	$colsize = 'cols="'.$this->cols.'"';
	//     }
	//     $rowsize = '';
	//     if ($this->rows) {
	//     	$rowsize = 'rows="'.$this->rows.'"';
	//     }	    
	// 	$inputfield = "<textarea readonly $colsize $rowsize data-dummy=\"dummy\" name=\"$fieldname\" id=\"$fieldname\">$fieldvalue</textarea>";
    //     $labeledfield = (isset($parms['withlabel'])) ? $this->addLabel($fieldname,$inputfield) : $inputfield;
    //     return $labeledfield;
    // }

	public function memToEditHtml($value, $parms=array()) 
    {
	    $fieldname = $this->getEditname ($parms);
	    $fieldvalue = htmlspecialchars($value);
		$colsize = '';
	    if ($this->cols) {
	    	$colsize = 'cols="'.$this->cols.'"';
	    }
	    $rowsize = '';
	    if ($this->rows) {
	    	$rowsize = 'rows="'.$this->rows.'"';
	    }	    
        // 001790 - managing readonly and disabled - 2020-04-28
        $disabled = '';
        if (isset($parms['disabled']) && $parms['disabled']) {
            $disabled = 'disabled';
        }
        $readonly = '';
        if (isset($parms['readonly'])  && $parms['readonly']) {
            $readonly = 'readonly';
        }
		$inputfield = "<textarea $readonly $disabled $colsize $rowsize data-dummy=\"dummy\" name=\"$fieldname\" id=\"$fieldname\">$fieldvalue</textarea>";
        $labeledfield = (isset($parms['withlabel'])) ? $this->addLabel($fieldname,$inputfield) : $inputfield;
        return $labeledfield;
	}	
}

 // Button field
 class F_FieldButton extends Ef_Field 
 {
    public function addLabel($fieldname, $stringvalue)
    {
        $fieldlabel = '&nbsp;';
        $labeledstring = ("
            <div class=\"form-group\">
            <label for=\"$fieldname\" class=\"col-sm-2 control-label\">$fieldlabel</label>
            $stringvalue        
            </div>        
        ");
        return $labeledstring;
    }

    public function memToViewHtml($value, $parms=array()) 
    {
	    $fieldname = $this->getEditname($parms);
	    $fieldvalue = htmlspecialchars($value);
		$inputfield = "<input disabled type=\"button\" data-dummy=\"dummy\" name=\"$fieldname\" id=\"$fieldname\" value=\"$fieldvalue\" />";	
        $labeledfield = (isset($parms['withlabel'])) ? $this->addLabel($fieldname,$inputfield) : $inputfield;
        return $labeledfield;
    }

	public function memToEditHtml($value, $parms=array()) 
    {
	    $fieldname = $this->getEditname($parms);
	    $fieldvalue = htmlspecialchars($value);
		$inputfield = "<input type=\"button\" data-dummy=\"dummy\"  name=\"$fieldname\" id=\"$fieldname\" value=\"$fieldvalue\" />";	
        $labeledfield = (isset($parms['withlabel'])) ? $this->addLabel($fieldname,$inputfield) : $inputfield;
        return $labeledfield;
	}
	
	public function memToReadonlyHtml($value, $parms=array()) 
    {
		return $this->memToViewHtml($value, $parms);
	} 	
}

// Button field, contextual to a given row
class F_FieldRowButton extends Ef_Field 
{
	protected $buttonprefix;
    protected $buttontext;
    protected $rowidname;

	public function __construct($argname, $argattrib) 
    {
		parent::__construct($argname,$argattrib);
		
		if (array_key_exists('buttonprefix',$argattrib)) 
        {
		    $this->buttonprefix = $argattrib['buttonprefix'].'_';
		}
		if (array_key_exists('buttontext',$argattrib)) 
        {
		    $this->buttontext = $argattrib['buttontext'];
		}
		if (array_key_exists('rowidname',$argattrib)) 
        {
		    $this->rowidname = $argattrib['rowidname'];
		}
		return $this;
	}	

    // access to the row identifier : default is the 'irow' = row number on screen
    public function getRowid ($parms=array()) 
    {
        extract ($parms['fieldvalues']);                	    
        if ($this->rowidname) {
            return ${$this->rowidname}; 
        } else if (array_key_exists('irow', $parms)) {
            return $parms['irow'];              
        } else {
            return '';
        }    
    }

    public function addLabel($fieldname, $stringvalue)
    {
        // Changed 2018-08-13
        // $fieldlabel = '&nbsp;';                   
        // $labeledstring = ("
        //     <div class=\"form-group\">
        //     <label for=\"$fieldname\" class=\"col-sm-2 control-label\">$fieldlabel</label>
        //     $stringvalue        
        //     </div>        
        // ");
        // return $labeledstring;
        return $stringvalue;            
    }
    
    
    public function memToViewHtml ($value, $parms=array()) 
    {
        // $value is ignored
        // $rowidname = $parms['rowidname'];
        // extract ($parms['fieldvalues']);                	    
        $rowid = $this->getRowid($parms);
	    // $fieldname = $this->buttonprefix .${$this->rowidname};
        $fieldname = $this->buttonprefix.$rowid;
        // $buttontext = str_replace ( '%row%', ${$this->rowidname}, $this->buttontext ); 
        $buttontext = str_replace ( '%row%', $rowid, $this->buttontext );
        
		$inputfield = "<input disabled type=\"button\" data-dummy=\"dummy\"  name=\"$fieldname\" id=\"$fieldname\" value=\"$buttontext\" />";	
        $labeledfield = (isset($parms['withlabel'])) ? $this->addLabel($fieldname,$inputfield) : $inputfield;
        return $labeledfield;
    }

	public function memToEditHtml ($value, $parms=array()) 
    {
        $rowid = $this->getRowid($parms);
	    // $fieldname = $this->buttonprefix .${$this->rowidname};
        $fieldname = $this->buttonprefix.$rowid;
        // $buttontext = str_replace ( '%row%', ${$this->rowidname}, $this->buttontext ); 
        $buttontext = str_replace ( '%row%', $rowid, $this->buttontext );
        
		$inputfield = "<input type=\"submit\" name=\"$fieldname\" data-dummy=\"dummy\" id=\"$fieldname\" value=\"$buttontext\" />";	
        $labeledfield = (isset($parms['withlabel'])) ? $this->addLabel($fieldname,$inputfield) : $inputfield;
        return $labeledfield;             
	}
	
	public function memToReadonlyHtml ($value, $parms=array()) 
    {
		return $this->memToViewHtml($value, $parms);
	} 
                                     	
	public function getPostedRow () 
    {
		$postedkeys = array_keys($_POST);
		
		foreach ($postedkeys as $postedkey) {
			if (strpos ($postedkey, $this->buttonprefix) !== false) {
				$posunderscore = strrpos ($postedkey, '_');
				$postedrow = substr ($postedkey, $posunderscore+1); 

				return $postedrow;
			}
		}
		return false;
	}
}		

// Integer field
class F_FieldInt extends Ef_Field 
{
	public function __construct($argname, $argattrib) 
    {
        $intparms = array('len'=>'9');
        $newattrib = array_merge($argattrib,$intparms); 
		parent::__construct($argname,$newattrib);
	}
	public function memToEditHtml($value, $parms=array()) 
    {
        // 001670 - 2019-11-29 - some need for left aligned descendants of F_FieldInt                      
        // $intparms = array('aligninput'=>'right'); 
        // $newparms = array_merge($parms, $intparms);        
        // return parent::memToEditHtml($value, array_merge($parms, $intparms));
        if (!isset($parms['aligninput'])) {
            $parms['aligninput'] = 'right';
        }
        return parent::memToEditHtml($value, $parms);
    }
}

// Amount field (or other fixed decimals)
class F_FieldAmount extends Ef_Field
{

	protected $decpoint;
	protected $septhous;
    protected $nbdec;
 
	public function __construct($argname, $argattrib) 
    {
		parent::__construct($argname,$argattrib);
		
		if (array_key_exists('decpoint',$argattrib)) {
		    $this->decpoint = $argattrib['decpoint'];
		} else {
            $this->decpoint = '.';
        }
		if (array_key_exists('septhous',$argattrib)) {
			$this->septhous = $argattrib['septhous'];		
		} else {
            $this->septhous = ' ';
        }
		if (array_key_exists('nbdec',$argattrib)) {
			$this->nbdec = $argattrib['nbdec'];		
		} else {
            $this->nbdec = 2;
        }
		return $this;
	}	

    // inputing a value : inject style="text-align:right" in the input field
    public function memToEditHtml($value, $parms=array()) 
    {    
        if (trim($value) == '') 
            $value = 0;
        $newvalue = number_format($value, $this->nbdec, $this->decpoint, $this->septhous);
        $parms['aligninput'] = 'right';
        $editvalue = parent::memToEditHtml($newvalue, $parms); 
        // $alignedvalue = '<div style="text-align:right">'.$editvalue.'</div>';
        return $editvalue;        
    }

    // displaying a value : put it in a div aligned to right
    public function memToViewHtml($value, $parms=array()) 
    {
        $newvalue =  number_format($value, $this->nbdec, $this->decpoint, $this->septhous);
        $newvalue = '<div style="text-align:right">'.$newvalue.'</div>';
        return $newvalue;
    }
    
    // changing postvalue to memory value : 
    // changes decimal point to . and thousand separator to empty
    public function postHtmlToMem($value, $parms=array())
    {
        $value1 = str_replace( $this->decpoint, '.', $value);
        $value2 = str_replace( $this->septhous, '', $value1);
        return $value2;
    } 
}

// ?>