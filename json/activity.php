<?

include_once("../lib/lib.php");
header("Content-type: application/json");

ini_set("auto_detect_line_endings", true);

function user_by_email($email) {
	$db = new db_helper();
	$db->CommandText("SELECT id FROM users WHERE email = '%s' AND account_disabled=0;");
	$db->Parameters($email);
	$db->Execute();
	if ($db->Rows_Count() > 0) {
		$r = $db->Rows();
		return $r['id'];
	} else {
		return 0;
	}
}


function user_role_in_client($user_id, $client_id) {
	$db = new db_helper();
	$db->CommandText("SELECT role FROM users_clients WHERE user_id='%s' AND client_id='%s';");
	$db->Parameters($user_id);
	$db->Parameters($client_id);
	$db->Execute();
	if ($db->Rows_Count() > 0) {
		$r = $db->Rows();
		
		return $r['role'];
	} else {
		return "";
	}
}


function remove_tag($id, $activityid, $tagid) {
	
	//remove the user from the activity.
	$json = "{\"tasks\": [";
	$json .= "{\"task\": \"activity.tag.delete\", \"id\":\"" . $id . "\", \"activityid\":\"" . $activityid . "\", \"tagid\":\"" . $tagid . "\"}";
	$json .= "]}";
	
	$results = api_short(SERVICE_MODEL, $json);
	
}
function remove_user($id, $activityid, $user_id) {
	
	//remove the user from the activity.
	$json = "{\"tasks\": [";
	$json .= "{\"task\": \"activity.user.remove\", \"id\":\"" . $id . "\", \"activityid\":\"" . $activityid . "\", \"userid\":\"" . $user_id . "\"}";
	$json .= "]}";
	
	$results = api_short(SERVICE_MODEL, $json);
	
}

function user_add($id, $activityid, $email, $name, $phone, $tags, $client_id) {
	$password = randomPassword(6);
	
	if( trim($email) == "" || trim($name) == "" ) {
		header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
		echo "An email address and name must be provided.";
		die();
	}
	
	$needsUpdatingTheModelerAccounts = false;
	
	//do we need to create a user account.
	$user_id = user_by_email($email);
	if( $user_id == 0 ) {
		$needsUpdatingTheModelerAccounts = true;
		
		//passwords are stored temporarily in a password_temp field so that we can email users to let them know if their access.
		//create the user account.
		$db = new db_helper();
		$db->CommandText("INSERT INTO users (email, password, name, phone, client_id,subscribed, password_temp, activated) VALUES ('%s', '%s', '%s', '%s','%s','%s','%s','%s');");
		$db->Parameters($email);
		$db->Parameters(md5($password));
		$db->Parameters($name);
		$db->Parameters($phone);
		$db->Parameters($client_id);
		$db->Parameters(1);
		$db->Parameters($password);
		$db->Parameters(1);

		$db->Execute();
		if ($db->Rows_Affected() > 0) {
			$user_id = $db->Last_Insert_ID();
		} else {
			header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
			die();
		}
	}
	
	if( $user_id > 0 ) {
		
		//add the user to the client/users group.
		if( user_role_in_client($user_id,$client_id) == "" ) {
			$db->CommandText("INSERT INTO users_clients (client_id,user_id,author_user_id,role) VALUES ('%s','%s','%s','%s');");
			$db->Parameters($client_id);
			$db->Parameters($user_id);
			$db->Parameters(session('uid'));
			$db->Parameters('PLANNER');
			$db->Execute();
		}
		
		//add the user to the activity.
		$json = "{\"tasks\": [";
		$json .= "{\"task\": \"activity.get\", \"id\":\"" . $id . "\", \"activityid\":\"" . $activityid . "\"}";
		$json .= "]}";

		$results = api_short(SERVICE_MODEL, $json);
		$activity_contents = $results->results[0]->activity;
		//check if the user is in the activity. if not update the activity.
		
		$users = array();
		if( property_exists($activity_contents,"users") ) {
			$users = $activity_contents->users;
		}
		
		$bFound = false;
		for($i=0;$i<count($users);$i++) {
			$user = $users[$i];
			if( intval($user->id) == $user_id ) {
				$bFound = true;
				break;
			}
		}
		
		if( !$bFound ) {
			//add the user to the activity and 
			
			array_push($users, json_decode('{"id" : "'.$user_id.'", "name" : "'.$name.'", "email" : "'.$email.'", "tags" : "'.$tags.'"}'));
			
			$activity_contents->users = $users;
		}
		
		if( $needsUpdatingTheModelerAccounts || !$bFound ) {
			
			$activityStr = json_encode($activity_contents);
			
			$json = "{\"tasks\": [";
			$json .= "{\"task\": \"server.security.refresh\"}";
			$json .= "]}";
	
			$results = api_short(SERVICE_SERVER, $json);
			//print_r($results);
			
			
			$json = "{\"tasks\": [";
			$json .= "{\"task\": \"activity.user.add\", \"id\":\"" . $id . "\",\"activityid\":\"" . $activityid . "\", \"userid\" : \"".$user_id."\", \"tags\" : \"".$tags."\"}";
			$json .= "]}";

			$results = api_short(SERVICE_MODEL, $json);
			

		}
	
	} else {	
		header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
		die();
	}
	
}

