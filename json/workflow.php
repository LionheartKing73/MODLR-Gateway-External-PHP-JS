<?

include_once("../lib/lib.php");
header("Content-type: application/json");
ini_set("auto_detect_line_endings", true);


//process querystring arguments
$action = querystring("action");
$id = querystring("id");
$workflowid = querystring("workflowid");

$json = "{\"tasks\": [";
$json .= "{\"task\": \"workflow.get\", \"workflowid\":\"" . $workflowid . "\", \"id\":\"" . $id . "\"}";
$json .= "]}";

$results = api_short(SERVICE_MODEL, $json);
echo json_encode($results->results[0]);

?>