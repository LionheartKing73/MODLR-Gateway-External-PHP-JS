<?
include_once("lib/lib.php");
$model_contents = null;

if( session("role") != "MODELLER" ) {
	header("Location: /home/");
}

$model_name = form("txtNewModel");
if( $model_name != "" ) {
	
	$list_granularity = form("list_granularity");
	$list_financialyear = form("list_financialyear");
	$list_monthbuild = form("list_monthbuild");
	
	//"timegranularity","timemonthbuild","timefinancialyear"
	
	
	$json = "{\"tasks\": [";
	$json .= "{\"task\": \"model.create\", \"name\":\"" . $model_name . "\", \"timegranularity\":\"" . $list_granularity . "\", \"timemonthbuild\":\"" . $list_monthbuild. "\", \"timefinancialyear\":\"" . $list_financialyear  . "\"}";
	$json .= "]}";
	
	$results = api_short(SERVICE_MODEL, $json);
	
	
	if( intval($results->results[0]->result) == 1 ) { 
		$id = $results->results[0]->id;
		redirectToPage ("/model/?id=" . $id);
		die();
	} else {
		
		
		
	}
	
}

$action = querystring("action");
$id = querystring("id");
$model = null;
$usersHidden = array();

if( $id != "" ) {
	
	if( $action == "delete_view" ) {
		
		$workview = querystring("workview");
		$json = "{\"tasks\": [";
		$json .= "{\"task\": \"workview.delete\", \"id\":\"" . $id . "\", \"workviewid\":\"" . $workview . "\"}";
		$json .= "]}";

		$results = api_short(SERVICE_MODEL, $json);
	
	}
	
	
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
		
		if( property_exists( $model_contents, 'hide' ) ) {
			$usersHidden = $model_contents->hide;
		}
		
		$mode = "edit";
		
		
	} else {
		//model not found
		
		echo "<!-- ".$json." -->";
		redirectToPage ("/home/");
		die();
	}
} else {
	redirectToPage ("/home/");
	die();
}

include_once("lib/header.php");

if( $id == "" ) { 
	echo "<title>MODLR » Create a Model</title>";
} else {
	echo "<title>MODLR » ".$name."</title>";
}

?>
	<script>
<?
		echo "	var model_detail = ".json_encode($model_contents).";\r\n";
