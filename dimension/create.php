<?
include_once("../lib/lib.php");
$model_contents = null;

if( session("role") != "MODELLER" ) {
	header("Location: /home/");
}

$action = querystring("action");
$id = querystring("id");
$model = null;
$error_message = "";

if( $id != "" ) {
	
	echo "<!-- model id provided -->\r\n";

	$json = "{\"tasks\": [";
	$json .= "{\"task\": \"model.get\", \"id\":\"" . $id . "\"}";
	$json .= "]}";

	$results = api_short(SERVICE_MODEL, $json);
	
	if( intval($results->results[0]->result) == 1 ) { 
		echo "<!-- model id found in server -->\r\n";
		
		$model_contents = $results->results[0]->model;
		$model = $model_contents;
        $name = $model_contents->name;
		$dimensions = $model_contents->dimensions;
		$existing_dimension_names = array();
		foreach ($dimensions as $dim){
			array_push($existing_dimension_names, strtolower($dim->name));
		}
	} else {
		//model not found
		
		redirectToPage ("/home/");
		die();
	}
} else {
	redirectToPage ("/home/");
	die();
}

if (isset($_POST['dimension_name'])){
	$dimension_name = form("dimension_name");
	$dimension_type = form("dimension_name_select");
	$json = "{\"tasks\": [";
	$json .= "{\"task\": \"dimension.create\", \"id\" : \"".$id."\", \"name\" : \"".$dimension_name."\", \"type\" : \"".$dimension_type."\"}] }";
	$json .= "]}";
	$results = api_short(SERVICE_MODEL, $json);
	if (intval($results->results[0]->result) == 0){
		$error_message = $results->results[0]->message;
	}
    else {
		redirectToPage("/dimension/manage/?id=".$id."&dimension_id=".$results->results[0]->id);
	}	

}

include_once("../lib/header.php");

echo "<title>MODLR » Manage » ".$name." - Create Dimension</title>";

?>		

		<style>
		.cell {
			border:0px;
			padding: 5px;
		}
		.cellHeading {
			padding: 5px;
			background-color:#999;
			font-weight:bold;
			color:white;
			border:0px;
		}
		.c {
			min-width: 10px;
			max-width: 380px;
			font-size: 12px;
			border:1px solid #EEE;
			padding:4px;
			cursor:pointer;
			text-align: right;
			/* background-color: #fff; */
		}
	</style>

	<script>
<?
		echo "	var model_detail = ".json_encode($model_contents).";\r\n";
?>
	</script>
	<style type='text/css'>
		.ui-selecting { background: #6dba89 !important; }
		.ui-selected { background-color: #6dba89 !important; color:#FFF !important; }
		#displayVariableValue {word-break: break-all;}
	</style>
<?
include_once("../lib/body_start.php");

outputModelToolbar($id, $name);
$model_name = $name;

?>

				<div class="row">
					
					<!-- Basic forms -->
					<div class="col-lg-6">
						<section class="panel">
							<header class="panel-heading">
								Manage <? echo $name;?> Model
							</header>
							<div class="panel-body">

							<form action="#" class="form-horizontal" id = "create_dimension_form" method = "POST">
								<div id = 'error_message' class = 'alert alert-danger' style = "display: <? echo ($error_message != "" ? "block" : "none");?>">
								<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
								<?
									echo $error_message;
								?>
								</div>
								<div id = 'warning_message' style='display:none;'>
								</div>
								<div class="form-group" id='dimensionBlock'>
									<label for="dimension_name" class="col-lg-3 control-label">Dimension Name:</label>
									<div class="col-lg-9">
										<input type="input" style = "width: 300px;" class="form-control" id="dimension_name" name="dimension_name"  value="New Dimension" placeholder="New Dimension" required="true"  />
									</div>
								</div>
								<div class="form-group" id='dimensionType'>
									<label for="dimension_name_select" class="col-lg-3 control-label">Dimension Type:</label>
									<div class="col-lg-6">
									<select class="form-control"  style = "width: 300px;"  name = "dimension_name_select" id="dimension_name_select">
										<option value ="standard">Standard</option>
										<option value ="time">Time</option>
										<option value ="scenario">Scenario</option>
										<option value ="measure">Measure</option>
										<option value ="Geography">Geography</option>
									</select>
									</div>
								</div>
								<div class="form-group" style = "margin-top: 10px;">
										<div class="col-lg-offset-3 col-lg-10">
											<button class="btn btn-primary" type='button' onclick="createDimension();">Create</button>
											<span class="btn btn-primary" onclick="window.location='/model/?id=<? echo $id;?>';">Cancel</span>
											<input type = "submit" name = "create_dimension_btn" id ="create_dimension_btn" style = "display: none;"/>
										</div>
								</div>
								</form>

<?
include_once("../lib/body_end.php");
?>

<script>
function createDimension(){
	var existing_dimension = <? echo json_encode($existing_dimension_names); ?>;
	var dimension_name = $("#dimension_name").val().toLowerCase();
	var is_dim_name_existing = true;
	if (jQuery.inArray(dimension_name, existing_dimension) == -1 && dimension_name.length > 0){
		is_dim_name_existing = false;
		$("#dimension_name").css('border-color', '');
		$("#warning_message").css('display', 'none');
		$("#create_dimension_form").submit();
	}
	if (is_dim_name_existing || dimension_name.length == 0 ) {
		$("#warning_message").css('display', 'block');
		$("#warning_message").addClass('alert alert-danger');
		$("#warning_message").html('<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span> Please pick another dimension name');		
		$("#dimension_name").css('border-color', 'red');
	}
}
</script>

<?
include_once("../lib/footer.php");
?>