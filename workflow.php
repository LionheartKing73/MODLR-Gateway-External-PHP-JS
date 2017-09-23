<?
include_once("lib/lib.php");


$id = querystring("id");
$workflowid = querystring("workflowid");
$action = querystring("action");
$name = querystring("name");

if( $id == "" ) {
	redirectToPage ("/home/");
	die();
}

if( $action == "create" ) {
	$json = "{\"tasks\": [";
	$json .= "{\"task\": \"workflow.create\", \"name\":\"" . $name . "\", \"id\":\"" . $id . "\"}";
	$json .= "]}";
	
	$results = api_short(SERVICE_MODEL, $json);
	
	
	if( intval($results->results[0]->result) == 1 ) { 
		$workflowid = $results->results[0]->id;
		redirectToPage ("/workflow/?id=" . $id . "&workflowid=" . $workflowid);
		die();
	} else {
		redirectToPage ("/home/");
		die();
	}
} else if( $action == "rename" ) {
	$json = "{\"tasks\": [";
	$json .= "{\"task\": \"workflow.rename\", \"name\":\"" . $name . "\", \"id\":\"" . $id . "\",\"workflowid\":\"" . $workflowid . "\"}";
	$json .= "]}";
	
	$results = api_short(SERVICE_MODEL, $json);
	
	redirectToPage ("/workflow/?id=" . $id . "&workflowid=" . $workflowid);
	die();
} else if( $action == "setVariable" ) {

	$variable = querystring("variable");

	$json = "{\"tasks\": [";
	$json .= "{\"task\": \"workflow.set.variable\", \"name\":\"" . $name . "\", \"id\":\"" . $id . "\",\"workflowid\":\"" . $workflowid . "\",\"variable\":\"" . $variable . "\"}";
	$json .= "]}";
	
	$results = api_short(SERVICE_MODEL, $json);
	
	redirectToPage ("/workflow/?id=" . $id . "&workflowid=" . $workflowid);
	die();
}

$workflow = null;
if( $workflowid == "" ) {
	redirectToPage ("/home/");
	die();
} else {
	
	$json = "{\"tasks\": [";
	$json .= "{\"task\": \"model.get\", \"id\":\"" . $id . "\"},";
	$json .= "{\"task\": \"workflow.get\", \"workflowid\":\"" . $workflowid . "\", \"id\":\"" . $id . "\"}";
	$json .= "]}";
	
	$results = api_short(SERVICE_MODEL, $json);
	
	$model_contents = $results->results[0]->model;
	$model_name = $model_contents->name;
		
		
	$workflow = $results->results[1];
	
}


include_once("lib/header.php");
?>
    	<title>MODLR » Manage Workflow</title>
		<script>
			var modelid = '<? echo $id;?>';
			var workflowid = '<? echo $workflowid;?>';
		</script>
		<style>
		tr.form-row {
			height: 32px;
		}
		input.form-control {
			height: 27px;
		}
		select.form-control {
			height: 27px;
		}
		</style>
		
<?
include_once("lib/body_start.php");
outputModelToolbar($id, $model_name);
?>

			<div class="row">
					
					<div class="col-md-8">
						<div class="panel">
							<div class="panel-body">
								<h4>Workflow Editor: <? echo $workflow->name;?></h4>
								
							</div>
						</div>
					</div>
					
					<div class="col-md-4">
						<div class="panel">
							<div class="panel-body" style="padding-top: 20px;">
								<center>
									<button class="btn btn-success" onclick="addTask();"><a style='color:white;'>Add Item</a></button>&nbsp;
									<button class="btn btn-success" onclick="renameWorkflowPrompt();"><a style='color:white;'>Rename</a></button>&nbsp;
									<button class="btn btn-success" onclick="setVariablePrompt();"><a style='color:white;'>Set Variable</a></button>&nbsp;
									<button class="btn btn-danger" onclick="deleteWorkflow('<? echo $workflow->id;?>','<? echo $workflow->name;?>');"><a style='color:white;'>Delete</a></button>
								</center>
							</div>
						</div>
					</div>
					
			</div>
			<div class="row">
			
				<div class="col-lg-12">
					<section class="panel">
						<header class="panel-heading">
							Workflow Instructions
						</header>
						<div class="panel-body" style="height:300px;overflow-y:scroll;">
							
							
							<table class="table table-striped workview-table object-table">
								<thead>
									<tr>
										<th>Name</th><th class="text-right" style='width:166px;padding-right:22px !important;'>Actions</th>
									</tr>
								</thead><tbody id='workflow-items'>

								</tbody>
							</table>
							
		
						</div>
					</section>
				</div>
			</div>
					
