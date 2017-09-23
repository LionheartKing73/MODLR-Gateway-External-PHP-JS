<?php
include_once("lib/lib.php");
header("Content-type: application/json");

$debug = false;

if( $debug )
	echo "<!-- AUTH SERVICE -->";


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

$service = querystring("service");

if( $service == "super" ) {
	if( $debug )
		echo "<!-- AUTH SERVICE SUPER -->";
	
	//an application server is reverse authenticating on the system
	$key = querystring("key");					//application_session_key
	$client_id = querystring("client_id"); 		//client hash
	$port = querystring("port");				//application_port
	$version = querystring("version");				//application_port
	$server_ip = getRealIpAddr();
	$server_address = $_SERVER['REMOTE_ADDR'] . ":" . $port;
	
	$db = new db_helper();
	$db->CommandText("SELECT client_id FROM clients WHERE hash = '%s' LIMIT 1");
	$db->Parameters($client_id);
	$db->Execute();
	if ($db->Rows_Count() > 0) {
		$r = $db->Rows();
		
		$id = $r['client_id'];
		
		if( $debug )
			echo "<!-- CLIENT: ".$id." -->";
		
		
		$db->CommandText("SELECT users.id,email,name,password,phone,role FROM users_clients LEFT JOIN users ON users.id = users_clients.user_id WHERE users_clients.client_id = '%s' AND users.account_disabled=0;");
		$db->Parameters($id);
		$db->Execute();
		if ($db->Rows_Count() > 0) {
			$users = array();
			$count=0;
			while( $r = $db->Rows() ) { 
				$user = array(
						'id' => $r['id'],
						'username' => $r['email'],
						'name' => $r['name'],
						'password' => $r['password'],
						'phone' => $r['phone'],
						'role' => $r['role']
					);
				$users[$count] = $user;
				$count++;
			}
			
			echo json_encode($users);
			
			//update versioning
			$db = new db_helper();
			$db->CommandText("UPDATE servers SET server_version = '%s' WHERE server_ip = '%s' AND client_id = '%s';");
			$db->Parameters($version);
			$db->Parameters($server_ip);
			$db->Parameters($id);
			$db->Execute();
			
			//echo "sql:" .$db->ExecutedCommand();
			
			die();
		}
	} else {
		//auth fail
		echo "<!-- FAILED AUTHORISATION-->";
		die();
	}
	
} else if( $service == "lookup" ) {
	
	$server_ip = getRealIpAddr();
	
	if( $server_ip == "59.167.240.155" || $server_ip == "101.177.126.125") {
		$server_ip = "128.199.158.89";
	}
	
	$db = new db_helper();
	$db->CommandText("SELECT clients.client_name, clients.client_brand, clients.client_color_primary, clients.client_color_secondary, clients.country, server_codename, server_ip, server_id, server_port, server_region, server_version, server_domain FROM modlr.servers LEFT JOIN clients ON clients.client_id = servers.client_id WHERE server_ip = '%s' LIMIT 1");
	$db->Parameters($server_ip);
	$db->Execute();
	
	if ($db->Rows_Count() > 0) {
		$r = $db->Rows();
		
		$payload = array(
			"name" => $r['client_name'],
			"brand" => $r['client_brand'],
			"color_primary" => $r['client_color_primary'],
			"color_secondary" => $r['client_color_secondary'],
			"country" => $r['country'],
			"version" => $r['server_version'],
			"domain" => $r['server_domain'],
			"server_id" => $r['server_id']
		);
		
		echo json_encode($payload);
	} else {
		echo json_encode(array("error" => "instance not identifiable."));
	}
	
} else if( $service == "sms" ) {
	
	$client_id = querystring("client_id"); 		//client hash
	$version = querystring("version"); 		//client hash
	
	$to = querystring("to"); 		//client hash
	$body = querystring("body"); 
	if( $body == "" ) {
		$body = form("body"); 		//client hash
	}
	
	$server_ip = getRealIpAddr();
	
	$db = new db_helper();
	$db->CommandText("SELECT client_id FROM clients WHERE hash = '%s' LIMIT 1");
	$db->Parameters($client_id);
	$db->Execute();
	if ($db->Rows_Count() > 0) {
		$r = $db->Rows();
		$client_id = $r['client_id'];
		
		$db = new db_helper();
		$db->CommandText("INSERT INTO sms_queue (client_id,server_ip,server_version,sms_to,sms_body) VALUES ('%s','%s','%s','%s','%s');");
		$db->Parameters($client_id);
		$db->Parameters($server_ip);
		$db->Parameters($version);
		$db->Parameters($to);
		$db->Parameters($body);
		$db->Execute();
		
		echo "1";
		die();
	} 
	
	echo "0";
	die();
} else if( $service == "email" ) {
	
	$client_id = querystring("client_id"); 		//client hash
	$version = querystring("version"); 		//client hash
	
	$to = querystring("to"); 		//client hash
	$from = querystring("from"); 		//client hash
	
	$html = querystring("html"); 
	if( $html == "" ) {
		$html = form("html"); 		//client hash
	}
	
	$subject = querystring("subject"); 		//client hash
	
	$server_ip = getRealIpAddr();
	
	
	$db = new db_helper();
	$db->CommandText("SELECT client_id FROM clients WHERE hash = '%s' LIMIT 1");
	$db->Parameters($client_id);
	$db->Execute();
	if ($db->Rows_Count() > 0) {
		$r = $db->Rows();
		$client_id = $r['client_id'];
		
		$db = new db_helper();
		$db->CommandText("INSERT INTO email_queue (client_id,server_ip,server_version,email_from,email_to,email_subject,email_html) VALUES ('%s','%s','%s','%s','%s','%s','%s');");
		$db->Parameters($client_id);
		$db->Parameters($server_ip);
		$db->Parameters($version);
		$db->Parameters($from);
		$db->Parameters($to);
		$db->Parameters($subject);
		$db->Parameters($html);
		$db->Execute();
		
		echo "1";
		die();
	} 
	
	echo "0";
	die();
	
	
} else if( $service == "account" ) {
	$password = randomPassword(8);
	
	$client_id = querystring("client_id"); 		//client hash
	$name = querystring("name"); 		
	$email = querystring("email"); 		
	$mobile = querystring("mobile"); 	

	$db = new db_helper();
	$db->CommandText("SELECT client_id FROM clients WHERE hash = '%s' LIMIT 1");
	$db->Parameters($client_id);
	$db->Execute();
	if ($db->Rows_Count() > 0) {
		$r = $db->Rows();
		$client_id = $r['client_id'];
	} else {
		header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
		die();
	}
	
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
		$db->Parameters($mobile);
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
			$db->Parameters($user_id);
			$db->Parameters('PLANNER');
			$db->Execute();
		}
	}
	
	echo $password;
	die();
	
	
} else if( $service == "change" ) {
	
	$server_ip = getRealIpAddr();
	$client_id = 	querystring("client_id"); 		//client hash
	$modelid = 		querystring("modelid"); 		//client hash
	$activityid = 	querystring("activityid"); 		//client hash
	$pageid = 		querystring("pageid"); 		//client hash
	$userid = 		querystring("userid"); 		//client hash
	$encoded = 		form("content"); 		//client hash
	
	
	$db = new db_helper();
	$db->CommandText("SELECT client_id FROM clients WHERE hash = '%s' LIMIT 1");
	$db->Parameters($client_id);
	$db->Execute();
	if ($db->Rows_Count() > 0) {
		$r = $db->Rows();
		$client_id = $r['client_id'];
	} else {
		header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
		die();
	}
	
	
	$serverid = 0;
	
	//update versioning
	$db = new db_helper();
	$db->CommandText("SELECT server_id FROM servers WHERE server_ip = '%s' AND client_id = '%s';");
	$db->Parameters($server_ip);
	$db->Parameters($client_id);
	$db->Execute();
	if ($db->Rows_Count() > 0) {
		$r = $db->Rows();
		$serverid = $r['server_id'];
	} else {
		header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
		die();
	}	
	
	$logRecord = PageChangesLogEx($encoded, $serverid, $modelid, $activityid, $pageid, $userid);
	
} else if( $service == "error" ) {
	
	$client_id = querystring("client_id"); 		//client hash
	$message = querystring("message"); 		//client hash
	$version = querystring("version"); 		//client hash
	$callstack = querystring("callstack"); 		//client hash
	$server_ip = getRealIpAddr();
	
	if( $version == "" )
		$version = "Pre Versioning";
	
	$exception = "Exception";
	if( strpos( $callstack , ":")  === false) {
	
	} else {
		$exception = substr( $callstack , 0 , strpos( $callstack , ":")  );
	}
	
	
	$db = new db_helper();
	$db->CommandText("SELECT client_id FROM clients WHERE hash = '%s' LIMIT 1");
	$db->Parameters($client_id);
	$db->Execute();
	if ($db->Rows_Count() > 0) {
		$r = $db->Rows();
		$client_id = $r['client_id'];
		
		$db = new db_helper();
		$db->CommandText("INSERT INTO servers_errors (client_id,server_ip,message,callstack,exception,server_version) VALUES ('%s','%s','%s','%s','%s','%s');");
		$db->Parameters($client_id);
		$db->Parameters($server_ip);
		$db->Parameters($message);
		$db->Parameters($callstack);
		$db->Parameters($exception);
		$db->Parameters($version);
		$db->Execute();
		
	} 
	
	
	
}

?>