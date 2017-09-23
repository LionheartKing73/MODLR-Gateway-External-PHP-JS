<?




include_once("../lib/lib.php");
ini_set("auto_detect_line_endings", true);

$servers = thisUsersServers();

if (!isset($_SESSION['uid'])) {
	echo json_encode(array("result"=>false,"error" => "Session Expired"));
	die();
}

//process querystring arguments
$statement = form("statement");
if( $statement == "" ) {
	$statement = querystring("statement");
}
/*
$format = form("format");
if( $format == "" ) {
	$format = querystring("format");
}

if( $format == "" ) {
	header("Content-type: application/json; charset=utf-8");
} else {
	header("Content-type: ".$format."; charset=utf-8");
}
*/
$statement = str_replace("\"","\\\"",$statement);

$json = "{\"tasks\": [";
$json .= "{\"task\": \"language.query\", \"statement\":\"" . $statement . "\"}";
$json .= "]}";

$res = array();

for($i=0;$i<count($servers);$i++) {
	$server_id = $servers[$i];
	
	$results = api_short_efficient(SERVICE_COLLABORATOR, $json, $server_id);
	
	$response = array("server_id"=>$server_id,"response"=>$results->results[0]);
	
	array_push($res, $response);

}

echo json_encode(array("result"=>true,"response"=>$res));

?>