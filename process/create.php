<?
include_once("../lib/lib.php");

//~~~~~~~~~~~~~~~~~~~~~~~~~~ Find the specified model and load it.
$id = querystring("id");
$processid = querystring("processid");
$results = null;
if( $id != "" ) {
	echo "<!-- model id provided -->\r\n";
	$json = "{\"tasks\": [";
	$json .= "{\"task\": \"model.get\", \"id\":\"" . $id . "\"}";
	$json .= "]}";
	$results = api_short(SERVICE_MODEL, $json);

	if( intval($results->results[0]->result) == 1 ) { 
		echo "<!-- model id found in server -->\r\n";
		
		$contents = $results->results[0]->model;
		$name = $contents->name;


		$json = "{\"tasks\": [";
		$json .= "{\"task\": \"home.directory\"}";
		$json .= "]}";
		$datasources = api_short(SERVICE_SERVER, $json);
	
		$target_cube = "";
		$target_dimension = "";
		$target_hierarchy = "";
		$custom_name = "";
		$datasource = "";
		$datasource_method = "";
		$datasource_sql = "";
		$datasource_table = "";
		$action = "";
		$language = "javascript";
		
		$process_load = null;
		if( $processid != null ) {
			$json = "{\"tasks\": [";
			$json .= "{\"task\": \"process.get\", \"id\": \"" .$id. "\", \"processid\": \"" .$processid. "\"}";
			$json .= "]}";
			$process_load = api_short(SERVICE_MODEL, $json);
			$process_load = $process_load->results[0]->process;
			
			$action = $process_load->action;
			
			if( isset( $process_load->language ) ) {
				$language = $process_load->language;
			}
			
			if( isset( $process_load->target_cube ) ) {
				$target_cube = $process_load->target_cube;
			}
			if( isset( $process_load->target_dimension ) ) {
				$target_dimension = $process_load->target_dimension;
			}
			if( isset( $process_load->target_hierarchy ) ) {
				$target_hierarchy = $process_load->target_hierarchy;
			}
			$custom_name = $process_load->custom_name;
			$datasource = $process_load->datasource;
			$datasource_method = $process_load->datasource_method;
			$datasource_sql = $process_load->datasource_sql;
			$datasource_table = $process_load->datasource_table;
			
			if( $datasource_sql == "" ) {
				if( isset( $process_load->query ) ) {
					$datasource_sql = $process_load->query;
				}
			}
			
			
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
		<style>
			input.dimension {
				margin-bottom:2px;
			}
		</style>
		<title>MODLR » <? echo $name;?> » Process Editor</title>
    
		<link rel="stylesheet" type="text/css" href="/js/codemirror/codemirror.css" />
		<link rel="stylesheet" href="/js/codemirror/addon/display/fullscreen.css" rel="stylesheet">
		<link rel="stylesheet" href="/js/codemirror/addon/dialog/dialog.css">
		<link rel="stylesheet" href="/js/codemirror/addon/search/matchesonscrollbar.css">
			
		<style>
		
		
			.ui-front {
				z-index: 10000;
			}
			.CodeMirror {
			  border: 1px solid #eee;
			  height: 550px;
			}
			
		</style>	
		<script>
			function goFullToggle(bFullScreen) {
				if( bFullScreen ) {
					$(".fixed-top").css("position","initial");
				} else {
					$(".fixed-top").css("position","fixed");
				}
			}
		</script>
<?

include_once("../lib/body_start.php");

outputModelToolbar($id, $name);
?>

		
				<div class="row">
				
					<div class="col-lg-6">
						<section class="panel">
							<header class="panel-heading">
								Developer Console
							</header>
							<div class="panel-body">
					
								<form action="#" class="form-horizontal">

									<div class="form-group">
										<label for="select1" class="col-lg-2 control-label">Action</label>
										<div class="col-lg-10">
											<select class="form-control" id="action" onChange="actionChange();">
<?
if( $process_load != null ) {
	if( trim(strtolower($action)) == trim(strtolower("Build a cube from data")) ) {
		echo "<option>Custom</option><option selected>Build a cube from data</option><option>Build a hierarchy from data</option>";
	} else if( trim(strtolower($action)) == trim(strtolower("Custom")) ) {
		echo "<option selected>Custom</option><option>Build a cube from data</option><option>Build a hierarchy from data</option>";
	} else {
		echo "<option>Custom</option><option>Build a cube from data</option><option selected>Build a hierarchy from data</option>";
	}
} else {
?>
	<option>Custom</option><option selected>Build a cube from data</option><option>Build a hierarchy from data</option>										
<?
}
?>
											</select>
										</div>
									</div>
									
									<div class="form-group" id='cubeBlock' style='display:none;'>
										<label for="input1" class="col-lg-2 control-label">Cube:</label>
										<div class="col-lg-10">
											<input type="input" class="form-control" id="target_cube" name="target_cube" value="<? echo $target_cube;?>" placeholder="New Cube Name" onChange='textUpdate(this);' />
										</div>
									</div>
									
									
									<div class="form-group" id='dimBlock' style='display:none;'>
										<label for="input1" class="col-lg-2 control-label">Dimension:</label>
										<div class="col-lg-10">
											<input type="input" class="form-control" id="target_dimension" name="target_dimension" value="<? echo $target_dimension;?>" placeholder="New Dimension Name"  onChange='updateDimension(this);'/>
										</div>
									</div>
									
									<div class="form-group" id='hierarchyBlock' style='display:none;'>
										<label for="input1" class="col-lg-2 control-label">Hierarchy:</label>
										<div class="col-lg-10">
											<input type="input" class="form-control" id="target_hierarchy" name="target_hierarchy" value="<? echo $target_hierarchy;?>" placeholder="New Hierarchy Name"  onChange='textUpdate(this);'/>
										</div>
									</div>
									
									<div class="form-group" id='languageBlock' style='display:none;'>
										<label for="language" class="col-lg-2 control-label">Language:</label>
										<div class="col-lg-10">
											<select class="form-control" id="language" onChange="languageChange();">
<?
if( $process_load != null ) {
	if( trim(strtolower($language)) == trim(strtolower("javascript")) ) {
		echo "<option selected value='javascript'>JavaScript</option><option value='r'>R v2.14.2</option>";
	} else if( trim(strtolower($language)) == trim(strtolower("r")) ) {
		echo "<option value='javascript'>JavaScript</option><option selected value='r'>R v2.14.2</option>";
	} else {
		echo "<option selected value='javascript'>JavaScript</option><option value='r'>R v2.14.2</option>";
	}
} else {
?>
	<option selected value='javascript'>JavaScript</option><option value='r'>R v2.14.2</option>										
<?
}
?>
											</select>
										</div>
									</div>
									
								</form>
								
								
								<p>Follow the steps below to build a component from a datasource: </p>
								<ol>
									<li>Select the Datasource.</li>
									<li>Review Data. Filter the data columns (Table selection only)</li>
									<li>Set Build Actions.</li>
									<li>Validate & Execute the process</li>
								</ol>
								<span class="help-block">Note: When building and populating a cube you will only be able to populate the bottom levels of each dimension. 
								The resulting element additional will be added to the default hierarchy only. To build alternate hierarchies you will need to do so for each dimension separately.
											</span>

							</div>
						</section>
					</div>
					<!-- /Basic forms -->

					<div class="col-lg-6">
						<section class="panel">
							<header class="panel-heading">
								1. Select the Datasource
							</header>
							<div class="panel-body">
					
								
								<h4>Datasource Selection</h4>
								<p>Select the datasource to use for this action. If the datasource you want to use is not listed below you can add it by using the "New Datasource" link under navigation. </p>
								
								<form action="#" method='post' class="form-horizontal">
								
									<div class="form-group">
										<label for="select1" class="col-lg-2 control-label">Datasource:</label>
										<div class="col-lg-10">
											<select class="form-control" id="datasource" onChange="datasourceChange();">
<?
	//todo: link to a loaded value on edit.
	$datasource = $datasource;

	
	$selected = "";
	if( "none" == strToLower($datasource) ) {
			$selected = " selected";
	}
	echo "<option value='NONE'".$selected.">None</option>";
	

	$contents = $datasources->results[0]->datasources;
	for($i=0;$i<count($contents);$i++) {
		$ds = $contents[$i];
		
		$selected = "";
		if( strToLower($ds->id) == strToLower($datasource) ) {
			$selected = " selected";
		}
		echo "<option value='".$ds->id."'".$selected.">".$ds->name."</option>";
	}
	
?>
											</select>
										</div>
									</div>
									
									
									<div class="form-group" id='methodBlock'>
										<label for="select1" class="col-lg-2 control-label">Method:</label>
										<div class="col-lg-10">
											<select class="form-control" id="datasource_method" onChange="datasourceChange();">
<?
	//todo: link to a loaded value on edit.
	$methods = array("Select a Table","Write a Query");
				
	sort($methods , SORT_STRING);
	
	for($i=0;$i<count($methods);$i++) {
		$selected = "";
		if( strToLower($methods[$i]) == strToLower($datasource_method) ) {
			$selected = " selected";
		}
		echo "<option value='".$methods[$i]."'".$selected.">".$methods[$i]."</option>";
	}

?>
											</select>
										</div>
									</div>
								
									<div class="form-group" id='tablesBlock'>
										<label id='tablesLabel' for="select1" class="col-lg-2 control-label">Table:</label>
										<div class="col-lg-10">
											<select class="form-control" id="tables" onChange="queryPreview();">

											</select>
										</div>
									</div>
									
									<div class="form-group" id='queryBlock' style='display:none;'>
										<label for="input3" class="col-lg-2 control-label">Query:</label>
										<div class="col-lg-10">
											<textarea class="form-control" rows="5" id="txtQuery" placeholder="SELECT * FROM tablename;"><? echo $datasource_sql;?></textarea>
											<span class="help-block">Note: to preview the data a limit command will be appended to the query. </span>
											<span class="btn btn-primary" onclick="queryPreview();">Preview</span>
										</div>

									</div>

									<div class="form-group" id='miscBlock'>
										<div class="col-lg-offset-2 col-lg-10">
											<div class="checkbox">
												<label>
													<input type="checkbox" id='chkOneColumn' onChange='dataTickChanged();'> Data is all on one column.
												</label>
												<span class="help-block">Note: Tick this box if you data source has a column which denotes the measure into which the system should load data. This is common with extracts from OLAP datasources. </span>
											</div>
										</div>
									</div>



									
								
								</form>
								
							</div>
						</section>
					</div>
					
					
				</div>
				
				
				<div class="row" id='review_data'>
				
					<div class="col-lg-12">
						<section class="panel">
							<header class="panel-heading">
								Review Data
							</header>
							<div class="panel-body">
							
								<p>Loaded below is a preview of the datasource you have selected above. You can now start to map this into the intended cube, dimension or hierarchy as needed. </p>
								
								<div style='overflow:scroll;width:98%;'>
									<table class="table table-striped" id='preview'>
										<thead>
										</thead>
										<tbody>
										</tbody>
									</table>

								</div>
								
								<p class="text-danger" id='previewText'></p>
								
							</div>
						</section>
					</div>
				</div>
				
				
				<div class="row" id='build_actions'>
				
					
					<div class="col-lg-12">
						<section class="panel">
							<header class="panel-heading">
								Set Build Actions
							</header>
							<div class="panel-body">
					

								<p>Loaded below is a preview of the datasource you have selected above. You can now start to map this into the intended cube, dimension or hierarchy as needed. </p>
								
								<div style='overflow:scroll;width:98%;'>
									<table class="table table-striped" id='builds'>
										<thead>
											<tr style='border:1px solid #59636d;background-color:#59636d;color:#FFF;'>
												<td>Name</td>
												<td style='width:100px;'>Data Type</td>
												<td style='width:280px;overflow-x:hidden;'>Preview <i class="fa fa-arrow-left" style='cursor:pointer;' onclick='decPreview();'></i>&nbsp;<i class="fa fa-arrow-right" style='cursor:pointer;' onclick='incPreview();'></i></td>
												<td>Build</td>
												<td>Target</td>
											</tr>
										</thead>
										<tbody id='buildsBody'>
											
										</tbody>
									</table>

									<div class="col-lg-10">
										<span class="btn btn-danger" onclick="addCalculation();">Add Calculated Column</span>
									</div>
								</div>
								
							</div>
						</section>
					</div>
				</div>
				
				
				<div class="row" id='custom_scripting'>
				
					<div class="col-lg-12">
						<section class="panel">
							<header class="panel-heading">
								Process Scripting
							</header>
							<div class="panel-body">
					
								<form action="#" class="form-horizontal">

									<div class="form-group">
										<label class="control-label col-md-2">Scripting</label>
										<div class="col-md-10" >
											<?
												$script = "function pre() {\r\n\t//this function is called once before the processes is executed.\r\n\t//Use this to setup prompts.\r\n\tscript.log('process pre-execution parameters parsed.');\r\n}\r\n\r\nfunction begin() {\r\n\t//this function is called once at the start of the process\r\n\tscript.log('process execution started.');\r\n}\r\n\r\nfunction data(record) {\r\n\t//this function is called once for each line of data on the second cycle\r\n\t//use this to build dimensions and push data into cubes\r\n\t\r\n}\r\n\r\nfunction end() {\r\n\t//this function is called once at the end of the process\r\n\tscript.log('process execution finished.');\r\n}\r\n\r\n";
												if( $process_load != null ) {
													if( property_exists( $process_load, 'script' ) ) {
														if( trim($process_load->script) != "" ) {
															$script = $process_load->script;
														}
													}
												}
											?>
											<textarea class="wysihtml5 form-control" id='page_contents' name='page_contents' rows="15" style='height:800px;'><? echo $script;?></textarea>
										</div>
									</div>
									
								</form>
								
								
							</div>
						</section>
					</div>
				</div>
				
				
				<div class="row">
				
					<div class="col-lg-6">
						<section class="panel">
							<header class="panel-heading">
								5. Validate the Process
							</header>
							<div class="panel-body">
					
					

								<p>Please Note: You can save your process even if it is incomplete but you will not be able to execute it until it has validated successfully. </p>
								
								<form action="#" class="form-horizontal">

									<div class="form-group">
										<label for="input1" class="col-lg-2 control-label">Custom Name:</label>
										<div class="col-lg-10">
											<input type="input" class="form-control" id="custom_name" name="custom_name" value="<? echo $custom_name;?>" placeholder="Process Name" onChange='textUpdate(this);'/>
											<p class="text-muted">Note: This custom name will appear appended to the generated process name.</p>
										</div>
									</div>
									
									<div class="form-group">
										<div class="col-lg-12" align="right">
											<span class="btn btn-primary" onclick="save();">Validate & Save</span>
											<span class="btn btn-warning" onclick="executeThisProcess();">Execute Process</span>
											<span class="btn btn-danger" onclick="window.location='/model/?id=<? echo $id;?>';">Back to Model</span>
										</div>
									</div>
									
									
								</form>
								
								
							</div>
						</section>
					</div>
					
					
					<div class="col-lg-6">
						<section class="panel">
							<header class="panel-heading">
								Validation Logs
							</header>
							<div class="panel-body" id='verityResults'>
					

								
								
								
							</div>
						</div>
					</div>
				</div>
				
				
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
include_once("../lib/body_end.php");
?>
<script>
var process_definition_loaded = null;
<?
//need to dump loaded process values for list population when a datasource is loaded again at a later date for editing
echo "var tableName = '".$datasource_table."';\r\n";

if( $process_load != null ) {
	echo "var process_definition_loaded = ".json_encode($process_load).";\r\n";
	
}
//$process_load
?>
var datasourceList = <? echo json_encode($datasources->results[0]->datasources);?>;
var serviceDataName = "<? echo SERVICE_DATASOURCE;?>";
var serviceModelName = "<? echo SERVICE_MODEL;?>";
var server_id = <? echo session("active_server_id");?>;
</script>

	<!-- Pandora Library -->
	<script src="/js/service/process.js"></script>
	
	<script type="text/javascript" src="/js/codemirror/codemirror.js"></script>
	<script type="text/javascript" src="/js/codemirror/loadmode.js"></script>
	
	
<script type="text/javascript" src="/js/codemirror/xml/xml.js"></script>
<script type="text/javascript" src="/js/codemirror/javascript/javascript.js"></script>
<script type="text/javascript" src="/js/codemirror/css/css.js"></script>
<script type="text/javascript" src="/js/codemirror/vbscript/vbscript.js"></script>
<script type="text/javascript" src="/js/codemirror/htmlmixed/htmlmixed.js"></script>
<script type="text/javascript" src="/js/codemirror/r/r.js"></script>

<script type="text/javascript" src="/js/codemirror/addon/dialog/dialog.js"></script>
<script type="text/javascript" src="/js/codemirror/addon/search/searchcursor.js"></script>
<script type="text/javascript" src="/js/codemirror/addon/search/search.js"></script>
<script type="text/javascript" src="/js/codemirror/addon/scroll/annotatescrollbar.js"></script>
<script type="text/javascript" src="/js/codemirror/addon/search/matchesonscrollbar.js"></script>
<script type="text/javascript" src="/js/codemirror/addon/search/jump-to-line.js"></script>
<script type="text/javascript" src="/js/codemirror/addon/display/fullscreen.js"></script>


<script type="text/javascript">

$( document ).ready(function() {
	$(document).bind('keydown', function(e) {
		if((e.ctrlKey || e.metaKey) && e.which == 83 || e.altKey && e.which == 83) {
			e.preventDefault();
			save();
			return false;
		   
		}
		if((e.ctrlKey || e.metaKey) && e.which == 81 || e.altKey && e.which == 81) {
			e.preventDefault();
			save();
			return false;
		}
	});
});

</script>
	
<?
include_once("../lib/footer.php");
?>