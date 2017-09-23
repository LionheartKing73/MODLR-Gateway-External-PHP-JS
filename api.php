<?php
ob_start("ob_gzhandler");
include_once("lib/lib.php");
header("Content-type: application/json");

$debug = false;


$service = querystring("service");
$server_id = querystring("server_id");
$server_address = session("server_address");
$cookie = session("user_cookie");

if( $server_id == "0" )
	$server_id = session("active_server_id");

$servers = thisUsersServers();
$found = false;
for($i=0;$i<count($servers);$i++) {
	if( $servers[$i] == $server_id ) {
		$found = true;
	}
}

if( $server_id != "" ) {
	
	$server_address =  getAddressForServer($server_id);
	$_SESSION["server_address_".$server_id] = $server_address;
	
	if( session("user_cookie_".cleanAddress($server_address)) == "" ) {
		$_SESSION["user_cookie_".cleanAddress($server_address)] = "";
		
		if( api_login($server_address,$_SESSION['username'], $_SESSION['password']) ) {
			//This should now happen automagically.
			//$_SESSION["user_cookie_".cleanAddress($server_address)] = session("user_cookie");
		} else {
			$_SESSION["server_address_".$server_id] = "";
		}
	}
	
	$cookie = $_SESSION["user_cookie_".cleanAddress($server_address)];
} else {
	//assume its the default.
	
}

if( $found ) {
	// The data to send to the API
	$postData = file_get_contents("php://input");
	
	//if the user is a contributor only let through the tasks which are directly available to collaborators:
		//workview.execute
		//cube.update
	/*
	//************************* security is now with the application server
	if( session("role") != "MODELLER" ) {
		$tasks = json_decode($postData);
		for($i=0;$i<count($tasks->tasks);$i++) {
			$task = $tasks->tasks[$i];
			if( $task->task != "cube.update" && $task->task != "workview.execute"  && $task->task != "cube.value.trace" && $task->task != "cube.value" && $task->task != "workview.metadata" ) {
				$tasks->tasks[$i] = json_decode('{"task":"do.nothing"}');
			}
		}
		
		
	}
	*/
	$tasks = json_decode($postData);
	$postData = json_encode($tasks);
	$cookieOld = session("user_cookie");
	echo api($server_address, $service, $postData, $cookie);
	$_SESSION["user_cookie"] = $cookieOld;
}


?>