<?php



class db_helper {
    public $Host = C_DB_HOST;          // Hostname of our MySQL server
    public $Database = C_DB_NAME;      // Logical database name on that server
    public $User = C_DB_USER;          // Database user
    public $Password = C_DB_PASS;      // Database user's password

    private $link_ID = 0;               // Result of mysql_connect()
    private $query_ID = 0;              // Result of most recent mysql_query()
    private $last_Insert_ID = 0;        // Identity key retrieved after insert

    private $commandText = "";          // sql command
    private $executedCommandText = "";  // sql command executed
    private $parameters = array();      // sql parameters
 
    private $errormsg = "";

    // properties
    public function Link_ID($value = "") {
        if ($value != "") {
            $this->link_ID = $value;
        }
        return $this->link_ID;
    }

    public function Query_ID($value = "") {
        if ($value != "") {
            $this->query_ID = $value;
        }
        return $this->query_ID;
    }

    public function Last_Insert_ID($value = "") {
        if ($value != "") {
            $this->last_Insert_ID = $value;
        }
        return $this->last_Insert_ID;
    }

    // command text
    public function CommandText($value = "") {
        if ($value != "") {
            $this->commandText = $value;
            $this->Parameters_Clear();
        }
        return $this->commandText;
    }

    // command parameters
    public function Parameters($value = null, $isnullable = false) {
        if (((!isset($value) || trim($value) == '')) && ($isnullable)) {
        	$this->parameters[] = "NULL";
        }
        else if (!is_null($value)) {
            $this->parameters[] = $value;
        }
        return $this->parameters;
    }

    // get executed command statement
    public function ExecutedCommand() {
        return $this->executedCommandText;
    }

    // boolean to check if execution has errors
    public function HasErrors() {
        return (strlen($this->errormsg) > 0);
    }

    // get error message
    public function Errors() {
        return $this->errormsg;
    }

    // constructor
    public function __construct() {
        $this->connect();
    }

    // open connection
    public function connect() {
        if ($this->link_ID == 0) {
            $this->link_ID = mysqli_connect($this->Host, $this->User, $this->Password);
            if (!$this->link_ID) {
				
				header("Location: /500/" );
				die();
						
                throw new Exception("Could not connect: ".mysql_errno());
            }
            $select_db = mysqli_select_db($this->link_ID, $this->Database);
            if (!$select_db) {
                throw new Exception("Unable to select database: ".$this->Database);
            }
        }
    }

    // force close connection
    public function Close() {
        if ($this->link_ID != 0) mysqli_close($this->link_ID);
        $this->link_ID = 0;
    }

    // clear parameters
    private function Parameters_Clear() {
        unset($this->parameters);
        $this->parameters = array();
    }

    // execute sql statement
    public function Execute($command = "") {
        try {
            if ($this->link_ID == null) {
                $this->connect();
            }
            // assemble commandtext
            if (!empty($command)) {
                // remove parameters
                $this->Parameters_Clear();
                $this->CommandText($command);
            }
            else {
            	if (sizeof($this->parameters) > 0) { 
                	for ($i = 0; $i < sizeof($this->parameters); $i++) {
                    	// escape for sql injection
                    	$this->parameters[$i] = mysqli_real_escape_string($this->link_ID, $this->parameters[$i]);                	
                		// $this->commandText = str_replace("\%s", $this->parameters[$i], $this->commandText, 1);
                	}                	
                    $this->commandText = vsprintf($this->CommandText(), $this->Parameters());
                }
            }
            $this->executedCommandText = $this->CommandText();

            $this->query_ID = mysqli_query($this->link_ID, $this->executedCommandText);
            // retrieve Id of inserted data
            if (strpos(strtolower($this->executedCommandText), "insert") > -1) {
                $this->last_Insert_ID = mysqli_insert_id($this->link_ID);
            }
            return $this->query_ID;
        }
        catch (Exception $ex) {
            throw $ex->message;
        }
    }

    // execute scalar
	public function ExecuteScalar($command = "") {
        try {
            if ($this->link_ID == 0) {
                $this->connect();
            }
            // assemble commandtext
            if (!empty($command)) {
                // remove parameters
                $this->Parameters_Clear();
                $this->CommandText($command);
            }
            else {
                for ($i = 0; $i < sizeof($this->parameters); $i++) {
                    // escape for sql injection
                    $this->parameters[$i] = mysqli_real_escape_string($this->link_ID, $this->parameters[$i]);
                }
                if (sizeof($this->parameters) > 0) {
                    $this->CommandText(vsprintf($this->CommandText(), $this->Parameters()));
                }
            }
            $this->executedCommandText = $this->CommandText();

            $this->query_ID = mysqli_query($this->link_ID, $this->executedCommandText);
            $r = $this->Rows(true);
            return intval($r[0]);
        }
        catch (Exception $ex) {
            throw $ex->message;
        }
    }

    // returns collection of rows
    public function Rows($asArray = false) {
    	$result = null;
        if ($asArray) {
        	if (!is_resource($this->query_ID)) {
        		if (session("isAdmin")) echo $this->ExecutedCommand()."<br/>";
        		echo $this->Errors();
        	}
        	$result = mysqli_fetch_array($this->query_ID);
        }
        else {
        	$result = mysqli_fetch_assoc($this->query_ID);
        }
        return $result;
    }

    // return retrieved rows count
    public function Rows_Count() {
        if ($this->query_ID != null) {
            return intval(mysqli_num_rows($this->query_ID));
        }
        return 0;
    }

    // return affected row count
    public function Rows_Affected() {
        if ($this->query_ID != 0) {
            return mysqli_affected_rows($this->link_ID);
        }
        return 0;
    }
}

$db = new db_helper();

function ValidateName($text) {
	return ValidateExpression($text);
}
function ValidateExpression($input, $min_length = 3, $max_length = 20, $pattern = '[^[a-zA-Z0-9_]{3,}$]', $checkFilter = true)
{
	if ($input == "") return false;
	if ((strlen($input) < $min_length) || (strlen($input) > $max_length)) return false;
	if ($checkFilter) {
		$db = new db_helper();
		$db->CommandText("SELECT * FROM word_filter WHERE Name != ''");
		$db->Execute();
		if ($db->Rows_Count() > 0) {
			while ($r = $db->Rows()) {
				$temp = $input;
				$temp = str_ireplace($r["Name"], "", $temp);
				if ($temp != $input) return false;
			}
		}
	}
	return preg_match($pattern, $input);
}
function ReplaceFilter($txt, $displayForAdmin = false) {
    $db = new db_helper();
    $db->Execute("SELECT * FROM word_filter");    
    if ($db->Rows_Count() > 0) {        
        while ($r = $db->Rows()) {        	
            $txt = str_ireplace($r["Word"], $displayForAdmin ? "<font style='color: red;'>".$r['Word']."</font>" : "[filtered]", $txt);            
        }
    }
    return trim($txt);
}


?>