<?php
// Copyright Vincent Wartelle & Oklin.com 2016-2017
// EntryField framework - Open sourced under MIT license  

// html array
class F_Array 
{
    protected $warray;
    public function __construct($argarray) 
    {
        if (is_array ($argarray))
            $this->warray = $argarray;
        else
            $this->warray = array();
    }

    public function render($parms=array()) 
    {

        if (array_key_exists('style', $parms)) {
            $style = $parms['style'];
        } else {
            $style = 'html';
        }
        
        switch ($style) {
            case 'html':
                return ($this->renderHtml($parms));
                break;
            default:
                throw new Exception ("cannot render array,  method : $style does not exist");
                break;
        }
    }
    
    public function renderHtml($parms=array()) 
    {
        
        $rettext = '<table border=1>';
        foreach ($this->warray as $row) {
            $rettext .= "<tr>\n";
            foreach ($row as $cell) {
                $rettext .= "<td>";
                $rettext .= $cell;
                $rettext .= "</td>";            
            }
            $rettext .= "</tr>\n";          
        }
        $rettext .= '</table>';
        return $rettext;
    }
}


// SQL Query
class F_SqlReq 
{
    protected $sqlquery;
    protected $statement;
    protected $dbid;
    
    public function __construct($argsqlquery, $argdbid='def') 
    {
        // Ef_Log::log ($argsqlquery, "building this request on db $argdbid");
        $this->sqlquery = $argsqlquery;
        $this->dbid = $argdbid;
        
    }
    
    // Prepare execution of sql statement
    public function prepare() 
    {
        $pdolink = Ef_Db::dbLink($this->dbid);
        if (!isset($pdolink)) {
            throw new Exception ("prepare $this->sqlquery \n : it seems like having no db connection");
        }
        // Ef_Log::log($pdolink, 'pdolink in F_SqlReq::prepare()');
        
        $statmt = $pdolink->prepare($this->sqlquery);     
        // Ef_Log::log($statmt,'statmt in F_SqlReq::prepare');
           
        if (!$statmt) {
        	$errorArray = $pdolink->errorInfo();
        	$errorMsg = implode(" / ", $errorArray);        
            throw new Exception ("prepare $this->sqlquery \n : invalid request ". $errorMsg);
        }
        $this->statement = $statmt;
        return $this->statement;
    }
    
    // make a quoted string
    public function reqQuote($string)
    {
        $pdolink = Ef_Db::dbLink($this->dbid);
        if (!isset($pdolink)) {
            throw new Exception ("prepare $this->sqlquery \n : it seems like having no db connection");
        }
        $tmpstring = $pdolink->quote($string);
        // remove external quotes
        if (substr($tmpstring, 0, 1) == '\'' && substr($tmpstring, -1, 1) == '\'')
            return substr($tmpstring, 1, strlen($tmpstring)-2);   
        else 
            return $string;             
    }
    
    // add slashes for php code and quote for sql insertion
    public static function addSlashQuote($string)
    {
        $string = addslashes($string);
        $quotereq = new Ef_SqlReq("");
        return $quotereq->reqQuote($string);
    }

    // add quote for sql insertion
    public static function quote($string)
    {
        $quotereq = new Ef_SqlReq("");
        return $quotereq->reqQuote($string);
    }
    

    
    // Execute sql statement and return array of rows
    // $resultmode may be PDO::FETCH_ASSOC (associative array) or PDO::FETCH_NUM
    // other values, see http://php.net/manual/fr/pdostatement.fetch.php
    public function getRows($resultmode=PDO::FETCH_ASSOC) 
    {                
        if (!$this->statement)
            $this->statement = $this->prepare();            
        $this->statement->execute();
        $nbrows = 0;
        $resultvalues = array();
        while ($row = $this->statement->fetch($resultmode)) {
                // Ef_Log::log ($row,'row');
            $resultvalues[$nbrows] = $row;
            $nbrows++;
        }
        return $resultvalues;
    }

    // Execute sql statement and return row
    public function getRow($resultmode=PDO::FETCH_ASSOC) 
    {                
        if (!$this->statement)
            $this->statement = $this->prepare();            
        $this->statement->execute();
        $row = array();
        $row = $this->statement->fetch($resultmode);
        // Ef_Log::log ($row,'row');
        return $row;
    }
    
    // Execute sql statement and return next row - 2016-11-29
    // the statement is already executed and we query row after row
    public function getNextRow($resultmode=PDO::FETCH_ASSOC) 
    {                
        if (!$this->statement)
            throw new Error("Can't use getNextRow if statement is not created");
        $row = $this->statement->fetch($resultmode);
        // Ef_Log::log ($row,'row');
        return $row;
    }
    // Execute sql statement and return value
    public function getValue() 
    {
        if (!$this->statement)
            $this->statement = $this->prepare();            
        $result = $this->statement->execute();
        return $this->statement->fetchColumn();
    }
   
    public function getSqlQuery()
    {
        return $this->sqlquery;        
    } 
    
    // change sql query in extremis if necessary - 2016-12-29
    public function setSqlQuery($query)
    {
        $this->sqlquery = $query;
    }
    
    // Execute sql statement with no value returned
    public function execute() 
    {
        if (!$this->statement)
            $this->statement = $this->prepare();            
        $result = $this->statement->execute();
        
        // Ef_Log::log ($result,'result in F_SqlReq::execute()');        
        if (!$result) {
        	$errorArray = $this->statement->errorInfo();
        	$errorMsg = implode(" / ", $errorArray);        
            throw new Exception ("execute $this->sqlquery \n : error ". $errorMsg);                
        }

        if (Ef_Config::get('f_logsql_function')) {
            $logSqlFunction = Ef_Config::get('f_logsql_function'); 
            if (is_callable($logSqlFunction)) {
                call_user_func_array($logSqlFunction, array($this->sqlquery)); 
            } 
        } 
    }
        
    // Execute several insert statements        
    public static function groupInsert($table, $fieldlist, $valuelists, $database='')
    {
        $dbtype = Ef_Config::get('f_db_dbtype');
        
        foreach ($valuelists as $valuelist) {
        
            $valuearray = explode (',', $valuelist);
            $quotedvaluearray = array();
            foreach ($valuearray as $value) {
                // see function F_Field::memToSql (same)
                $value = trim($value, ' \'');     
                $value = stripslashes($value);               
                    
                if ($dbtype && $dbtype == 'sqlite') {
            	     $quotedvaluearray[] = 	"'".sqlite_escape_string($value)."'";    
                } else {		
            	     $quotedvaluearray[] = "'".addslashes($value)."'";
                }
            }
            $quotedvaluelist = implode(', ', $quotedvaluearray);
                                               
            $insertsqlreq = new Ef_SqlReq("
                insert into $table ($fieldlist) values ($quotedvaluelist);
            ",$database);
            // Ef_Log::log($insertsqlreq, 'insertsqlreq');
            
            $insertsqlreq->execute();        
    }
    }

}

// Sql table
class F_SqlTable 
{
    protected $name;
    protected $alias;
    protected $fieldarray = array();    
    protected $fieldarrayobj = array();
    protected $fieldkeyarray = array();
    protected $dbid;

