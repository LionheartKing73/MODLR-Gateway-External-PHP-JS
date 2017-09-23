<?php
ob_start("ob_gzhandler");
include_once("lib/lib.php");
header("Content-type: application/json");

$debug = false;


$service = "server.service";
$server_address = session("server_address");
$cookie = session("user_cookie");
$server_id = session("active_server_id");


$server_address = session("server_address_".$server_id);
$cookie = session("user_cookie_".$server_id);


	// The data to send to the API
	
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
	$tasks = '{"tasks":[{"task":"home.directory"},{"task":"storage.check"},{"task":"server.memory"},{"task":"server.users"}]}';
	$postData = json_encode($tasks);
	$cookieOld = session("user_cookie");

	echo api($server_address, $service, $postData, $cookie);
	$_SESSION["user_cookie"] = $cookieOld;

	print_r(curl_error );

?>