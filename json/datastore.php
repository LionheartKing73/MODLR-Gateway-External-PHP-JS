<?

include_once("../lib/lib.php");
header("Content-type: application/json");

ini_set("auto_detect_line_endings", true);

$alert = "";

function getColumns($table) { 
	global $db;
	$result = mysql_query("SHOW COLUMNS FROM ". mysql_real_escape_string($table),$db->Link_ID()); 
	if (!$result) { 
		echo 'Could not run query: ' . mysql_error(); 
	} 
		$fieldnames=array(); 
	if (mysql_num_rows($result) > 0) { 
		while ($row = mysql_fetch_assoc($result)) { 
		  	$fieldnames[] = $row['Field']; 
		} 
	} 

	return $fieldnames; 
} 

function is_date($str) {
	//d or j
	//m or n
	//Y or y
	//G or H
	//i
	//s
	$formats = array("Y-m-d","Y-m-d H-i-s", "d/m/Y");
	foreach ($formats as $format)
    {
		$date = DateTime::createFromFormat($format, $str);
		if ($date == false) {
			
		} else {
			return true;
		}
    }
    return false;
}

function performAuditAndCreateTable($table, $records, $fields) {
	global $db;
	echo "1. Creating Table.";
    
	$fieldIsNumeric = array();
	$fieldIsDate = array();
	$fieldIsEmpty = array();
	
	for($i=0;$i<count($fields);$i++) {
		array_push($fieldIsNumeric, true);
		array_push($fieldIsDate, true);
		array_push($fieldIsEmpty, true);
		
		if( strpos( $fields[$i] , "year" ) !== false  || strpos( $fields[$i] , "month" ) !== false  || strpos( $fields[$i] , "day" ) !== false  || strpos( $fields[$i] , "period" ) !== false  ) {
			$fieldIsNumeric[$i] = false;
		}
	}
	
	for($y=0;$y<count($records);$y++) {
		for($i=0;$i<count($fields);$i++) {
			//ignore blanks, they dont actually determine if a field is numeric or string.
			if( $records[$y][$i] == "" ) {
				
			} else {
				$fieldIsEmpty[$i] = false;
				
				if( is_numeric($records[$y][$i]) == false ) {
					$fieldIsNumeric[$i] = false;
				} 
				if( is_date($records[$y][$i]) == false ) {
					$fieldIsDate[$i] = false;
				} 
				if( strpos($records[$y][$i], ":") !== false ) {
					$fieldIsNumeric[$i] = false;
				} 
			}
		}
	}
	
	$sql = "CREATE TABLE datastore.".mysql_real_escape_string($table)." (id int(11) NOT NULL AUTO_INCREMENT,";
	
	for($i=0;$i<count($fields);$i++) {
		$str = " varchar(250) DEFAULT '',";
		if( $fieldIsEmpty[$i] ) {
			
		} else {
			if( $fieldIsNumeric[$i] ) {
				$str = " decimal(22,6) DEFAULT '0',";
			}
			if( $fieldIsDate[$i] ) {
				$str = " timestamp NULL DEFAULT NULL,";
			}
		}
		$sql .= $fields[$i] . $str;
	}
	$sql .= "PRIMARY KEY (id) ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8";
	
	$result = mysql_query($sql,$db->Link_ID()); 
	
	echo $sql . "\r\n";
}
function loadBatch($table, $records, $fields) {
	echo "3. Loading Batch.";
    if( count($records) == 0 ) {
        error_log("Records Size: 0.");
        print_r($records);
        print_r($fields);
        return;
    }
	global $db;
	
	$sql = "INSERT INTO ".$table." (";
	for($i=0;$i<count($fields);$i++) {
		$sql .= "`". mysql_real_escape_string($fields[$i]) . "`,";
	}
	$sql = substr($sql,0,strlen($sql)-1);
	$sql .= ") VALUES ";
	for($y=0;$y<count($records);$y++) {
		$sql .= "(";
		for($i=0;$i<count($fields);$i++) {
			$sql .= "'" . mysql_real_escape_string($records[$y][$i]) . "',";
		}
		$sql = substr($sql,0,strlen($sql)-1);
		$sql .= "),";
	}
	$sql = substr($sql,0,strlen($sql)-1);
	$sql .= ";";
	
	$result = mysql_query($sql,$db->Link_ID()); 
    
    $error = mysql_error($db->Link_ID());
    if( !empty($error) ) {
        error_log($error);
    }
    error_log($sql);
}