    protected static $tables = array();
    // table alias being built - to allow fool proof check
    protected static $tblaliasinprogress;

    public function __construct($argname, $argalias, $argdbid='def') 
    {
        
        foreach (self::$tables as $table) {
            #   2016-03-24 : allow table cloning
            #   if ($table->name == $argname) {
            #       throw new Exception ("table name already exists : $argname");
            #   }
            if ($table->alias == $argalias) {
                 throw new Exception ("table alias already exists : $argalias"); 
            }
        }
        $this->name = $argname;
        $this->alias = $argalias;
        $this->dbid = $argdbid;
        self::$tables[] = $this;          
    }
    
    public function getName() 
    {
        return $this->name;
    }
    
    public function getAlias() 
    {
        return $this->alias;
    }

    public function getDbid()
    {
        return $this->dbid;
    }
    
    // Build the field array of the table, getting all fields associated to it
    public function buildFieldArray() 
    {
        foreach (Ef_Field::getList() as $field) {       
            if ($field->getTbl() == $this->alias) {
                $this->fieldarray[] = $field->getName();
                $this->fieldarrayobj[] = $field;
                
                if ($field->getAttribute("keypos") !== false) {
                    $keypos = $field->getAttribute("keypos");
                    $this->fieldkeyarray[$keypos] = $field->getName();             
                }
            }
        }
        // Ef_Log::log($this->alias,'definition of this table alias is done');
        Ef_SqlTable::tblAliasFinished($this->alias); // 2016-01-18        
    }
    
    public function getFieldArray() 
    {
        return $this->fieldarray;   
    }
    
    public function getFieldKeyArray() 
    {   
        return $this->fieldkeyarray;
    }
    
    public function getFieldList() 
    {
        if (count($this->fieldarray) ==0)
            $this->buildFieldArray();
        return Ef_SqlTable::fieldArrayToFieldList ($this->fieldarray);
    }
    
    
    public function getFieldArrayObj() 
    {
        if (count($this->fieldarray) ==0)
            $this->buildFieldArray();
        
        return $this->fieldarrayobj;    
    }

    // Gets an ID for a new row
    public function getNewId($numId='', $maxvalue='') 
    {        
        if ($numId == '') {
            if (count($this->fieldkeyarray) != 1) {
                throw new Exception ("use of getNewId possible if one and only one key in table");
            }
            $fieldkeyname = $this->fieldkeyarray[0];
            $field = Ef_Field::findByname($fieldkeyname);
            if ($field->getAttribute('type') != 'int') {
                throw new Exception ("use of getNewId is possible only on integer key");
            }
            $fieldshortname = Ef_Field::getShortNameFromName($fieldkeyname);
        } else {
            $fieldshortname = $numId;
        }
        $tablename = $this->name; 
		$maxlineidsreq = ( "
			select max($fieldshortname) from $tablename \n 
		");
        if ($maxvalue) {
            $maxlineidsreq .= " where $fieldshortname < $maxvalue \n ";
        }
		$maxreq = new Ef_SqlReq($maxlineidsreq,$this->dbid);
		$newlineid = ($maxreq->getValue()) +1;
        return $newlineid;                            
    }        

    // Get an array of all aliases of the table - 2016-12-14
    public function getAliases()
    {
        $aliases = array();        
        foreach (self::$tables as $table) {
            if ($table->getName() == $this->getName()) {
                $aliases[] = $table->alias;
            } 
        }        
        return $aliases;
    } 

    // List all tables
    public static function getList() 
    {
        return self::$tables;
    }
    
    // Echo a list of all tables
    public static function echoList() 
    {
        foreach (self::$tables as $table) {
            echo "table : $table->name, $table->alias <br>";
        }
    }
    
    public static function findByName($argname) 
    {
        foreach (self::$tables as $table) {  
            if ($table->name == $argname)
                return $table;
        }
        return null;
    }                                                
    
    public static function findByAlias($argalias) 
    {
        foreach (self::$tables as $table) {
            if ($table->alias == $argalias)
                return $table;
        }
        return null;    
    }
    
    public static function fieldArrayToFieldList($fieldarray) 
    {
        $resstring = "";
        foreach ($fieldarray as $fieldname) {
            if ($resstring) 
                $resstring .= ', ';
            $resstring .= $fieldname;       
        }
        return $resstring;
    }
    
    //  Foolproof : keep information that a table is being built
    public static function tblAliasInProgress($argalias)
    {
        // another table is building
        if (self::$tblaliasinprogress &&  self::$tblaliasinprogress != $argalias) {
            throw new Exception("Trying to build ".$argalias." while table ".self::$tblaliasinprogress." is not finished");
        }
        // continuing to build the same table
        if (self::$tblaliasinprogress == $argalias) {
            return;
        }        
        // beginning to build this table
        if (Ef_SqlTable::findByAlias($argalias)) {
            self::$tblaliasinprogress = $argalias;
        }
    }

    //  Foolproof : declare that a table is completely defined
    public static function tblAliasFinished($argalias)
    {
        if (self::$tblaliasinprogress == $argalias) {
            self::$tblaliasinprogress = '';    
        } else {
            throw new Exception("Trying to finish ".$argalias." while table ".self::$tblaliasinprogress." is not finished");        
        }
    }
    
    // Foolproof : give info about a table currently being bilt
    public static function getTblAliasInProgress()
    {
        return self::$tblaliasinprogress; 
    }
    
    // Duplicate a table definition with a given alias
    public static function cloneWithAlias($tablename, $newalias) 
    {
        $tableobj = Ef_SqlTable::findByName($tablename);        
        $dbid = $tableobj->getDbid();
                
        $newtableobj = new Ef_SqlTable($tablename, $newalias, $dbid);
        
        // aalgorithm seen in Ef_SqlTable::buildFieldArray        
        foreach (Ef_Field::getList() as $field) {       
            if ($field->getTbl() == $tableobj->getAlias()) {
                $clonedfield = Ef_Field::cloneWithAlias($field, $newalias);
                $newtableobj->fieldarray[] = $clonedfield->getName();
                $newtableobj->fieldarrayobj[] = $clonedfield;
                
                if ($clonedfield->getAttribute("keypos") !== false) {
                    $keypos = $clonedfield->getAttribute("keypos");
                    $newtableobj->fieldkeyarray[$keypos] = $clonedfield->getName();             
                }
            }
        }
        // Ef_SqlTable::tblAliasFinished($newalias);         
        // Ef_Log::log($newtableobj,'newtableobj at end of cloneWithAlias');
    }
    
        
}

//  A virtual table is like F_SqlTable but does not exist in the database
class F_VirtualTable extends F_SqlTable 
{
    public function __construct($argname, $argalias) 
    {
        parent::__construct($argname, $argalias);        
    }               
               