?>
	</script>
	<style type='text/css'>
		.ui-selecting { background: #6dba89 !important; }
		.ui-selected { background-color: #6dba89 !important; color:#FFF !important; }
		
		.parent_workviews {
			margin-bottom:5px;
			cursor:pointer;
		}
		.parent_cubes {
			margin-bottom:5px;
			cursor:pointer;
		}
		.parent_dimensions {
			margin-bottom:5px;
			cursor:pointer;
		}
		.parent_processes {
			margin-bottom:5px;
			cursor:pointer;
		}
		.parent_schedules {
			margin-bottom:5px;
			cursor:pointer;
		}
		
		.fa {
			top: 2px;
			position: relative;
		}
		.child_leaf {
			  margin-left: 5px;
			  padding-top: 4px;
			  cursor: pointer;
			  padding-left: 12px;
			  border-left: 1px dotted #666;
		}
		.model_view:before {
			  display: inline-block;
			  content: "";
			  position: relative;
			  top: -2px;
			  left: -5px;
			  width: 10px;
			  height: 0;
			  border-top: 1px dotted #666;
			  z-index: 1;
			  margin: 0px;
			  padding: 0px;
		}
		
		.ui-accordion .ui-accordion-content {
		  padding: 0px;
		  border-top: 0;
		  overflow-y: scroll;
		  height:320px;
		}
		
		table.object-table {
			font-size:11px;
			/*width: 99%;*/
		}
		
		.object-row {
			padding: 4px !important;
			height:20px;
			padding-left:6px;
		}
		
		span.object-name {
			margin-top: 3px;
			margin-left: 5px;
			display: inline-block;
		}
		
		.accordion ::selection {
			background: initial;
			color: initial;
		}
		
		.ui-state-active,
		.ui-widget-content .ui-state-active,
		.ui-widget-header .ui-state-active {
			background: #e6e6e6  url(/css/modeler_theme/images/ui-bg_glass_75_e6e6e6_1x400.png) 50% 50% !important;
		}
		.ui-widget-content {
			border: 1px solid #e6e6e6;
		}
		
		ul.tasklist {
			padding-top:8px;
			padding-left:8px;  
			padding-right: 23px;
			margin-bottom:4px;
		}
		.tasklist li {
			background: #f3f3f3;
			-webkit-border-radius: 3px;
			-moz-border-radius: 3px;
			border-radius: 3px;
			position: relative;
			padding: 13px;
			margin-bottom: 5px;
			list-style: none;
		}
		
		span.tasklist  {
			  -webkit-border-radius: 3px;
			  -moz-border-radius: 3px;
			  border-radius: 3px;
			  padding-left: 3px;
			  padding-right: 2px;
			  padding-bottom: 2px;
			  border: 1px solid #999;
			  background-color: #fff;
			  float: left;
			  cursor:pointer;
		}
		
		.tasklist p {
			margin-left: 50px;
		}
		span.counter {
			font-size: 16px;
			float: left;
		}
		
		.blank {
			visibility:hidden;
		}
	</style>
<?
	
	
	
include_once("lib/body_start.php");

function cubeById($model_contents, $cubeId) {
	$cubes = $model_contents->cubes;
	for($i=0;$i<count($cubes);$i++) {
		$cube = $cubes[$i];
		if( $cube->id == $cubeId ) {
			return $cube;
		}
	}
	return null;
}

function cleanChars($str) {
	return mysql_real_escape_string($str);
}
function cleanCharsEncode($str) {
	return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}


if( $id != "" ) {
	outputModelToolbar($id, $name);
}

if( $id != "" ) {

?>


				<div class="row">
					
					
					<div class="col-md-6">
						<div class="panel">
						<div class="panel-body">
							<h4>Overview</h4>
							
							<div class="accordion"  id="accordion">
								
								
								<h3><img src='/img/icons/16/table-dimensions.png'/>&nbsp;Workviews&nbsp;(Reports)</h3>
								<div class="tab-pane" id="workviews" style='height: 367px;overflow-y: scroll;overflow-x: hidden;'>

									<table class="table table-striped workview-table object-table">
										<thead>
											<tr>
												<th>Name</th><th>Cube</th><th class="text-right" style='width:116px;padding-right:22px !important;'>Actions</th>
											</tr>
										</thead><tbody>
<?
//$contents
$views = $model_contents->workviews;
for($i=0;$i<count($views);$i++) {
$view = $views[$i];

$url = '/workview/editor/?id=' .$id. '&workview=' . $view->id;		
$ondblclick = "ondblclick='window.open(\"".$url."\", \"_blank\");'";
$onclick = "onclick='window.open(\"".$url."\", \"_blank\");'";

$cube = cubeById($model_contents, $view->cube);

?>
											<tr  class='workview-entry' data-workview='<? echo $view->id;?>' data-model='<? echo $id;?>'>
												<td class='workview-name object-row' <? echo $ondblclick;?> style='cursor:pointer;'><img src='/img/icons/16/table.png'/>&nbsp;<span class='object-name'><? echo cleanCharsEncode($view->name);?></span></td>
												<td class='object-row'><? echo cleanCharsEncode($cube->name);?></td>
												<td class="text-right workview-name object-row" style="padding-right:22px !important;">
													<button class="btn btn-xs btn-success" <? echo $onclick;?>>Open</button><!--</a>-->
													<a style='color:white;' onclick="deleteItem('Workview','<? echo $view->id;?>','<? echo cleanChars($view->name);?>')" href="#"><button class="btn btn-xs btn-danger">Delete</button></a>
											</td></tr>
<?
}
?>
										</tbody>
									</table>

								</div>
								
								<h3><img src='/img/icons/16/diamond-dimensions.png'/>&nbsp;Cubes</h3>
								<div class="tab-pane" id="cubes" style='height: 367px;overflow-y: scroll;overflow-x: hidden;'>

									<table class="table table-striped object-table">
										<thead>
											<tr>
												<th style='  width: 180px;'>Name</th><th>Dimensions</th><th class="text-right"  style="padding-right:22px !important;width: 130px;">Actions</th>
											</tr>
										</thead><tbody>
<?
//$contents
$cubes = $model_contents->cubes;
for($i=0;$i<count($cubes);$i++) {
$cube = $cubes[$i];	
$url = '/workview/editor/?id=' .$id. '&cube=' . $cube->id;	
$ondblclick = "ondblclick='window.open(\"".$url."\", \"_blank\");'";
$onclick = "onclick='window.open(\"".$url."\", \"_blank\");'";
?>
											<tr>
												<td class='object-row' style='cursor:pointer;' <? echo $ondblclick;?>><img src='/img/icons/16/diamond.png'/>&nbsp;<span class='object-name' ><? echo cleanCharsEncode($cube->name);?></span></td>
												<td class='object-row'>
<?
for($k=0;$k<count($cube->dimensions);$k++) {
echo cleanCharsEncode($cube->dimensions[$k]->name);
if( $k < count($cube->dimensions)-1 ) {
	echo ", ";
}
}
?>
												</td>
												<td class="text-right object-row" style="font-size: 10px;padding-right:22px !important;">
													<button class="btn btn-xs btn-success"><a style='color:white;' <? echo $onclick;?>>Open</a></button>
													<a style='color:white;' onclick="deleteItem('Cube','<? echo $cube->id;?>','<? echo cleanChars($cube->name);?>');" href="#"><button class="btn btn-xs btn-danger">Delete</button></a>
											</td></tr>
<?
}
?>
										</tbody>
									</table>

								</div>
								
								
								<h3><img src='/img/icons/16/block.png'/>&nbsp;Dimensions</h3>
								<div class="tab-pane" id="dimensions" style='height: 367px;overflow-y: scroll;overflow-x: hidden;'>
	
									<table class="table table-striped object-table">
										<thead>
											<tr>
												<th>Name</th><th class="text-right" style="padding-right:22px !important;">Actions</th>
											</tr>
										</thead><tbody>
<?
//$contents
$dimensions = $model_contents->dimensions;
for($i=0;$i<count($dimensions);$i++) {
$dimension = $dimensions[$i];
?>
											<tr >
												<td class='object-row'><img src='/img/icons/16/block.png'/>&nbsp;<span class='object-name'><? echo cleanCharsEncode($dimension->name);?></span></td>
												<td class="text-right object-row" style="font-size: 10px;padding-right:22px !important;">
													<!-- <button class="btn btn-xs btn-success"><a style='color:white;' href="/cubes/browse/?id=<? echo $id;?>&id=<? echo $dimension->id;?>">Open</a></button> -->
													<a style='color:white;' onclick="deleteItem('Dimension','<? echo $dimension->id;?>','<? echo cleanChars($dimension->name);?>')" href="#"><button class="btn btn-xs btn-danger">Delete</button></a>
											</td></tr>
<?
}
?>
										</tbody>
									</table>
								</div>
								
								
								<h3><img src='/img/icons/16/script-text.png'/>&nbsp;Processes</h3>
								<div class="tab-pane" id="processes" style="height: 367px;overflow-y: scroll;overflow-x: hidden;">
									<table class="table table-striped object-table">
										<thead>
											<tr>
												<th>Name</th><th>Datasource</th><th class="text-right"  style="padding-right:22px !important;">Actions</th>
											</tr>
										</thead><tbody>
<?
//$contents
$processes = $model_contents->processes;
for($i=0;$i<count($processes);$i++) {
$process = $processes[$i];



?>
											<tr><td class='object-row'><img src='/img/icons/16/script-text.png'/>&nbsp;<span class='object-name'><? echo cleanCharsEncode($process->name);?></span></td>
												<td class='object-row'><? echo cleanChars($process->datasource_name);?></td>
												<td class="text-right object-row" style="font-size: 10px;padding-right:22px !important;">
													<a style='color:white;' href="/process/create/?id=<? echo $id;?>&processid=<? echo $process->processid;?>"><button class="btn btn-xs btn-success">Edit</button></a>
													<button class="btn btn-xs btn-warning" onclick="executeProcess('<? echo $id;?>','<? echo $process->processid;?>');">Execute</button>
													<button class="btn btn-xs btn-danger" onclick="deleteItem('Process','<? echo $process->processid;?>','<? echo cleanChars($process->name);?>')"><a style='color:white;' href="#">Delete</a></button>
											</td></tr>
<?
}
?>
										</tbody>
									</table>

								</div>
								
								
								<h3><img src='/img/icons/16/table.png'/>&nbsp;Tables</h3>
								<div class="tab-pane" id="tables" style="height: 367px;overflow-y: scroll;overflow-x: hidden;">
									<table class="table table-striped object-table">
										<thead>
											<tr>
												<th>Name</th><th>Database</th><th class="text-right"  style="padding-right:22px !important;">Actions</th>
											</tr>
										</thead><tbody>
<?
//$contents
$tables = $model_contents->tables;
for($i=0;$i<count($tables);$i++) {
$table = $tables[$i];



?>
											<tr><td class='object-row'><img src='/img/icons/16/table.png'/>&nbsp;<span class='object-name'><? echo cleanCharsEncode($table->name);?></span></td>
												<td class='object-row'><? echo cleanChars($table->database);?></td>
												<td class="text-right object-row" style="font-size: 10px;padding-right:22px !important;">
													<a style='color:white;' href="/table/?id=<? echo $id;?>&tableid=<? echo $table->id;?>"><button class="btn btn-xs btn-success">Edit</button></a>
													<button class="btn btn-xs btn-danger" onclick="deleteItem('Table','<? echo $table->id;?>','<? echo cleanChars($table->name);?>')"><a style='color:white;' href="#">Delete</a></button>
											</td></tr>
<?
}
?>
										</tbody>
									</table>

								</div>
								
								
								<h3><img src='/img/icons/16/function.png'/>&nbsp;Variables</h3>
								<div class="tab-pane" id="workviews" style='height: 367px;overflow-y: scroll;overflow-x: hidden;'>

									<table class="table table-striped workview-table object-table">
										<thead>
											<tr>
												<th>Name</th><th>Value</th><th class="text-right" style='width:116px;padding-right:22px !important;'>Actions</th>
											</tr>
										</thead><tbody>
<?

for($i=0;$i<count($model_contents->variables);$i++) {
	$var = $model_contents->variables[$i];
?>
											<tr>
												<td class='object-row' style='cursor:pointer;' ><img src='/img/icons/16/function.png'/>&nbsp;<span class='object-name' ><? echo cleanCharsEncode($var->key);?></span></td>
												<td class='object-row'><? echo $var->value;?></td>
												<td class="text-right object-row" style="font-size: 10px;padding-right:22px !important;">
													<a style='color:white;' onclick="deleteItem('Variable','<? echo $var->key;?>','<? echo $var->key;?>')" href="#"><button class="btn btn-xs btn-danger">Delete</button></a>
											</td></tr>
<?
}
?>
										</tbody>
									</table>

								</div>
								
								<h3><img src='/img/icons/16/clock.png'/>&nbsp;Schedules</h3>
								<div class="tab-pane" id="cubes" style='height: 367px;overflow-y: scroll;overflow-x: hidden;'>

									<table class="table table-striped object-table">
										<thead>
											<tr>
												<th style='  width: 180px;'>Process</th><th>Schedule</th><th class="text-right"  style="padding-right:22px !important;width: 130px;">Actions</th>
											</tr>
										</thead><tbody>
<?

function patternToString($pattern) {
	$comp = explode(" ",$pattern);
	$str = "";
	
	for($i=0;$i<count($comp);$i++) {
		$pair = explode(":",$comp[$i]);
		if( $pair[1] != "*" ) {
			if( $str == "" ) {
				$str .= "On ";
			} else {
				$str .= ", At ";
			}
			if( $pair[0] == "Mth" ) {
				$str .= "Month ".$pair[1];
			} else if( $pair[0] == "Day" ) {
				$str .= "Day ".$pair[1];
			} else if( $pair[0] == "Hr" ) {
				$str .= "Hour ".$pair[1];
			} else if( $pair[0] == "Min" ) {
				$str .= "Minute ".$pair[1];
			} else if( $pair[0] == "Wd" ) {
				$str .= "Weekday ".$pair[1];
			}
			
			
		}		
	}
	return $str;
}

for($i=0;$i<count($model_contents->schedules);$i++) {
	$var = $model_contents->schedules[$i];
	
	$pro = null;
	for($k=0;$k<count($model_contents->processes);$k++) {
		$process = $model_contents->processes[$k];
		if( $process->processid == $var->processid ) {
			$pro = $process;
			break;
		}
	}
	
	$ondblclick = "";
	
	$pattern = $var->pattern;
	$process_name = $pro->name;
	$schedule_id = $var->scheduleid;
	$schedule_desc = patternToString($pattern);
	
?>
											<tr>
												<td class='object-row' style='cursor:pointer;' <? echo $ondblclick;?>><img src='/img/icons/16/clock.png'/>&nbsp;<span class='object-name' ><? echo cleanCharsEncode($process_name);?></span></td>
												<td class='object-row'><? echo $schedule_desc;?></td>
												<td class="text-right object-row" style="font-size: 10px;padding-right:22px !important;">
													<a style='color:white;' onclick="deleteItem('Schedule','<? echo $schedule_id;?>','<? echo "Schedule: " .cleanChars($process_name);?>')" href="#"><button class="btn btn-xs btn-danger">Delete</button></a>
											</td></tr>
<?
}
?>
										</tbody>
									</table>

								</div>
							</div>
						</div>
						</div>
					</div>
					
					
					<div class="col-md-6">
						<div class="panel">
						<div class="panel-body">
							<h4>Workflow Processes</h4>
							
							<?
							if( count($model_contents->workflows) == 0 ) {
								echo "<p>There are presently no business processes within this model.</p>";
							}
							?>
							
							<div class="accordion-processes"  id="accordion-processes">
<?
for($i=0;$i<count($model_contents->workflows);$i++) {
	$workflow = $model_contents->workflows[$i];
	
	?>
	<h3><img src='/img/icons/16/task.png'/>&nbsp;<? echo $workflow->name;?></h3>
	<div class="tab-pane" id="cubes" style='height: 367px;overflow-y: scroll;overflow-x: hidden;' >
		
	
		 <ul class="tasklist">
<?
for($k=0;$k<count($workflow->tasks);$k++) {
	$task = $workflow->tasks[$k];
	
	$html = $task->title;
	
	$html_add = "";
	
	if( $task->link != "" ) {
		$url = $task->link;
		$onclick = "onclick='window.open(\"".$url."\", \"_blank\");'";
		$html_add .= '<button class="btn btn-xs btn-success" style="width: 123px;" '.$onclick.'><a style="color:white;" href="#"><img src="/img/icons/16/chain.png"/> Open Link</a></button>&nbsp;';
	}
	if( $task->workview != "" ) {
		$url = '/workview/editor/?id=' .$id. '&workview=' . $task->workview;	
		$onclick = "onclick='window.open(\"".$url."\", \"_blank\");'";
		$html_add .= '<button class="btn btn-xs btn-success" style="width: 123px;" '.$onclick.'><a style="color:white;" href="#"><img src="/img/icons/16/table.png"/> Open Workview</a></button>&nbsp;';
	}
	if( $task->process != "" ) {
		$onclick = "onclick=\"executeProcess('".$id."','".$task->process."');\"";
		$html_add .= '<button class="btn btn-xs btn-danger" style="width: 123px;" '.$onclick.'><a style="color:white;" href="#"><img src="/img/icons/16/script-text.png"/> Execute Process</a></button>&nbsp;';
	}
	
	
	$class = "fa-check blank";
	if( $task->complete == "1" ) { 
		$class = "fa-check";
	}
?>
			<li class="clearfix">
				<span class='tasklist' onclick="toggleCheckedTask('<? echo $workflow->id;?>','<? echo $task->id;?>', this);"><i class="fa <? echo $class;?>"></i></span>
				<span class="counter">&nbsp;<? echo ($k+1);?>)&nbsp;</span>
				<p class="">
					<? echo $html;?>
				</p>
<?
	if( $html_add != "" ) {
		echo "<span style='float:right;'>".$html_add."</span>";
	}
?>
			</li>
<?
}
?>

		</ul>
		<button class="btn btn-xs btn-grey" style="float: right;margin-right: 9px;margin-bottom: 9px" onclick="window.location='/workflow/?id=<? echo $id;?>&workflowid=<? echo $workflow->id;?>';"><a style="color:white;" href="#">Manage Workflow</a></button>
	
	</div>
	<?
}

?>
							</div>
						</div>
						</div>
					</div>
					
				</div>

<script>


</script>

<div id="dialog-process" title="Execute Process" style='display:none;'>
	<p>Please confirm the execution of this process.</p>
	<b>Input Required:</b><br/>
	<div id='process_parameters'  class="list-group">
	</div>
	<b>Build Items:</b><br/>
	<div id='process_actions'  class="list-group">
	</div>
</div>

<?
}

include_once("lib/body_end.php");

?>

<script>
function toggle(cls) {
	var target = $("."+cls)[0].childNodes[0];
	var targetIcon = $(target.childNodes[0]);
	var children = $("#children_of_"+cls);
	
	targetIcon.removeClass("fa-plus-square-o");
	targetIcon.removeClass("fa-minus-square-o");
	
	if( children.css("display") == "none" ) {
		children.css("display","block"); 
		targetIcon.addClass("fa-minus-square-o");
	} else {
		children.css("display","none"); 	
		targetIcon.addClass("fa-plus-square-o");
	}
	
}
$( "#accordion" ).accordion({
	collapsible: true, active: false
});
$( "#accordion-processes" ).accordion({
	collapsible: true, active: false
});

</script>

<div id="dlgDeleteItem" title="Delete Object" style='display:none;'>
	<span id='confirmParagraph'></span>
</div>



<script src="/js/service/model.js"></script>

<?
include_once("lib/footer.php");
?>