function cleanFieldForDB($str) { 
	$str = str_replace(" ","_",$str);	
	$str = str_replace("&","",$str);
	$str = str_replace("!","",$str);
	$str = str_replace("@","",$str);
	$str = str_replace("#","",$str);
	$str = str_replace("$","",$str);
	$str = str_replace("%","",$str);
	$str = str_replace("^","",$str);
	$str = str_replace("&","",$str);
	$str = str_replace("*","",$str);
	$str = str_replace("(","",$str);
	$str = str_replace(")","",$str);
	$str = str_replace("=","",$str);
	$str = str_replace("+","",$str);
	$str = str_replace(".","_",$str);
	$str = str_replace("-","_",$str);
	return strToLower($str);
}

function uploadFileData() {
	global $_FILES, $db;
	
	$name = "".$_FILES['file']['name'];
	$path = explode('.', $name);
	$ext = strtolower(end($path));
	$tmpName = $_FILES['file']['tmp_name'];
	
	
	$wipe = form("wipe");
	$table = form("table");
	$NeedCreation = false;
	if( $table == "" || is_null($table) ) { 
		//time to create a table...
		$table = str_replace(".".$ext,"",$name);
		$table = trim(cleanFieldForDB($table));
	
		$NeedCreation = true;
	} else {
		//perform wipe
		if( $wipe == "true" ) {
			$sql = "TRUNCATE TABLE ".mysql_real_escape_string($table).";";
			$result = mysql_query($sql,$db->Link_ID()); 
		}
		//;
	}
	
	
	//1. gather column names from first populated line of data.
	//2. read through the first 100 lines creating a massive insert statement. 
	//3. use the 100 lines to survey the data types. 
	//4. create the table with the resulting types
	//5. import the rest of the data
	
	$HadFirstRow = false;
	
	$records = array();
	$fields = array();
	
	// check the file is a csv
	if($ext === 'csv'){
		if(($handle = fopen($tmpName, 'r')) !== FALSE) {
			// necessary if a large csv file
			set_time_limit(0);
			
			$row = 0;
			while(($data = fgetcsv($handle, 1000, ',', '"')) !== FALSE) {
				// number of fields in the csv
				$num = count($data);
                if( $num == 0 ) {
                    
                } else {
                    if( !$HadFirstRow ) {
                        $hasAllFields = true;
                        $hasNumericValues = false;
                        $fieldsComplete = 0;
                        if( $NeedCreation ) { 
                            for($i=0;$i<count($data);$i++) {
                                $fieldName = $data[$i];
                                if( is_numeric($data[$i]) ) {
                                    $hasNumericValues = true;
                                    $fieldName = "field" . $i;
                                } else if( $data[$i] == "" ) {
                                    $hasAllFields = false;
                                    $fieldName = "field" . $i;
                                } else {
                                    $fieldsComplete++;
                                }
                                
                                $fieldName = cleanFieldForDB($fieldName);
                                if( $fieldName == "id" ) {
                                    $fieldName = "field" . $i;
                                }
                                array_push($fields, $fieldName);
                            }
                        } else {
                            $fields = getColumns($table);
                            
                        }
                        
                        $HadFirstRow = true;
                    } else {
                        array_push($records, $data);
                        
                        if( $row == 1000 ) {
                            //create the table.
                            if( $NeedCreation ) {
                                performAuditAndCreateTable($table, $records, $fields);
                            }
                        }
                         
                        //commit every 100 rows.
                        if( intval($row/1000) == $row/1000 ) {
                            //load data
                            loadBatch($table, $records, $fields);
                            $records = array();
                        }
                    }
				}
				// inc the row
				$row++;
			}
			fclose($handle);
			
			if( $row < 1000 ) {	//never made it to the survey 100 rows.
				//create table
				if( $NeedCreation )
					performAuditAndCreateTable($table, $records, $fields);
				
				//load data
				loadBatch($table, $records, $fields);
				$records = array();
			} else {	//commit final records.
				//load remaining data
				loadBatch($table, $records, $fields);
				$records = array();
			}
			
			
		}
	} else {
		//return bad http header. server error or something.
		header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
		die();
	}
	
	
	die();
}