    // Make sure that each column has a session value (which may be '')          
    private function initSessionRow($irow=0)
    {
        foreach ($this->getFieldArrayObj() as $field) {
            $fieldname = $field->getName();
            $ivarnameN = $field->getIVarnameFromNameIrow($fieldname, $irow);
            // Ef_Log::log($ivarnameN, "ivarnameN in initSessionRow");
            if (!array_key_exists($ivarnameN, $_SESSION)) {
                $_SESSION[$ivarnameN] = '';
            }                               
        }            
    } 

                                                    
    // Get the fields posted as a row of variables usable as names 
    public function getPostedFieldsInMemRow ($irow=0) 
    {
        if (count($_POST) > 0) {
            $resultrow = array();
            foreach ($this->getFieldArrayObj() as $field) {
                $fieldname = $field->getName();                                 
                $postnameN = $field->getPostnameFromNameIrow($fieldname, $irow);
                $ivarnameN = $field->getIVarnameFromNameIrow($fieldname, $irow);
                
                if (array_key_exists($postnameN, $_POST)) {
                    $postvalue = $_POST[$postnameN];
                    $resultrow[$ivarnameN] = $field->postHtmlToMem($postvalue);
                } else {
                    $resultrow[$ivarnameN] = '';
                }                   
            }
        }
        // Ef_Log::log($resultrow, 'resultrow in getPostedFieldsInMemRow');
        return $resultrow;
    }    

    // Keep a memory row into session
    public function setMemRowInSession ($memrow, $irow=0) 
    {
        if (count($memrow) > 0) {
            foreach ($this->getFieldArrayObj() as $field) {
                $fieldname = $field->getName();
                $ivarnameN = $field->getIVarnameFromNameIrow($fieldname, $irow);
                // Ef_Log::log($ivarnameN,"nth variable for $fieldname and $irow");
                if (array_key_exists($ivarnameN, $memrow)) {
                    $_SESSION[$ivarnameN] = $memrow[$ivarnameN];
                } else {
                    $_SESSION[$ivarnameN] = '';                
                }               
            }        
        }
        return;
    }
    
    // Restore a memory row from session
    public function getMemRowFromSession ($irow=0) 
    {
        $this->initSessionRow($irow);
        $memrow = array();
        if (count($_SESSION) > 0) {
            foreach ($this->getFieldArrayObj() as $field) {
                $fieldname = $field->getName();
                $ivarnameN = $field->getIVarnameFromNameIrow($fieldname, $irow);
                if (array_key_exists($ivarnameN, $_SESSION)) {
                    $memrow[$ivarnameN] = $_SESSION[$ivarnameN];
                } else {
                    $memrow[$ivarnameN] = '';
                }
            }                
        }
        return $memrow;        
    }
    
    // Restore and editable row from session 
    public function getEditRowFromSession ($irow=0,$withlabel=true) 
    {    
        $this->initSessionRow($irow);
        $memrow = array();
        if (count($_SESSION) > 0) {
            foreach ($this->getFieldArrayObj() as $field) {
                $fieldname = $field->getName();
                $ivarnameN = $field->getIVarnameFromNameIrow($fieldname, $irow);
                $argtable = array('irow'=>$irow,'fieldvalues'=>array());
                if ($withlabel)
                    $argtable['withlabel'] = 1; 
                if (array_key_exists($ivarnameN, $_SESSION)) {                
                    $memrow[$ivarnameN] = $field->memToEditHtml($_SESSION[$ivarnameN],$argtable);
                } else {
                    $memrow[$ivarnameN] = $field->memToEditHtml('',$argtable);
                }
            }                
        }
        return $memrow;        
    }
    //
            
}

// A data read-only list associated to a sql query
class F_ReadList extends F_SqlReq 
{
    //  the concept of sql list request 
    //  1) a template text :
    //    select %fieldlist%
    //      from w_comments com 
    //        left join news new on com.news_id = news.id
    //     order by [fieldlist] asc/desc  -> not necessary can be generated
    //     %where% 
    //     %orderby%        
    //  2)  field formatting, from database to memory, and from memory to html,
    //      is a competence of the F_Field class
    //  3)  table names and aliases must be unique
    //  4)  name and alias must be given : 
    //      - tables are identified by their name
    //      - fields are linked to table by the table alias
    protected $template;
    protected $fieldlist;
    protected $fieldarray = array();
    protected $fieldarrayobj = array();
    protected $orderby;
    protected $where;
    protected $style;
    // foolproof, avoid building two times by different ways a F_ReadList
    protected $isbuilt; 
    // fieldstate... 
    protected $fieldstatearray = array();
    // listed values 
    protected $listedarray = array();

    /* identify lists with a code */    
    protected $code;
    /* list of lists */ 
    protected static $lists = array();
    // inserted fields
    protected $insertedfieldafter = array();
    
    public function __construct($argcode, $argtemplate, $argdbid='def') 
    {
        // foolproof check : a sql table must not be in progress        
        $tblalias = Ef_SqlTable::getTblAliasInProgress();
        if ($tblalias != '' ) {
            throw new Exception ("Can't build ".$argcode." while table ".$tblalias." is not finished");
        } 
        // argcode must be present
        if (!$argcode) {
            throw new Exception ("Can't build a F_ReadList or derivate with an empty code");
        }
        // name must be unique
        if (Ef_Readlist::findByCode($argcode)) {
            throw new Exception ("A F_ReadList or derivate with the code $argcode already exists");        
        }
        // argcode must be valid alphanumeric string 
        // 2016-01-28 do not admit - and _ anymore
        // $alsovalid = array('-', '_');
        // if(!ctype_alnum(str_replace($alsovalid, '', $argcode))) 
        if (!ctype_alnum($argcode)) {
            throw new Exception ("A F_ReadList or derivate must be named by an alphanumeric string : a-z A-Z 0-9");        
            // echo 'Your username is not properly formatted.';
        } 
    
        parent::__construct("", $argdbid);
        $this->template = $argtemplate;   
        self::$lists[] = $this;    
        // $this->code = count(self::$lists);
        $this->code = $argcode;          
    }   

    // Find by code
    public static function findByCode($searchcode)
    {
        foreach (self::$lists as $thislist) {
            if ($thislist->code == $searchcode) {
                return $thislist;
            }        
        }
        return null;        
    }
    
