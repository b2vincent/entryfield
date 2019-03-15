<?php
// Copyright Vincent Wartelle & Oklin.com 2016-2019
// EntryField framework - Open sourced under MIT license  

if (!function_exists("sqlite_escape_string")) {
    function sqlite_escape_string ( $string ) {
        return SQLite3::escapeString($string);
    }
}


// Version management
class F_Version
{
    // see http://semver.org   - $major.$minor.$patch
    public static function numvers() 
    {      
        return '1.24.1'; // 2019-13-03
    }   
        
}

// Config management : this is basically a global array 
class F_Config 
{	
	public static $vars;
	
	public static function set($key, $var, $keypref="def") 
    {
        if (!isset($var)) {
            throw new Exception ("missing \$var argument when setting $key");
        }
		self::$vars[$keypref][$key] =$var;
	}
	public static function get($key, $keypref="def") 
    {
        if (isset(self::$vars[$keypref][$key]))
            return self::$vars[$keypref][$key];
        else
            return '';	
	}
	public static function getVars($keypref="def") 
    {
		return self::$vars[$keypref];
	}
}


// Utilities 
class F_Util 
{
    // when uncertain that the key exists in an array, use this rather than $array[$key]
    // (unless will produce a notice level error )
    public static function getArrayValue( $array, $key ) {
    	// 2017-02-22 - prevent usage on non array
        // if (array_key_exists($key, $array)) {
    	// 	return $array[$key];
    	// }
    	if (is_array($array) && array_key_exists($key, $array)) {
    		return $array[$key];
    	}
        return false; 
    }
}

// Log information to a file
class F_Log 
{

    protected static function logVarToFilePointer($myvar, $myvarname, $fp)
    {
		if ($myvarname != '') {
			fputs ($fp, $myvarname.":\r\n");
		}
		$varvalue = print_r($myvar, true);
		$varvalue = str_replace ("\n","\r\n", $varvalue);
		fputs ($fp, $varvalue."\r\n");
		fclose ($fp);                        
    }
    
    // write in a log if a given keyword is set
    public static function conditionLog($myvar, $myvarname='', $keyword, $keywordfile)
    {
        if (Ef_Config::get($keyword) != 'do') {
            return;
        }
        if (! Ef_Config::get($keywordfile)) {
            throw new Exception ("missing $keywordfile in Config");
        }
        $filename = Ef_Config::get($keywordfile);
        $fp = fopen($filename,"a+");
        Ef_Log::logVarToFilePointer ($myvar, $myvarname, $fp); 
    }

	// log a var or an array to a file
	public static function log($myvar, $myvarname='', $logword='') 
    {
        // do nothing if logword is set and is not found in logwords - 2017-02
        if ($logword != '') {
            $logwords = Ef_Config::get('logwords');
            // Ef_Log::log($logwords,'logwords');
            if ( !is_array($logwords) || array_search ($logword , $logwords)===false) {
                return;
            }            
        }

		$F_Config = Ef_Config::getVars();
		
		extract($F_Config);
		if (!isset($f_log_debug) || !$f_log_debug) {  // 2019-03-13
			return;
		}
        if (!isset($f_log_debugfile) || !$f_log_debugfile) { 
            throw new Exception ("missing f_log_debugfile in Config");
        }
		$fp = fopen($f_log_debugfile,"a+");
	        
        Ef_Log::logVarToFilePointer ($myvar, $myvarname, $fp);
    }
	
	// do an intelligent html echoing
	public static function htmlEcho($myvar, $myvarname='') 
    {
		if ($myvarname != '') {
			echo("\r\n<br>htmlEcho var: $myvarname<br>\r\n");
		}
		$htmlvalue = print_r($myvar, true);
		$htmlvalue = str_replace ("\n","<br>\r\n", $htmlvalue);
		echo "$htmlvalue <br>\r\n";
	}

	// nice echoing in html page from var_dump comment in php.net
	public static function htmlDump($myvar, $myvarname='', $height="20em") 
    {
		echo "<pre style=\"border: 1px solid #000; height: {$height}; overflow: auto; margin: 0.5em;\">\n";
        echo $myvarname; var_export($myvar);
		echo "</pre>\n";
	}	
	
	// echoing a subtitle in html page
	public static function echoTitle ($value, $style='h4', $withhr=true) 
    {
	    if ($withhr)
	        echo("<hr>\n");
	    echo("<".$style.">");
	    echo $value;
	    echo("</".$style.">\n");
	}

}

// Session management : wrapper around PHP session management
class F_Session 
{
    // identify if the session is started
    // differs between php version 5.4 and before
    public static function isStarted () 
    {        
        if (phpversion() >= '5.4') {
            if (session_status() == PHP_SESSION_NONE) {
                return false;
            } else {
                return true;
            }    
        } else {
            if (session_id() == '') {
                return false;
            } else {
                return true;
            }    
        }        
    }

    public static function getSessionId() 
    {
        return session_id();
    }