//return a $db which references the target database.
$server_address = session("server_address");
$values = explode(":",$server_address);
$server_address = $values[0];

$password = getPasswordForServer(session("active_server_id"));

$db = new db_helper();
$db->Host = $server_address;
$db->User = "root";
$db->Password = $password;
$db->Database = "datastore";
$db->Close();
$db->connect();



//process querystring arguments
$action = querystring("action");
$table = querystring("table");
$page = querystring("page");
$size = querystring("size");


$actions = [];
$filters = [];

//process the actions provided.
if( $action == "upload" ) {
	$requests = json_decode("{}");
	
	if( isset($_FILES) ) { 
	
		//process the file upload.
		if($_FILES['file']['error'] == 0){
			//upload file
			uploadFileData();
		} else {
			//return bad http header. server error or something.
			header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
			die();	
		}
	
	}
} else if( $action == "rename" ) {
	if( $table == "" ) {
		header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
		die();	
	}
	
	$new = querystring("new");
	$sql = "RENAME TABLE ".mysql_real_escape_string($table)." TO ".mysql_real_escape_string($new).";";
	$db->CommandText($sql);
	$db->Execute();
	
	//
	die();
} else if( $action == "sample" ) {
	if( $table == "" ) {
		header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
		die();	
	}
	
	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename='.$table.'_sample.csv');
	header("Pragma: no-cache");
	header("Expires: 0");
	
	$columns = getColumns($table);
	$str = "";
	for($i=0;$i<count($columns);$i++) {
		if( $columns[$i] != "id" ) {
			$str .= "\"".$columns[$i]."\",";
		}
	}
	$str = substr($str,0,strlen($str)-1);
	echo $str."\n";
	
	die();
} else {
	$postData = file_get_contents("php://input");
	$requests = json_decode($postData);
	
	$actions = $requests->actions;
	$filters = $requests->filters;
}

$columns = getColumns($table);
$idColumn = $columns[0];

for($i=0;$i<count($actions);$i++) { 
	$action = $actions[$i];
	
	if( $action->action == "remove" ) {
		//echo "<!-- action:remove -->";
		
		$itemStr = "('".join("','",$action->items) . "')";
		$itemStr = str_replace("\'","'",mysql_real_escape_string($itemStr));
		
		$sql = "DELETE FROM " . $table . " WHERE `".$idColumn."` IN ".$itemStr.";";
		$db->CommandText($sql);
		$db->Execute();
	} else if( $action->action == "delete" ) {
		
		$password = $action->password;
		if( checkUserPassword( session("uid") , $password) ) {
			$sql = "DROP TABLE " . $table . ";";
			$db->CommandText($sql);
			$db->Execute();
		} else {
			
			$alert = "The password provided was not valid.";
		}
	} else if( $action->action == "remove_all" ) {
		$password = $action->password;
		if( checkUserPassword( session("uid") , $password) ) {
				
			$sql = "DELETE FROM " . $table . " WHERE 1;";
			$db->CommandText($sql);
			$db->Execute();
		
		} else {
			
			$alert = "The password provided was not valid.";
		}
	} else if( $action->action == "update" ) {
		$sql = "UPDATE " . $table . " SET ";
		for($k=0;$k<count($action->values);$k++) {
			$sql .= " `%s`='%s',";
		}
		//trim the comma
		$sql = substr($sql,0,strlen($sql)-1) . " WHERE `".$idColumn."`='%s';";
		
		$db->CommandText($sql);
		for($k=0;$k<count($action->values);$k++) {
			$db->Parameters($action->values[$k]->field);
			$db->Parameters($action->values[$k]->value);
		}
		$db->Parameters($action->id);
		$db->Execute();
		
		//echo $db->ExecutedCommand();
	} else if( $action->action == "insert" ) {
		$sql = "INSERT INTO " . $table . " (";
		for($k=0;$k<count($action->values);$k++) {
			$sql .= "%s,";
		}
		$sql = substr($sql,0,strlen($sql)-1) . ") VALUES (";
		for($k=0;$k<count($action->values);$k++) {
			$sql .= "'%s',";
		}
		$sql = substr($sql,0,strlen($sql)-1) . ");";
		
		$db->CommandText($sql);
		for($k=0;$k<count($action->values);$k++) {
			$db->Parameters($action->values[$k]->field);
		}
		for($k=0;$k<count($action->values);$k++) {
			$db->Parameters($action->values[$k]->value);
		}
		$db->Execute();
		
		//echo $db->ExecutedCommand();
	}  
}