	// Compatibility with F_List
    public function getChangedArray() 
    {
        return array();     
    }
	// Compatibility with F_List
    public function getChangeStateFunction() 
    {
    	return null;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getFieldArray() 
    {
        return $this->fieldarray;       
    }
    public function getFieldStateArray() 
    {
        return $this->fieldstatearray;      
    }
    public function setFieldStateArray($fieldstatearray) 
    {
        $this->fieldstatearray = $fieldstatearray;
    }
    public function getListedArray() 
    {
        return $this->listedarray;      
    }
    public function setFieldList($argfieldlist) 
    {
        $this->fieldlist = $argfieldlist; 
    }
    public function getOrderBy() 
    {
        return $this->orderby; 
    }
    public function setOrderBy($argorderby) 
    {
        $this->orderby = $argorderby; 
    }
    public function getWhere()
    {
        return $this->where;
    }
    public function setWhere($argwhere) 
    {
        $this->where = $argwhere; 
    }
    public function getSqlQuery() 
    {
        return $this->sqlquery;
    }    
        
    // Managing field state
    public function setFieldState($argfieldname, $argstate) 
    {
        $this->fieldstatearray[$argfieldname] = $argstate;
    }
    
    public function getFieldState($argfieldname) 
    {
        if (isset($this->fieldstatearray[$argfieldname])) { 
            return $this->fieldstatearray[$argfieldname];
        } else {
            return '';
        }                    
    }
    
    public function setAllFieldState($argstate) 
    {
        foreach ($this->fieldarray as $fieldname) {  
            $this->fieldstatearray[$fieldname] = $argstate;
        }
    }
    
    // Insert a field after an existing one
    public function insertVirtualFieldAfter($argfieldname, $argfieldaftername = '') 
    {

        $field = Ef_Field::findByName ($argfieldname);
        if ($field === false) {
            throw new Exception ("insertVirtualFieldAfter $argfieldname / $argfieldaftername unknown field $argfieldname ");     
        }
        if ($argfieldaftername != '') {
            $field = Ef_Field::findByName ($argfieldaftername);
            if ($field === false) {
                throw new Exception ("insertVirtualFieldAfter $argfieldname / $argfieldaftername unknown field after $argfieldaftername ");      
            }
        }

        // there is an array of inserted fields after a field       
        $this->insertedfieldafter[$argfieldaftername][] = $argfieldname;
    
    } 
        
    // Insert a field after at the end of existing fields
    public function insertVirtualFieldAtEnd($argfieldname) 
    {
    	$argfieldaftername = end($this->fieldarray);
    	$this->insertVirtualFieldAfter($argfieldname, $argfieldaftername);
    
    }
    
    
    // Build select request, fieldarray and fieldarray obj from fieldarray
    public function buildFromFieldArray($fieldarray) 
    {
        // foolproof : this can be built only once
        if ($this->isbuilt == true) {
            throw new Exception ("buildFromFieldArray : the component is already built");
        }    
        $physicalfieldlist = '';
        
        foreach ($fieldarray as $fieldname) {
            $field = Ef_Field::findByName($fieldname);         
            if ($field === false) {
                throw new Exception ("buildFromFieldArray, field unknown : $fieldname");
            }
            $shortname = Ef_Field::getShortnameFromName($fieldname);
            $tbl = Ef_Field::getTblFromName($fieldname); 
            $table = Ef_SqlTable::findByAlias($tbl);
            if ($table === null) {
                throw new Exception ("buildFromFieldArray, table alias unknown in $fieldname");
            }
            // fieldarray, fieldarrayobj and fieldlist are complete
            $this->fieldarrayobj[] = $field;
            if ($this->fieldlist != '') 
                $this->fieldlist .= ', ';
            $this->fieldlist .= $fieldname;             
            // physicalfieldlist : only fields belonging to a physical table
            // if (is_a ($table, 'F_VirtualTable') === true) // 2015-05-12 
            if ($table instanceof F_VirtualTable === true) {
                $physicalfield = false;
            } else {
                $physicalfield = true;            
                if ($physicalfieldlist != '') 
                    $physicalfieldlist .= ', ';
                $physicalfieldlist .= $fieldname;
            }
                
        }
        $this->fieldarray = $fieldarray;
        $this->setAllFieldState('');  
        
        $this->sqlquery = str_replace ('%fieldlist%', $physicalfieldlist, $this->template);
        $this->sqlquery = str_replace ('%orderby%', $this->orderby, $this->sqlquery);
        $this->sqlquery = str_replace ('%where%', $this->where, $this->sqlquery);
        // Ef_Log::log($physicalfieldlist, 'physicalfieldlist');
        $this->isbuilt = true;
    }
    
    // Identify tables present in the sql template and add their fields to field array
    protected function buildFieldArrayFromTemplate() 
    {
        $fieldarray = array();
        foreach (Ef_SqlTable::getList() as $table) {
            $tabname = $table->getName();
            if (strpos ($this->template, $tabname) !== false) {
                $fieldarray = array_merge ($fieldarray, $table->getFieldArray());
            }
        }        
        return $fieldarray;
    }
    
    // Build select request : all-in-one function, builds fieldlist, fieldarray, fieldarrayobj
    public function buildSelectReq() 
    {
        // foolproof : this can be built only once
        if ($this->isbuilt == true) {
            throw new Exception ("buildSelectReq : the component is already built");
        }
        
        $fieldarray = $this->buildFieldArrayFromTemplate();
        $this->buildFromFieldArray($fieldarray);
        $this->isbuilt = true;
        // Ef_Log::log ($this->sqlquery);
    }
        
    // Build a table of listed value
    protected function buildListedArray($resultrows) 
    {
        $this->listedarray = array();        
        if (empty($resultrows)) {
            return; 
        }        
        $irow = 0;
        $valuearray = array();        
        
        foreach ($resultrows as $rowvalue) {
                    
            // insert empty values matching each virtual field            
            $maxpos = count($rowvalue) - 1;
            foreach ($this->fieldarrayobj as $icol=>$field) {

                // $field = Ef_Field::findByName ($fieldname);
                $fieldtbl = $field->getTbl();
                $fieldpos = $icol-1;
                // no table : this is a virtual field
                if (Ef_SqlTable::findByAlias($fieldtbl) === null) {             
            
                    for ($i = $maxpos; $i > $fieldpos; $i--) {
                        $rowvalue[$i+1] = $rowvalue[$i];
                    }
                    $rowvalue[$i+1] = '';
                }            
            }
            // Ef_Log::htmlEcho($rowvalue, '$rowvalue in buildListedArray after insert virtual');
            
            $icol = 0;
            foreach ($rowvalue as $fieldvalue) {
    
                $fieldname = $this->fieldarray[$icol];    
                // access to field definition
                $field = Ef_Field::findByName ($fieldname);
                    
                $editname = $field->getEditname(array('irow'=>$irow));
                $valuearray[$editname] = $field->sqlToMem($fieldvalue);
                $icol++;
            }    
            $irow++;
        }
        $this->listedarray = $valuearray;
    }                   
    
    // Count the rows returned by the query
    public function countRows($parms=array())
    {
        $statement = $this->prepare();
        $statement->execute();
        
        $resultrows = $this->getRows(PDO::FETCH_NUM);
        return count($resultrows);
    }
    
    // Render the rows returned by the query
    // The work is delegated to the ListView component
    public function getRenderRows($parms=array()) 
    {
        $statement = $this->prepare();
        $statement->execute();
        
        $resultrows = $this->getRows(PDO::FETCH_NUM);
        // Ef_Log::htmlEcho($resultrows, 'resultrows');
        
        $this->buildListedArray ($resultrows);
        
        
        $listview = Ef_Config::get('listview');
        if ($listview && class_exists ($listview)) {
            // Ef_Log::log ($listview,'listview in getRenderRows');
            $view = new $listview;
        } else { 
            $view = new Ef_ListView();
        }
        
        // return $view->render_old_old($resultrows, $this->fieldarray, $this->fieldstatearray, $parms);
        // return $view->render_old ($this->listedarray, $this->changedarray, $this->fieldarray, $this->fieldstatearray, $parms);
        return $view->render($this, $parms);                
    }
    
    // Extract a row from the listed array
    public function extractListedRow($irow) 
    {        
        $irowsuffix = '-'.$irow;
        $resultrow = array();
        
        foreach ($this->listedarray as $listedkey => $listedvalue) {
            $possuffix = strpos ($listedkey, $irowsuffix); 
            if ($possuffix !== false) {
                $resultrow[] = $listedvalue;
            }                       
        }
        // Ef_Log::log ($resultrow, 'resultrow in extract_LISTEDrow');
        if (count($resultrow) == 0) {
            return false;
        } else {    
            return $resultrow;
        }
    }

    // Extract a row from the listed array, with virtual fields added        
    public function extractCompletedRow($irow) 
    {
        // $row = Ef_List::extractListedRow ($listedarray, $irow);
        $row = $this->extractListedRow ($irow);
        if ($row === false) {
            return false;
        }
        $resultrow = array();
        
        
        $icol = 0;
        foreach ($this->fieldarray as $fieldname) {
            $resultrow[] = $row[$icol];
            
            $icol++;
            if (array_key_exists($fieldname, $this->insertedfieldafter)) // strict
		        if (is_array ($this->insertedfieldafter[$fieldname])) {
		            foreach (($this->insertedfieldafter[$fieldname]) as $virtfieldname) {  
                        // recycle value from kept  
                        $virtfield = Ef_Field::findByName($virtfieldname);
                        $editname = $virtfield->getEditname(array('irow'=>$irow));
                        // Ef_Log::log($editname,"editname in extractCompletedRow($irow)");
                        // Ef_Log::log($_SESSION['keep'][$this->code], '$_SESSION[kept][$this->code]');
                        if (array_key_exists('keep', $_SESSION) && array_key_exists($this->code,$_SESSION['keep'])) {
                            if (array_key_exists($editname, $_SESSION['keep'][$this->code]))
                                $resultrow[] = $_SESSION['keep'][$this->code][$editname];
                            else  
        		                $resultrow[] = '';  // @todo complete this
                        }
                        else
                            $resultrow[] = '';  // @todo complete this
		            }
		        }   
        }
        // Ef_Log::log ($resultrow, 'resultrow in extract_COMPLETEDrow');
        return $resultrow;
    }
    
    // Extract the fieldnames with added virtual fields
    public function extractCompletedFieldArray() 
    {
        $resultarray = array();
        foreach ($this->fieldarray as $fieldname) {
            $resultarray[] = $fieldname;
            if (array_key_exists($fieldname, $this->insertedfieldafter)) // strict
	            if (is_array ($this->insertedfieldafter[$fieldname])) {
	                foreach (($this->insertedfieldafter[$fieldname]) as $virtfieldname) {
	                    $resultarray[] = $virtfieldname;
	                }
	            }   
        }
        return $resultarray;
    
    }
}


// A data read-write list associated to a sql query
class F_List extends F_ReadList 
{
    // a list with update function  
    