    public static function start ($params = array()) 
    {

        ini_set('session.use_trans_sid','0'); // changed 2016-01-29   
        ini_set('session.auto_start','0');
        ini_set('session.use_cookies','1');     // cookie or not cookie ?
        ini_set('session.use_only_cookies','0');        

        // session name
        if (Ef_Util::getArrayValue($params,'sessname') === false) 
            $sessname = 'f_sess';
        else
            $sessname = $params['sessname'];

        Ef_Config::set('sessname',$sessname);    

        ini_set('session.name',$sessname);
        
        // session max lifetime
		if (Ef_Util::getArrayValue($params,'sesslifetime') === false) 
            $sesslifetime = '1440';
        else
            $sesslifetime = $params['sesslifetime'];
        ini_set('session.gc_maxlifetime',$sesslifetime);

        // session cache expiration (cookie)         
        // if ($params['sessioncacheexpire'] == '') strict 
        if (Ef_Util::getArrayValue($params, 'sessioncacheexpire') === false)
            $sessioncacheexpire = '180';
        else            
            $sessioncacheexpire = $params['sessioncacheexpire'];
        ini_set('session.cache_expire', $sessioncacheexpire);
        
        session_start();
        
        // reinit session data
        if (Ef_Util::getArrayValue ($_GET, 'sessinit') === 'true') {
            // Ef_Log::log($_GET,'_GET in Ef_Session::start');
            if (Ef_Session::isStarted() === true)
            {
                Ef_Session::delete();
            }
            unset($_GET['sessinit']);
            
            session_start();
        }        
    }
    
    public static function delete() 
    {
        session_regenerate_id(true);
        session_destroy();
    }

    public static function clearContent()
    {
        session_unset();
    }

    public static function checkStarted () 
    {
        if (Ef_Session::isStarted() == false) {
            throw new Exception ("ERROR No session is started");        
        }
    }    
    
    public static function appendMessage($context, $msg, $separator='<br/>') 
    {
        Ef_Session::checkStarted();
        $key =  $context.'_message';
        Ef_Session::setVal($key, Ef_Session::getVal($key).$msg.$separator);    
    }
    
    public static function getMessages($context)
    {
        Ef_Session::checkStarted();
        $key =  $context.'_message';
        return Ef_Session::getVal($key); 
    }
    
    public static function clearMessages($context)
    {
        Ef_Session::checkStarted();
        $key =  $context.'_message';
        return Ef_Session::delKey($key);            
    }
    
    public static function setVal ($key, $value) 
    {
        Ef_Session::checkStarted();
        // 2017-02-24 - $_SESSION[$key] = serialize($value);
        $_SESSION[$key] = $value;
        return true;
    }

    public static function getVal ($key) 
    {
        Ef_Session::checkStarted();
        if (self::checkKey($key)) {
            // 2017-02-24 - return unserialize($_SESSION[$key]);
            return $_SESSION[$key];
        } else {
            return false;
        }
    }

    public static function checkKey ($key) 
    {
        Ef_Session::checkStarted();
        return isset($_SESSION[$key]);
    }

    public static function delKey ($key) 
    {    
        Ef_Session::checkStarted();
        if (self::checkKey($key)) {
            unset($_SESSION[$key]);
            return !self::checkKey($key);
        } else {
            return false;
        }
    }    
}


// Database connection management
class F_Db 
{

	// protected static $dbinstance;
	protected static $instances = array();
	protected $pdolink;
    protected $dbType;
	