//process querystring
$pageNo = 0;
if( $page != "" ) {
	$pageNo = intval($page)-1;
}
$sizeNo = 25;
if( $size != "" ) {
	$sizeNo = intval($size);
	if( $sizeNo > 100 ) 
		$sizeNo = 100;
}

$limitOne = $pageNo * $sizeNo;
$limitTwo = $sizeNo;




if( $table == "" ) {
	echo '{"rows" : [],"headings" : []}';
	die();
}



//return an updated set
echo '{"headings" : [';

$json = "";
for($i=0;$i<count($columns);$i++) { 
	$json .= '{"name" : "'.$columns[$i].'"},';
}
$json = substr($json,0,strlen($json)-1);
echo $json;

echo '], "rows" : [';

$queryColumns = array();
for($i=0;$i<count($columns);$i++) { 
	array_push($queryColumns, "`".$columns[$i]."`");
}

$whereClause = "";
for($i=0;$i<count($filters);$i++) {
	$filter = $filters[$i];
	$mode = $filter->mode;
	$column = $filter->column;
	$expression = $filter->expression;
	
	/*
	<option value='begins'>Begins with</option>
	<option value='contains'>Contains</option>
	<option value='ends'>Ends with</option>
	<option value='greater'>Is Greater than</option>
	<option value='less'>Is Less than</option>
	<option value='equals'>Is Equal to</option>
	<option value='notequals'>Is Not Equal to</option>
	*/
	if( $i == 0 ) {
		$whereClause .= " WHERE ";
	} else {
		$whereClause .= " AND ";
	}
	
	
	if( $mode == "begins" ) {
		$whereClause .= " ".$column." LIKE '".$expression."%' ";
	} else if( $mode == "ends" ) {
		$whereClause .= " ".$column." LIKE '%".$expression."' ";
	} else if( $mode == "contains" ) {
		$whereClause .= " ".$column." LIKE '%".$expression."%' ";
	} else if( $mode == "greater" ) {
		$whereClause .= " ".$column." > '".$expression."' ";
	} else if( $mode == "less" ) {
		$whereClause .= " ".$column." < '".$expression."' ";
	} else if( $mode == "equals" ) {
		
		if( is_numeric(trim($expression)) ) { 
			$whereClause .= " ".$column." = ".$expression." ";
		} else {
			$whereClause .= " ".$column." = '".$expression."' ";
		}
	} else if( $mode == "notequals" ) {
		$whereClause .= " ".$column." <> '".$expression."' ";
	}
}



$sql = "SELECT " . join(",",$queryColumns) . " FROM " . $table . " ".$whereClause." LIMIT ".$limitOne.",".$limitTwo.";";

//error_log("Datastore: ". $sql);

$json = "";
$db->CommandText($sql);
$db->Execute();
if ($db->Rows_Count() > 0) {
	while( $r = $db->Rows() ) {
		
		$json .= '{"id" : "'.$r[$columns[0]].'", "data" : [';
		
		for($i=0;$i<count($columns);$i++) { 
			$value = $r[$columns[$i]];
			$value = str_replace("\\","\\\\",$value);
			$json .= '"'.htmlentities($value).'",';
		}
		$json = substr($json,0,strlen($json)-1);
		
		$json .= "]},";
		
	}
}
$json = substr($json,0,strlen($json)-1);
echo $json;

echo '],';

$sql = "SELECT COUNT(*) as rows_total FROM " . $table . " ".$whereClause;
$db->CommandText($sql);
$db->Execute();
$rows_total = 0;
if( $db->Rows_Count() > 0 ) {
	$r = $db->Rows();
	$rows_total = $r['rows_total'];
}

echo '"count" : "'.$rows_total.'",';
echo '"page" : "'.($pageNo+1).'",';
echo '"size" : "'.$sizeNo.'",';
echo '"alert" : "'.$alert.'"';
echo '}';

?>