    // update... 
    protected $updatetable;
    protected $updatetableobj;
    protected $updatequery;
    // insert
    protected $insertquery;
    
    // posted and changed values
    protected $postedarray = array();
    protected $changedarray = array();
    protected $postedrows = array();
    protected $changedrows = array();
    
    // list of control attributes
    protected $controlmethods = array();
    protected $errormsgs = array();   
    protected $errorgravities = array();
    protected $errorgravity;
	protected $errorfields = array();  

    // index to search update fields 
    protected $isearchinupdate;

    // function to change the state (or other things) ? individually per row
    protected $changestatefunction;
    
    public function __construct($argcode, $argtemplate, $argdbid='def') 
    {
        parent::__construct($argcode, $argtemplate, $argdbid);   
    }   

    public function getPostedArray() 
    {
        return $this->postedarray;     
    }
    
    public function getPostedMaxRow()
    {
        // changed 2016-04-11 - bad algo
        // get the suffix of the last key of the postedarray
        end($this->postedarray);        // move the internal pointer to the end of the array
        $key = key($this->postedarray); // get the matching key
        // Ef_Log::log($key, 'key in getPostedMaxRow');
        $hyphenpos = strrpos($key, '-'); // find the last hyphen
        // Ef_Log::log($hyphenpos,'hyphenpos in getPostedMaxRow');
		if ($hyphenpos > 1) {
			$maxrow = substr($key, $hyphenpos+1);
            // Ef_Log::log($maxrow,'maxrow in getPostedMaxRow');
            return $maxrow;
        } else {
            return false;
        }        
    }
    
    public function getPostedValue($key) 
    {
        return $this->postedarray[$key];
    }
    
    public function getFieldMemValue($field, $row)
    {
        if (is_subclass_of($field, 'F_Field')) {
            $fieldkeyrow = $field->getPostnameFromNameIrow($field->getName(),$row);
            // Ef_Log::log($fieldkeyrow,'fieldkeyrow in getFieldMemValue');
            // Ef_Log::log($this->postedarray,'$this->postedarray in getFieldMemValue');
            return $this->getPostedValue($fieldkeyrow);            
        } else {
            return '';
        } 
    }

    public function getChangedArray() 
    {
        return $this->changedarray;     
    }
    
    public function getErrorMsgs()
    {
        return $this->errormsgs;
    }