function loadBatch($table, $records, $fields) {
	global $db;
	
	$sql = "INSERT INTO datastore.".$table." (";
	for($i=0;$i<count($fields);$i++) {
		$sql .= mysql_real_escape_string($fields[$i]) . ",";
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
	
	//echo $sql . "\r\n";
}

function uploadFileData() {
	global $_FILES, $db;
	
	$name = "".$_FILES['file']['name'];
	$path = explode('.', $name);
	$ext = strtolower(end($path));
	$tmpName = $_FILES['file']['tmp_name'];
	
	$wipe = form("wipe");
	//perform wipe
	if( $wipe == "true" ) {
		//remove contributors from this activity.
	}
	
	$fields = array();
	
	
	// check the file is a csv
	if($ext === 'csv'){
		if(($handle = fopen($tmpName, 'r')) !== FALSE) {
			// necessary if a large csv file
			set_time_limit(0);
			
			$row = 0;
			while(($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
				// number of fields in the csv
				$num = count($data);

				if( $row == 0 ) {
					
				} else {
					array_push($records, $data);
					
					//commit every 100 rows.
					if( intval($row/100) == $row/100 ) {
						//load data
						loadBatch($table, $records, $fields);
						$records = array();
					}
				}
				
				// inc the row
				$row++;
			}
			fclose($handle);
					
			//load remaining data
			loadBatch($table, $records, $fields);
			$records = array();
		
			
		}
	} else {
		//return bad http header. server error or something.
		header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
		die();
	}
	
	
	die();
}


function add_page($id, $activityid, $editId) {
	
	//title is the identifier
	$title = querystring("title");
	$page_type = querystring("page_type");
	$page_0 = querystring("page_0");
	$page_1 = querystring("page_1");
	$page_2 = querystring("page_2");
	$page_1_size = querystring("page_1_size");
	$page_2_size = querystring("page_2_size");
	
	$route = querystring("route");
	
	
	//remove the user from the activity.
	$json = "{\"tasks\": [";
	$json .= "{\"task\": \"activity.get\", \"id\":\"" . $id . "\", \"activityid\":\"" . $activityid . "\"}";
	$json .= "]}";
	
	$results = api_short(SERVICE_MODEL, $json);
	$activity_contents = $results->results[0]->activity;
	//check if the user is in the activity. if not update the activity.
	
	if( property_exists($activity_contents,"screens") ) {
		//$screens = $activity_contents->screens;
	} else {
		$activity_contents->screens = array();
	}
	
	//if the page id is provided then configure this specific page.
	$page = json_decode('{"title" : "'.$title.'", "page_type" : "'.$page_type.'", "page_0" : "'.$page_0.'", "page_1" : "'.$page_1.'", "page_2" : "'.$page_2.'", "page_1_size" : "'.$page_1_size.'", "page_2_size" : "'.$page_2_size.'", "route" : "'.$route.'"}');
	
	$bFound = false;
	for($i=0;$i<count($activity_contents->screens);$i++) {
		$screen = $activity_contents->screens[$i];
		if( $screen->id == $editId ) {
			$page = json_decode('{"title" : "'.$title.'", "page_type" : "'.$page_type.'", "page_0" : "'.$page_0.'", "page_1" : "'.$page_1.'", "page_2" : "'.$page_2.'", "page_1_size" : "'.$page_1_size.'", "page_2_size" : "'.$page_2_size.'", "id" : "'.$editId.'", "route" : "'.$route.'"}');
			$activity_contents->screens[$i] = $page;
			
			$bFound = true;
			break;
		}
		if( $screen->title == $title ) {
			header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
			echo "Page already exists.";
			die();
		}
	}
	if( !$bFound ) {
		array_push($activity_contents->screens, $page);
	}
	
	$activityStr = json_encode($activity_contents);
	
	$json = "{\"tasks\": [";
	$json .= "{\"task\": \"activity.create.update\", \"id\":\"" . $id . "\",\"activityid\":\"" . $activityid . "\", \"definition\" : ".$activityStr."}";
	$json .= "]}";

	$results = api_short(SERVICE_MODEL, $json);

	
}


function remove_page($id, $activityid, $title) {
	
	//title is the identifier
	
	//remove the user from the activity.
	$json = "{\"tasks\": [";
	$json .= "{\"task\": \"activity.get\", \"id\":\"" . $id . "\", \"activityid\":\"" . $activityid . "\"}";
	$json .= "]}";
	
	$results = api_short(SERVICE_MODEL, $json);
	$activity_contents = $results->results[0]->activity;
	//check if the user is in the activity. if not update the activity.
	
	if( property_exists($activity_contents,"screens") ) {
		//$screens = $activity_contents->screens;
	} else {
		$activity_contents->screens = array();
	}
	
	for($i=0;$i<count($activity_contents->screens);$i++) {
		$screen = $activity_contents->screens[$i];
		if( $screen->title == $title ) {
			array_splice($activity_contents->screens,$i,1);
			break;
		}
	}
	
	$activityStr = json_encode($activity_contents);
	
	$json = "{\"tasks\": [";
	$json .= "{\"task\": \"activity.create.update\", \"id\":\"" . $id . "\",\"activityid\":\"" . $activityid . "\", \"definition\" : ".$activityStr."}";
	$json .= "]}";

	$results = api_short(SERVICE_MODEL, $json);
	
}

function move_page($id, $activityid, $title, $direction) {
	
	//title is the identifier
	
	//remove the user from the activity.
	$json = "{\"tasks\": [";
	$json .= "{\"task\": \"activity.get\", \"id\":\"" . $id . "\", \"activityid\":\"" . $activityid . "\"}";
	$json .= "]}";
	
	$results = api_short(SERVICE_MODEL, $json);
	$activity_contents = $results->results[0]->activity;
	//check if the user is in the activity. if not update the activity.
	
	if( property_exists($activity_contents,"screens") ) {
		//$screens = $activity_contents->screens;
	} else {
		$activity_contents->screens = array();
	}
	
	
	
	$newPosition = 0;
	$screen = null;
	$screens = $activity_contents->screens;
	for($i=0;$i<count($screens);$i++) {
		$screen = $screens[$i];
		if( $screen->title == $title ) {
			if( $direction == "up" ) {
				$newPosition = $i-1;
			} else {
				$newPosition = $i+1;
			}
			$page = $screen;
			array_splice($screens,$i,1);
			break;
		}
	}
	
	if( $newPosition < 0 )
		$newPosition = 0;
	if( $newPosition >= count($screens) )
		$newPosition = count($screens)-1;
		
	array_splice($screens,$newPosition,0,array($screen));
	$activity_contents->screens = $screens;
	
	$activityStr = json_encode($activity_contents);
	
	$json = "{\"tasks\": [";
	$json .= "{\"task\": \"activity.create.update\", \"id\":\"" . $id . "\",\"activityid\":\"" . $activityid . "\", \"definition\" : ".$activityStr."}";
	$json .= "]}";

	$results = api_short(SERVICE_MODEL, $json);
	
}

//process querystring arguments
$action = querystring("action");

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
} else if( $action == "add" ) {
	//password_temp
	
	$id = querystring("id");
	$activityid = querystring("activityid");
	
	$email = querystring("email");
	$name = querystring("name");
	$phone = querystring("phone");
	$tags = querystring("tags");
	
	
	$client_id = session("active_client_id");
	user_add($id, $activityid, $email, $name, $phone, $tags, $client_id);
} else if( $action == "add_existing" ) {
	
	 
	$id = querystring("id");
	$activityid = querystring("activityid");
	
	$tags = querystring("tags");
	$userid = querystring("userid");
	
	$json = "{\"tasks\": [";
	$json .= "{\"task\": \"activity.user.add\", \"id\":\"" . $id . "\",\"activityid\":\"" . $activityid . "\", \"userid\" : \"".$userid."\", \"tags\" : \"".$tags."\"}";
	$json .= "]}";

	$results = api_short(SERVICE_MODEL, $json);
	
} else if( $action == "remove_tag" ) {
	
	$id = querystring("id");
	$activityid = querystring("activityid");
	$tagid = querystring("tagid");
	
	remove_tag($id, $activityid, $tagid);
	
} else if( $action == "remove_user" ) {
	
	$id = querystring("id");
	$activityid = querystring("activityid");
	$user_id = querystring("user_id");
	
	remove_user($id, $activityid, $user_id);
	
} else if( $action == "add_page" ) {
	
	$id = querystring("id");
	$activityid = querystring("activityid");
	
	add_page($id, $activityid,"");
	
} else if( $action == "update_page" ) {
		
	$id = querystring("id");
	$activityid = querystring("activityid");
	
	add_page($id, $activityid, querystring("old"));
	
		
		
} else if( $action == "remove_page" ) {
	
	$id = querystring("id");
	$activityid = querystring("activityid");
	$title = querystring("title");
	
	remove_page($id, $activityid,$title);
	
} else if( $action == "move_page" ) {
	
	$id = querystring("id");
	$activityid = querystring("activityid");
	$title = querystring("title");
	$direction = querystring("direction");
	
	move_page($id, $activityid,$title,$direction);

} else if( $action == "hierarchy" ) { 
	
	$id = querystring("id");
	$dimension = querystring("dimension");
	$hierarchy = querystring("hierarchy");
	
	//return the dimension contents.
	$json = "{\"tasks\": [";
	$json .= "{\"task\": \"dimension.hierarchy.get\", \"id\":\"" . $id . "\", \"dimensionid\":\"" . $dimension . "\", \"hierarchyid\":\"" . $hierarchy . "\"}";
	$json .= "]}";

	$results = api_short(SERVICE_MODEL, $json);
	echo  json_encode($results);
	die();
}





$id = querystring("id");
$activityid = querystring("activityid");

$json = "{\"tasks\": [";
$json .= "{\"task\": \"model.get\", \"id\":\"" . $id . "\"},";
$json .= "{\"task\": \"activity.get\", \"id\":\"" . $id . "\", \"activityid\":\"" . $activityid . "\"}";
$json .= "]}";

$results = api_short(SERVICE_MODEL, $json);
echo  json_encode($results);

?>