<?
include_once("lib/body_end.php");
?>

<div id="dialog-rename-workflow" title="Rename Workflow" style='display:none;'>
	<form role="form" style='' id='workflow_create'>
		<table width='100%' cellspacing='0' cellpadding='0'>
			<tr>
				<td>
					<input type='text' class="form-control" name='workflowNewName' id='workflowNewName' style="width:400px;" placeholder='Workflow Name' value='<? echo $workflow->name;?>'/>
				</td>
				<td style='text-align:right;'>
					<button type="button" onclick='workflowRename("<? echo $id;?>","<? echo $workflowid;?>");' style="width:80px;" class="btn btn-success btn-default">Rename</button>
				</td>
			</tr>
		</table>
	</form>
</div>

<div id="dialog-variable-workflow" title="Set Workflow Variable" style='display:none;'>
	<form role="form" style='' id='workflow_create'>
		<table width='100%' cellspacing='0' cellpadding='0'>
			<tr>
				<td>
					<select name='workflowVariable' id='workflowVariable' style="width:400px;" class="form-control">
						<option value=''>None</option>
<?
$variables = $model_contents->variables;
for($i=0;$i<count($variables);$i++) {
	$variable = $variables[$i];
	if( $workflow->variable == $variable->key ) {
		echo "<option value='".$variable->key."' selected>".$variable->key."</option>";	
	} else {
		echo "<option value='".$variable->key."'>".$variable->key."</option>";
	}
}
?>
					</select>
				</td>
				<td style='text-align:right;'>
					<button type="button" onclick='workflowSetVariable("<? echo $id;?>","<? echo $workflowid;?>");' style="width:80px;" class="btn btn-success btn-default">Save</button>
				</td>
			</tr>
		</table>
	</form>
</div>

<div id="dialog-item" title="Add / Update Workflow Item" style='display:none;'>
	<form role="form" style='' id='workflow_item'>
		<table width='100%' cellspacing='0' cellpadding='0'>
			<tr class='form-row'>
				<td style='width:100px;'>
					<b>Title:</b>
				</td>
				<td>
					<input type='text' class="form-control" name='item_title' id='item_title' placeholder='Title'  value=''/>
				</td>
			</tr>
			<tr class='form-row'>
				<td>
					<b>Link:</b>
				</td>
				<td>
					<input type='text' class="form-control" name='item_link' id='item_link' placeholder='Link'  value=''/>
				</td>
			</tr>
			<!--
			<tr class='form-row'>
				<td>
					<b>Video:</b>
				</td>
				<td>
					<input type='text' class="form-control" name='item_video' id='item_video' placeholder='YouTube Video Link'  value=''/>
				</td>
			</tr>
			-->
			<tr class='form-row'>
				<td>
					<b>Workview:</b>
				</td>
				<td>
					<select name='item_workview' id='item_workview' class="form-control">
						<option value=''>None</option>
<?
$views = $model_contents->workviews;
for($i=0;$i<count($views);$i++) {
	$view = $views[$i];
	echo "<option value='".$view->id."'>".$view->name."</option>";
}
?>
					</select>
				</td>
			</tr>
			<tr class='form-row'>
				<td>
					<b>Process:</b>
				</td>
				<td>
					<select name='item_process' id='item_process' class="form-control">
						<option value=''>None</option>
<?
$processes = $model_contents->processes;
for($i=0;$i<count($processes);$i++) {
	$process = $processes[$i];
	echo "<option value='".$process->processid."'>".$process->name."</option>";
}
?>
					</select>
				</td>
			</tr>
			<tr class='form-row'>
				<td colspan='2' align='right'>
					<button type="button" onclick='saveTask();' class="btn btn-success btn-xs btn-default">Save</button>&nbsp;
					<button type="button" onclick='closeTask();' class="btn btn-danger btn-xs btn-default">Close</button>&nbsp;
				</td>
			</tr>
		</table>
	</form>
</div>

<div id="dlgDeleteItem" title="Delete Object" style='display:none;'>
	<span id='confirmParagraph'></span>
</div>
<script src="/js/service/workflow.js"></script>
<?
include_once("lib/footer.php");
?>