    // build a table in of listed value : specialized - keep a table in session too
    protected function buildListedArray($resultrows) 
    {            
        $_SESSION['listed'][$this->code] = array();
        $this->listedarray = array();        
        if (empty($resultrows)) {
            return; 
        }
        
        $irow = 0;
        $valuearray = array();        
        
        foreach ($resultrows as $rowvalue) {
        
            // insert empty values matching each virtual field            
            $maxpos = count($rowvalue) - 1;
            foreach ($this->fieldarrayobj as $icol=>$field) {

                // $field = Ef_Field::findByName ($fieldname);
                $fieldtbl = $field->getTbl();
                $fieldpos = $icol-1;
                // no table : this is a virtual field
                if (Ef_SqlTable::findByAlias($fieldtbl) === null) {             
            
                    for ($i = $maxpos; $i > $fieldpos; $i--) {
                        $rowvalue[$i+1] = $rowvalue[$i];
                    }
                    $rowvalue[$i+1] = '';
                }            
            }
            // Ef_Log::htmlEcho($rowvalue, '$rowvalue in buildListedArray after insert virtual');
            
            $icol = 0;
            foreach ($rowvalue as $fieldvalue) {
    
                $fieldname = $this->fieldarray[$icol];    
    
                // access to field definition
                $field = Ef_Field::findByName ($fieldname);
                    
                $editname = $field->getEditname(array('irow'=>$irow));
                $valuearray[$editname] = $field->sqlToMem($fieldvalue);
                $icol++;
            }
            $irow++;
        }
        $_SESSION['listed'][$this->code] = $valuearray;
        $this->listedarray = $valuearray;
        // Ef_Log::echoTitle ('Echoing listedarray in buildListedArray');
        // Ef_Log::htmlDump($this->listedarray,'this->listedarray');
        // Ef_Log::log ($this->listedarray,'listedarray in buildListedArray');
    }                   
    
    
    // @HTMLTAG : here html tag <br> present
    public function getErrorText($before='', $after="<br>\n")
    {
        $resulttext = '';
        foreach ($this->errormsgs as $msgerr) {
        	if ($msgerr != '') {
	            $resulttext .= $before;
	            $resulttext .= Ef_Lang::get($msgerr);
	            $resulttext .= $after;
			}        
        }
        return $resulttext;
    }

    public function setChangeStateFunction($changestatefunction) 
    {
        if (!is_callable ($changestatefunction)) {
            throw new Exception ("cannot set changestatefunction to $changestatefunction : not a callable method");
            return;
        }
        $this->changestatefunction = $changestatefunction;
    }
    public function getChangeStateFunction()  
    {
        return $this->changestatefunction;
    }

    public function getUpdateQuery() 
    {
        return $this->updatequery;
    }
    
    public function setUpdateTable($argtablename) 
    {
        $table = Ef_SqlTable::findByName($argtablename);
        
        if (!isset($table)) {
            throw new Exception ("update table unknown : $argtablename ");
            return;
        }
        $this->updatetable = $argtablename;
        $this->updatetableobj = $table;   
    }
    
    // 2016-03-24
    public function setUpdateTableByAlias($argtablealias)
    {
        $table = Ef_SqlTable::findByAlias($argtablealias);
        if (!isset($table)) {
            throw new Exception ("update table unknown : $argtablealias ");
            return;
        }
        $this->updatetable = $table->getName();
        $this->updatetableobj = $table;            
    }
            
    // build sql update request
    // 2014-08-30 : due to sqlite, no table alias in update
    public function buildUpdateReq() 
    {
        // refuse to build update request if no update table associated with this list
        if (!isset($this->updatetableobj)) {        
            // return;
            throw new Exception("Cannot build update request : no update table defined");
        }

        // refuse to build update request if the update table has no field defined                 
        if (count($this->updatetableobj->getFieldArray()) == 0) {
            throw new Exception("Cannot build update request : table fieldarray is not built");        
        }
        
        $updatetableobj = $this->updatetableobj;
        $tablename = $updatetableobj->getName();
        
        $updatesent = "update $tablename  set ";
        
        // generate field list
        foreach ($this->fieldarrayobj as $field) {
            // todo read field state
            // todo make a complete update statement
            $fieldname = $field->getName();
            // Ef_Log::log($fieldname, "fieldname inside F_List $this->code");
            
            // 2015-09-21 : fieldstatearray must be 'edit' or 'hidden' or 'readonly'
            $fieldstate = $this->fieldstatearray[$fieldname];
            // Ef_Log::log($fieldstate, "fieldstate for $fieldname");
            if ($fieldstate == 'edit' || $fieldstate == 'hidden' || $fieldstate == 'readonly') {
            
                if ($field->getTbl() == $updatetableobj->getAlias()) {
                     $updatesent .= "\n" . $field->getShortName();  
                     $updatesent .= ('= '. '%' .  $field->getEditname() . '-value'. '%' );
                     $updatesent .= ',';
                }                            
            } 
        }
        $updatesent = substr ($updatesent, 0, strlen($updatesent) -1);
        
        $tablekeys = $updatetableobj->getFieldKeyArray();

        // generate field key list
        $updatesent .= "\n" . " where ";        
        $firstterm = true;
        foreach ($tablekeys as $keyfieldname) {
            $field = Ef_Field::findByName ($keyfieldname);
            if ($firstterm === false)
                $updatesent .= "\n" . " and ";      
            $updatesent .= "\n" . $field->getShortName();  
            $updatesent .= ' = ' . '%'. $field->getEditname() . '-value'. '%'; 
            $firstterm = false;
        }
        $updatesent .= "\n";
        
        // Ef_Log::log ($updatesent, 'updatesent in buildUpdateReq'); 
        $this->updatequery = $updatesent;        
    }
    
    // return the next update variable in valuename form :example %efpa-title-value%  for var "efpa.title"
    public function searchNextUpdateVar() 
    {
        if (!$this->isearchinupdate)
            $this->isearchinupdate = 0;
            
        // Ef_Log::log ($this->isearchinupdate,'this->isearchinupdate');            
        // Ef_Log::log ($this->fieldarray,'this->fieldarray');
        
        while (true) {
            if (array_key_exists($this->isearchinupdate, $this->fieldarray)=== false ) {
                break;
            }
            $fieldname = $this->fieldarray[$this->isearchinupdate];
            $this->isearchinupdate = ($this->isearchinupdate)+1;                                     
            $state = $this->getFieldState($fieldname); 
            // 2015-03-26 add hidden state
            // 2015-09-21 add readonly state
            if ($state == 'edit' || $state == 'hidden' || $state == 'readonly' ) {
                $valuename = Ef_Field::getValuenameFromName ($fieldname);
                // Ef_Log::log($valuename,'searchNextUpdateVar returns this valuename');
                
                return $valuename;
            }
            
        }
        return false;    
    }
    
    public function resetNextUpdateVar() 
    {
        $this->isearchinupdate = 0;        
    }
        
    
    protected function resetChangedArray() 
    {    
        $this->postedrows = array();
        $this->changedrows = array();
        $this->changedarray = array();
    }
    
