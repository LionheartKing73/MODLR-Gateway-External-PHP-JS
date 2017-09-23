<?

include_once("../lib/lib.php");
header("Content-type: application/json");
ini_set("auto_detect_line_endings", true);

//process querystring arguments
$action = querystring("action");
$id = querystring("id");
$activityid = querystring("activityid");
$page_id = querystring("page");

if( $action == "save" ) {
	
	$postData = file_get_contents("php://input");
	$requests = json_decode($postData);
	
	$page_contents = $requests->page_contents;
	$type = $requests->type;
	$title = $requests->title;
	$style = $requests->style;
	
	$def = array(
		'pageid' => $page_id,
		'type' => $type,
		'name' => $title,
		'style' => $style,
		'contents' => $page_contents
	);
	
	$logRecord = PageChangesLog($page_contents, $id, $activityid, $page_id);
	
	$json = "{\"tasks\": [";
	$json .= "{\"task\": \"activity.page.create.update\", \"id\":\"" . $id . "\", \"activityid\":\"" . $activityid . "\", \"definition\":" . json_encode($def, JSON_FORCE_OBJECT) . "}";
	$json .= "]}";

	$results = api_short(SERVICE_MODEL, $json);
	echo json_encode($results->results[0]);

} else {
	echo "{}";
}
?>