	// open sql connexion
	protected function sqlOpen ($dbid='def')  
    {
		$dsn = '';
		$f_db_user = '';
		$f_db_pass = '';
		
		$F_Config = Ef_Config::getVars($dbid);
		extract($F_Config);		
		// Ef_Log::log($F_Config,'F_Config');

		if (!isset($f_db_dbtype))
            throw new Exception ("sqlOpen : f_db_dbtype not set in config");
			// $f_db_dbtype = 'mysql';

        $this->dbType = $f_db_dbtype;
        
		
		if ($f_db_dbtype == 'mysql') {
			$dsn = "mysql:dbname=$f_db_database;host=$f_db_host";
    		// ability to use another database port
    		if (isset($f_db_port))    // 2018-12-17
    			$dsn .= ";port=$f_db_port";
            // Ef_Log::log($dsn,'dsn in sqlOpen for mysql');
    		try {
    			$this->pdolink = new PDO($dsn, $f_db_user, $f_db_pass);
                $this->pdolink->query('SET SESSION sql_mode = "PIPES_AS_CONCAT"');
    		} catch (Exception $e) {
    			throw new Exception ('Connection failed: ' . $e->getMessage());
    		}    
            // $serververs = $this->pdolink->getAttribute(PDO::ATTR_SERVER_VERSION);
            // Ef_Log::log($serververs, 'server version : mysql');    
    		// $this->pdolink->query('set names latin1'); // Commented 2015-08-11
            if (Ef_Config::get('f_log_debug')) {
                $this->pdolink->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } 
    		return $this->pdolink;
        }
        
		if ($f_db_dbtype == 'sqlite') { 		    
			$dsn = 'sqlite:'.Ef_Config::get('f_sqlitedb_path').'/'.$f_db_database.'.sqlite';
            // Ef_Log::log($dsn,'dsn in sqlOpen for sqlite');	
    		try {
    			$this->pdolink = new PDO($dsn, $f_db_user, $f_db_pass);
    		} catch (Exception $e) {
    			throw new Exception ('Connection failed: ' . $e->getMessage());
    		}    
            // $serververs = $this->pdolink->getAttribute(PDO::ATTR_SERVER_VERSION);
            // Ef_Log::log($serververs, 'server version : sqlite');    
    		// $this->pdolink->query('set names latin1'); // Commented 2015-08-11
            if (Ef_Config::get('f_log_debug')) {
                $this->pdolink->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } 
    		return $this->pdolink;		
        }			
        
        if ($f_db_dbtype == 'sqlserver') {
            // $dsn="odbc:Driver={SQL Native Client};Server=$f_db_host;Database=$f_db_database;";
           // $dsn = "odbc:Driver={SQL Server Native Client 10.0};Server=$f_db_host;Database=$f_db_database;Uid=$f_db_user;Pwd=$f_db_pass;";
           $dsn = "odbc:Driver={SQL Server};Server=$f_db_host;Database=$f_db_database;Uid=$f_db_user;Pwd=$f_db_pass;";
   		    if (isset($f_db_port))
    			$dsn .= ";port=$f_db_port";
            // Ef_Log::log($dsn,'dsn in sqlOpen for mysql');
            // Ef_Log::log($dsn,'dsn in sqlOpen for sqlserver');
    		try {
    			$this->pdolink = new PDO($dsn);
    		} catch (Exception $e) {
    			throw new Exception ('Connection failed: ' . $e->getMessage());
    		}
            // $drivername = $this->pdolink->getAttribute(PDO::ATTR_DRIVER_NAME);
            // Ef_Log::log($drivername, 'driver name : sqlserver');    
    		// $this->pdolink->query('set names latin1'); // Commented 2015-08-11
            if (Ef_Config::get('f_log_debug')) {
                $this->pdolink->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } 
    		return $this->pdolink;                
        }			
	}

	protected function sqlClose() 
    {
        unset($this->pdolink);
	}
	
	protected function getPdoLink() 
    {
		return $this->pdolink;
	}

    protected function getDbType() 
    {
        return $this->dbType;
    }
	
	protected static function instance($dbid='def') 
    {
	   if ( !array_key_exists ($dbid, self::$instances) ) {	
	        self::$instances[$dbid] = new Ef_Db();   
	   }
	   return self::$instances[$dbid];
	}
    
    public static function getInstances()
    {
        return self::$instances;
    }

	public static function dbOpen($dbid='def') 
    {
		$dbolink = Ef_Db::instance($dbid)->sqlOpen($dbid);
		return $dbolink;
	}

	public static function dbClose($dbid='def') 
    {
		Ef_Db::instance($dbid)->sqlClose();
	}
	
	public static function dbLink($dbid='def') 
    {
		return Ef_Db::instance($dbid)->getPdoLink();
	}

	public static function dbType($dbid='def') 
    {
		return Ef_Db::instance($dbid)->getDbType();
	}
    
    public static function dbCloseAll() 
    {
        foreach (Ef_Db::getInstances() as $instance) {
            $instance->sqlClose();
        }        
    }	
}

// Translation management
class F_Lang 
{
	
	public static $translate;
	
    // set translation
	public static function set($key, $var, $language="def") {
        if (!isset($var)) {
            throw new Exception ("missing \$var argument when setting $key");
        }    
		self::$translate[$language][$key] =$var;
	}
    
    // get translation, change %1 %2 %3 to args
	public static function get($key, $pctargs=array(), $language="def") {
		
        if (isset(self::$translate[$language]))
            if (isset(self::$translate[$language][$key]))
                $res = self::$translate[$language][$key]; 
        
        if (!isset($res))
            $res = $key;
            
        $iargs = 1;
        foreach ($pctargs as $pctarg) {
            // Ef_Log::log($pctarg,'pctarg in Ef_Lang::get');
            $res = str_replace ("%$iargs", $pctarg, $res);
            $iargs++;            
        }
        for ($i=1; $i<=10; $i++) {
            if (strpos($res, "%$i") === false) // changed  0 to false 2018-09-17 
                break;
            $res = str_replace("%$i", '', $res);
        }
        return $res;
	}
    
    // return translation table
	public static function getVars($language="def") {
		return self::$translate[$language];
	}
	
}

// Wrapper for application properties
class F_Application 
{
	// protected static $viewstyle;
	protected static $dateformat;
	
	public static function getDateFormat() 
    {
	    if (!Ef_Application::$dateformat)
	        Ef_Application::setDateFormat();
	    
	    return Ef_Application::$dateformat;
	}
    
	public static function setDateFormat($argdateformat='Y-m-d') 
    {
	     Ef_Application::$dateformat = $argdateformat;   
	}
	
	public static function getDefaultCentury() 
    {
		return '20';
	}
}



// ?>