    /*
        buildChangedArray
            table $this->listedarray :  listed values
            table $this->postedrows :   posted rows 
            table $this->changedrows :  rows posted and changed
            table $this->changedarray : changed values
        -   Init posted rows,  changed rows and changed array
        -   Copy listedarray from session ('listed')
        -   First loop for posted rows          -- removed 2016-02-03
            -   For each value of $this->listedarray
                get $row
                if POST value exists
                    then $this->postedrows[$row] is true
        -   Second loop for changed values
            -   Foreach value of  $this->listedarray
                if $this->postedrows[$row] is true
                and if $_POST[$key] is different from  $this->listedarray
                then $this->changedrows[$row] is true
                and $this->changedarray[$key] is true               
    */      
    protected function buildChangedArray() 
    {
        
        $this->resetChangedArray();
        
        $this->listedarray = $_SESSION['listed'][$this->code];

        /* 2016-02-03 - sometimes changed values are not present in POST (checkbox set to empty)
            so looking at _POST is not the good way to see if row is posted
        */
        // Ef_Log::log ($this->postedrows,'$this->postedrows in buildChangedArray');        
        // Ef_Log::log ($this->updatequery,'$this->updatequery in buildChangedArray');
        
        foreach ($this->listedarray as $listedkey => $listedvalue) {                

            $row = Ef_Field::getRowFromEditName ($listedkey);

            $fieldname = Ef_Field::getNameFromEditname ($listedkey);
            // Ef_Log::log($listedvalue, "listedvalue in buildChangedArray for fieldname $fieldname, listedkey $listedkey ");
            
            $field = Ef_Field::findByName($fieldname);          

            // only field in edit state can change value (not hidden or readonly)
            if ($this->getFieldState($fieldname) != 'edit') {
                continue;
            } 

            /* 2016-02-03 sometimes changed values are not present in POST (checkbox set to empty) 
            */
            // 2016-02-03 new version : posted value are looked at, even if they are empty
            $postedvalue = $field->postHtmlToMem(Ef_Util::getArrayValue($_POST,$listedkey));
            // Ef_Log::log($postedvalue, "postedvalue in BuildChangedArray for $listedkey");
            $this->postedarray[$listedkey] = $postedvalue;
            if ($postedvalue != $listedvalue ) {
                $this->postedrows[$row] = true;
                $this->changedrows[$row] = true;
                $this->changedarray[$listedkey] = $postedvalue; 
            }              
        }     
        // Ef_Log::log ($this->postedrows,'$this->postedrows');
        // Ef_Log::log ($this->changedarray,'$this->changedarray'); 
        return $this->changedarray; // 2016-12-15   
    }               
        
        
    protected function rowIsChanged($row) 
    {
        if (array_key_exists($row, $this->changedrows)) {   
            return $this->changedrows[$row];    
        } else {
            return false;
        }               
    }       

    protected function rowIsPosted($row) 
    { 
        if (array_key_exists($row, $this->postedrows)) {
            return $this->postedrows[$row];
        } else {
            return false;
        }   
    }       
    
    // Register a control method of a class : add it to a table     
    public function registerControl( $classmethodarray ) 
    {
    
        if (is_array ($classmethodarray) ) {
            if (  is_callable($classmethodarray) ) {
                $this->controlmethods[] = $classmethodarray;                
            }  else {
                throw new Exception ("registerControl not a valid class / method array : $classmethodarray");              
            }
        }    
    }
    
    // Process control for this list : execute each control
    // make controls even if data not changed             
    public function processControl() 
    {

        $this->buildChangedArray();
        $this->errormsgs = array();        
        $this->errorfields = array();
        $this->errorgravities = array();
        $this->errorgravity = 0;
        $retvalue = true;
        
        // Ef_Log::log($this->postedrows, '$this->postedrows in processControl');
        foreach ($this->postedrows as $irow => $posted) {
        
            // build old row and new row for controls
            $oldrow = array();
            $newrow = array();          
            foreach ($this->fieldarray as $fieldnum => $fieldname ) { 

                // access to field definition
                $field = Ef_Field::findByName ($fieldname);                
                $editname = $field->getEditname(array('irow'=>$irow));
                // $valuearray[$editname] = $field->sqlToMem($fieldvalue);
                                
                // build a field name compatible with php usage (com.news_id -> com_news_id)
                $fieldvarname = Ef_Field::getVarnameFromName ($fieldname);
                    
                $oldrow[$fieldvarname] = $this->listedarray[$editname];
                if (array_key_exists ($editname, $this->changedarray ) ) {
                    $newrow[$fieldvarname] = $this->changedarray[$editname]; 
                } else {
                    $newrow[$fieldvarname] = $oldrow[$fieldvarname]; 
                }           
            }       
                    
            // call each control
            // gravity resulting from each control is kept in a table errorgravities
            // maximum gravity is kept in an attribute errorgravity
            // error messages are kept in a table errormsgs
            foreach ($this->controlmethods as $classmethodarray) {

                // Ef_Log::log($this->controlmethods, 'controlmethods in processControl');
                $ctlclassname = $classmethodarray[0];
                $ctlmethname = $classmethodarray[1];
                
                $ctlinstance = new $ctlclassname;
            
                $retcontrol = call_user_func_array (array($ctlinstance, $ctlmethname), array($oldrow, $newrow));                                
                if (is_callable(array ($ctlclassname, 'getGravityErr')) ) {
                    $gravity  = call_user_func_array ( array ($ctlinstance, 'getGravityErr'), array() );
                    $this->errorgravities[] = $gravity;
                    if ($gravity > $this->errorgravity) {
                        $this->errorgravity = $gravity;
                    }                                       
                }
                if (is_callable(array ($ctlclassname, 'getMsgErr')) ) {
                    $msgerr  = call_user_func_array ( array ($ctlinstance, 'getMsgErr'), array() );
                    // if ($msgerr) {
                    //     Ef_Log::htmlEcho ($msgerr, 'msgerr in processControl');
                    // }
                    $this->errormsgs[] = $msgerr;                   
                }
                if (is_callable(array ($ctlclassname, 'getFieldNames')) ) {
                    $fieldnames  = call_user_func_array ( array ($ctlinstance, 'getFieldNames'), array() );
                    if (is_array($fieldnames) && count($fieldnames) > 0) {
                        // Ef_Log::htmlEcho ($fieldnames, 'fieldnames in processControl');
                        // $this->errorfields = array_merge($this->errorfields, $fieldnames);
                        $this->errorfields[$irow] = $fieldnames;
                    }
                }
                // Ef_Log::log ($this->errorfields, 'errorfields in ProcessControl');                  
                
                                               
                if ($retcontrol == false) {
                    $retvalue = false;
                }               
            }
        }    
        return $retvalue;    
    }
    
    // See if a given field is in error for a given row
    public function isFieldInError($fieldname,$irow) 
    {
        if (!isset($this->errorfields[$irow])) {
            return false;
        } 
        if (array_search ($fieldname, $this->errorfields[$irow]) === false) {
            return false;    
        } else {
            return true;
        }             
    }

