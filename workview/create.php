<?
include_once("../lib/lib.php");

//~~~~~~~~~~~~~~~~~~~~~~~~~~ Find the specified model and load it.
$id = querystring("id");
$workviewid = querystring("workviewid");
$results = null;
if( $id != "" ) {
	echo "<!-- model id provided -->\r\n";
	$json = "{\"tasks\": [";
	$json .= "{\"task\": \"model.get\", \"id\":\"" . $id . "\"}";
	$json .= "]}";
	$results = api_short(SERVICE_MODEL, $json);

	if( intval($results->results[0]->result) == 1 ) { 
		echo "<!-- model id found in server -->\r\n";
		
		$model_contents = $results->results[0]->model;
		$name = $model_contents->name;

		$json = "{\"tasks\": [";
		$json .= "{\"task\": \"home.directory\"}";
		$json .= "]}";
		$datasources = api_short(SERVICE_SERVER, $json);
	
		//define the object properties.
		$target_cube = "";
		$target_workview = "";
		$action = "";
		
		$workview_load = null;
		if( $workviewid != null ) {
			$json = "{\"tasks\": [";
			$json .= "{\"task\": \"workview.get\", \"id\": \"" .$id. "\", \"workviewid\": \"" .$workviewid. "\"}";
			$json .= "]}";
			
			$workview_load = api_short(SERVICE_MODEL, $json);
			$workview_load = $workview_load->results[0]->process;
			
			//load the object properties.
			$action = $workview_load->action;
			$target_cube = $workview_load->target_cube;
			$target_workview = $workview_load->target_workview;
		}
		
		
	} else {
		header("Location: /home/");
		die();
	}
	
	
} else {
	header("Location: /home/");
	die();
}

include_once("../lib/header.php");
?>
		<title>MODLR » <? echo $name;?> » Create Workview</title>
<?

include_once("../lib/body_start.php");

outputModelToolbar($id, $name);
?>

				

				<div class="row">
					<div class="col-lg-12">
						<section class="panel">
							<header class="panel-heading">
								Create a new Workview
							</header>
							<div class="panel-body">
								<div class="position-center">
                            

									<form action="#" class="form-horizontal">

										<div class="form-group">
											<label for="select1" class="col-lg-2 control-label">Action</label>
											<div class="col-lg-10">
												<select class="form-control" id='list_action' onChange="methodChange();">
	<?
	if( $workview_load != null ) {
		if( trim(strtolower($action)) == trim(strtolower("Create a new workview on an existing cube")) ) {
			echo "<option selected>Create a new workview on an existing cube</option><option>Create a new workview on a new cube</option>";
		} else {
			echo "<option>Create a new workview on an existing cube</option><option selected>Create a new workview on a new cube</option>";
		}
	} else {
	?>
		<option>Create a new workview on an existing cube</option><option selected>Create a new workview on a new cube</option>										
	<?
	}
	?>
												</select>
											</div>
										</div>
									
										<div class="form-group" id='workviewBlock'>
											<label for="input1" class="col-lg-2 control-label">Workview</label>
											<div class="col-lg-10">
												<input type="input" class="form-control" id="target_workview" name="target_workview" value="<? echo $target_workview;?>" placeholder="New Workview Name" onChange='textUpdate(this);' />
											</div>
										</div>
									
										<div class="form-group" id='cubeBlock'>
											<label for="input1" class="col-lg-2 control-label">Cube:</label>
											<div class="col-lg-10">
												<input type="input" class="form-control" id="target_cube" name="target_cube" value="<? echo $target_cube;?>" placeholder="New Cube Name" onChange='textUpdate(this);' />
											</div>
										</div>
									
									
										<div class="form-group" id='cubeBlockNotes'>
											<label for="input1" class="col-lg-2 control-label">Note:</label>
											<div class="col-lg-10">
												<span class="help-block">A new cube will be created with standard dimensions such as Time, Scenario and Measures. Within the workview developer you will be able to add dimensions and configure existing ones.</span>
											</div>
										</div>
									
										<div class="form-group" id='cubeExistingBlock' style='display:none;'>
											<label for="input1" class="col-lg-2 control-label">Cube:</label>
											<div class="col-lg-10">
												<select class="form-control" id="target_existing_cube" onChange="cubeChange();">
	<?
	//$contents
	$cubes = $model_contents->cubes;
	for($i=0;$i<count($cubes);$i++) {
		$cube = $cubes[$i];
	
		$selected = "";
		if( strToLower($cube->name) == strToLower($target_cube) ) {
			$selected = " selected";
		}
		echo "<option value='".$cube->id."'".$selected.">".$cube->name."</option>";
	
	}
	?>
												</select>
											
											</div>
										</div>
									
										<div class="form-group">
											<div class="col-lg-offset-2 col-lg-10">
												<button class="btn btn-primary" type='button' onclick="createWorkView();">Create</button>
												<span class="btn btn-primary" onclick="window.location='/model/?id=<? echo $id;?>';">Cancel</span>
											</div>
										</div>
									
										<div class="form-group" id='cubeBlockNotes'>
											<label for="input1" class="col-lg-2 control-label">Note:</label>
											<div class="col-lg-10">
												<span class="help-block">
													Workviews are effectively windows through to a specific slice of a cube. When configuring a workview, the underlying cube will only be effected if you perform one of the following actions:
													<ul>
														<li>Add or Remove a dimension from the target cube.</li>
														<li>Add items into a dimension at the bottom most level.</li>
													</ul>
													In both of these cases the editor will alert you that your change will affect the structure of the underlying cube long with the resulting implications.
												</span>
											</div>
										</div>
									
									</form>
								
								

								</div>
							</div>
						</section>
					</div>
					<!-- /Basic forms -->

					
				</div>
				
<div id="dialog-confirm-overwrite" title="Build Items" style='display:none;'>
	<p>Please review the build items carefully as this may include the removal of objects which coincidentally have the same name.</p>
	<b>Build Items:</b><br/>
	<div id='process_actions'  class="list-group">
	</div>
</div>
				
<?
include_once("../lib/body_end.php");
?>
<script>
var workview_definition_loaded = null;
<?
if( $workview_load != null ) {
	echo "var workview_definition_loaded = ".json_encode($workview_load).";\r\n";
}
echo "var model_detail = ".json_encode($model_contents).";\r\n";
?>

var serviceDataName = "<? echo SERVICE_DATASOURCE;?>";
var serviceModelName = "<? echo SERVICE_MODEL;?>";
var server_id = <? echo session("active_server_id");?>;
</script>

	<!-- Pandora Library -->
	<script src="/js/service/workview.js"></script>
	
	
<?
include_once("../lib/footer.php");
?>