    // Process the update request
    // for each posted row $irow
    //    for each field of the update request
    //       change the post value into database value
    //       substitute this value in the update request
    //    then process the update request
    public function processUpdate() 
    {            
        $this->buildChangedArray(); // TODO : may already been done in processControl

		// SESSIONLIST : set update values in memory                                                                                     
        
        foreach ($this->postedrows as $irow => $posted) {         
            if (!$this->rowIsChanged($irow)) {
                continue;
            }
            
            $updatestring = $this->updatequery;
        
            $rowexists = false;
            $this->resetNextUpdateVar();            
            for ($ifield = 0; true ; $ifield ++) {
            
                $nextvar =  $this->searchNextUpdateVar();
                if ($nextvar == false)
                    break;
                    
                $postkey0 = str_replace('value%', $irow, $nextvar);
                $postkey  = str_replace('%', '', $postkey0);
                // Ef_Log::htmlEcho($postkey,'postkey');
                
                $fieldname = Ef_Field::getNameFromEditname($postkey);
                // Ef_Log::htmlEcho ($fieldname, 'fieldname');
                $field = Ef_Field::findByName($fieldname);
                  
                // changed 2016-01-29 - setting _POST[$postkey] to ''
                if (isset($_POST[$postkey])) {
                    $rowexists = true;
                } else {
                    $_POST[$postkey] = '';                
                }     
                $value = $field->postHtmlToMem($_POST[$postkey]);
                // Ef_Log::htmlEcho($value,'value in processUpdate');
                $parms['dbtype'] = Ef_Config::get('f_db_dbtype',$this->dbid); 
                // Ef_Log::log ($parms['dbtype'], '$parms['dbtype'] in Processupdate');
                $dbvalue = $field->memToSql($value,$parms);
                $updatestring = str_replace($nextvar, $dbvalue, $updatestring);                                    
            }

            if ($rowexists == false)
                break;
            
            // Ef_Log::log ($updatestring, 'updatestring in processUpdate ');
            $sqlreq = new Ef_SqlReq($updatestring, $this->dbid);
            $sqlreq->execute();            
        }                  
        $this->resetChangedArray();                    
    }

    // Keep all post values in session for a given field   
    public function keepPostInSession($field) 
    {
        $editname = $field->getEditname();
        if (!isset($_SESSION['keep']))
            $_SESSION['keep'] = array();
        if (!isset($_SESSION['keep'][$this->code]))
            $_SESSION['keep'][$this->code] = array();
        // unset previous values      
        foreach ($_SESSION['keep'][$this->code] as $key=>$value) {
            if (strpos($key, $editname) !== false) {
                unset($_SESSION['keep'][$this->code][$key]); 
            }
        }            
        // set values 
        if (isset($_POST)) {     
            foreach ($_POST as $key => $value) {
                if (strpos($key, $editname) !== false) {
                    $_SESSION['keep'][$this->code][$key] = $value;                
                }
            }
        }
    }
        
    // Search a value in changed array
    public static function extractChangedValue($fieldname, $changedarray, $irow) 
    {
    
        $field = Ef_Field::findByName ($fieldname);                
        $editname = $field->getEditname(array('irow'=>$irow));
    
        // Ef_Log::log ($editname, "searching for $fieldname in $irow");
        
        if (array_key_exists($editname, $changedarray)) {
            return $changedarray[$editname];
        } else {
            return false;
        }
    }
    
}

// A data read-write list associated to some session data
class F_SessionList extends F_List 
{

    protected $numberrows;  
    
    public function __construct($argcode) 
    {
        parent::__construct($argcode, '', '');
    }   

    public function buildUpdateReq () 
    {
        throw new Exception ("cannot call buildUpdateReq on F_SessionList ");        
    }    

    // Ensure that the row 0 of the session 'listed' table is set 
    public function initListedContent() 
    {
        // if ( ! is_array($_SESSION['listed'][$this->code]) ) // strict
        if ( ! isset ($_SESSION['listed'])) {
			$_SESSION['listed'] = array();        	
        }
        if ( ! is_array(Ef_Util::getArrayValue($_SESSION['listed'], $this->code))) {
            $_SESSION['listed'][$this->code] = array();            
        }
        if (count($_SESSION['listed'][$this->code]) == 0) {
            foreach ($this->fieldarray as $fieldname) {
                $postname = Ef_Field::getPostnameFromNameIrow ($fieldname, 0);
                $_SESSION['listed'][$this->code][$postname] = '';            
            }
        } 
    }

    // Set the listed content from an indexed table for a given row    
    public function setListedContent($irow, $arrayVal) 
    {
        $ival = 0;
        foreach ($this->fieldarray as $fieldname) {
            $postname = Ef_Field::getPostnameFromNameIrow ($fieldname, $irow);
            $_SESSION['listed'][$this->code][$postname] = $arrayVal[$ival];
            $ival++;            
        }            
    }
    
    // Reset the listed content for a given row
    public function resetListedContentRow($irow) 
    {
        foreach ($this->fieldarray as $fieldname) {
            $postname = Ef_Field::getPostnameFromNameIrow ($fieldname, $irow);
            unset($_SESSION['listed'][$this->code][$postname]);
        }                
    }
        
    // Render the rows returned by the query
    // The work is delegated to the ListView component
    public function getRenderRows($parms=array()) 
    {            
        $this->initListedContent();
        
        $this->listedarray = $_SESSION['listed'][$this->code];

        // Ef_Log::log($this->listedarray, 'listedarray in getRenderRows');
        
        // note : code below is common with F_ReadList            
        $listview = Ef_Config::get('listview');
        if ($listview && class_exists ($listview)) {
            // Ef_Log::log ($listview,'listview in getRenderRows');
            $view = new $listview;
        } else { 
            $view = new Ef_ListView();
        }
        
        return $view->render ($this, $parms);      // uses listed/changed/field/fieldstatearray
    }
    
    // Process the update request
    // for each posted row $irow
    //    for each field of the update request
    //       change the post value into memory value and keep it in session
    public function processUpdate() 
    {            
        $this->buildChangedArray(); // TODO : may already been done in processControl

        foreach ($this->postedrows as $irow => $posted) {         
            if (!$this->rowIsChanged($irow)) {
                continue;
            }
                    
            $rowexists = false;
            $this->resetNextUpdateVar();            
            for ($ifield = 0; true ; $ifield ++) {
            
                $nextvar =  $this->searchNextUpdateVar();
                if ($nextvar == false)
                    break;
                
                $postkey0 = str_replace('value%', $irow, $nextvar);
                $postkey  = str_replace('%', '', $postkey0);
                // Ef_Log::htmlEcho($postkey,'postkey');
                
                $fieldname = Ef_Field::getNameFromEditname($postkey);
                // Ef_Log::htmlEcho ($fieldname, 'fieldname');
                $field = Ef_Field::findByName($fieldname);
                
                if (array_key_exists($postkey, $_POST)) {
                    $rowexists = true;
                
                    $value = $field->postHtmlToMem($_POST[$postkey]);

                    // directly update the listed session array 
                    $_SESSION['listed'][$this->code][$postkey] = $value;
                }   
            }

            if ($rowexists == false)
                break;            
        }
                  
        $this->resetChangedArray();                    
    }

    // Keep all post values in session for a virtual field   
    public function keepPostInListed($field) {
        $editname = $field->getEditname();
        foreach ($_POST as $key => $value) {
            if (strpos($key, $editname) !== false) {
                $_SESSION['listed'][$this->code][$key] = $value;                
            }
        }
    }
        
}

// ?>