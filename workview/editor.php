<?
include_once("../lib/lib.php");

//~~~~~~~~~~~~~~~~~~~~~~~~~~ Find the specified model and load it.
$id = querystring("id");
$workviewid = querystring("workview");
$cubeid = querystring("cube");


$results = null;
if( $id != "" ) {
	echo "<!-- model id provided -->";
	$json = "{\"tasks\": [";
	$json .= "{\"task\": \"model.get\", \"id\":\"" . $id . "\"}";
	$json .= "]}";
	$results = api_short(SERVICE_MODEL, $json);

	if( intval($results->results[0]->result) == 1 ) { 
		echo "<!-- model id found in server -->\r\n";
		
		//if a cube id has been provided then we are expected to create a private temporary view of this cube.
		
		if( $cubeid != "" ) {
			$json = "{\"tasks\": [";
			$json .= '{"task": "workview.create", "id":"'.$id.'", "name": "temporary", "cube":"'.$cubeid.'", "private" : "1" }';
			$json .= "]}";
			$workview_create = api_short(SERVICE_MODEL, $json);
			header("Location: /workview/editor?id=".$id."&workview=".$workview_create->results[0]->id);
			die();
		}

		
		
		
		
		$contents = $results->results[0]->model;
		$name = $contents->name;

		$json = "{\"tasks\": [";
		$json .= "{\"task\": \"workview.get\", \"id\":\"" . $id . "\", \"workviewid\":\"" . $workviewid . "\"}";
		$json .= "]}";
		$workview_load = api_short(SERVICE_MODEL, $json);
		if( intval($results->results[0]->result) == 0 ) {
			header("Location: /model/?id=".$id);
			die();
		} 
		
		$json = "{\"tasks\": [";
		$json .= "{\"task\": \"workview.metadata\", \"id\":\"" . $id . "\", \"workviewid\":\"" . $workviewid . "\"}";
		$json .= "]}";
		$workview_metadata = api_short(SERVICE_MODEL, $json);
		if( intval($results->results[0]->result) == 0 ) {
			header("Location: /model/?id=".$id);
			die();
		} 
				
		$workview_metadata_contents = $workview_metadata->results[0]->metadata;
		
		
		$db = new db_helper();
		$db->CommandText("INSERT INTO users_recent_workviews (user_id, server_id, model_id, model_name, workview_id, workview_name) VALUES ('%s','%s','%s','%s','%s','%s');");
		$db->Parameters(session('uid'));
		$db->Parameters(session('active_server_id'));
		$db->Parameters($id);
		$db->Parameters($name);
		$db->Parameters($workviewid);
		$db->Parameters($workview_load->results[0]->name);
		$db->Execute();
		
	} else {
		header("Location: /home/");
		die();
	}
	
	
} else {
	header("Location: /home/");
	die();
}

function variableGet($key) {
	global $contents;
	for($i=0;$i<count($contents->variables);$i++) {
		$var = $contents->variables[$i];
		if( $var->key == $key ) {
			return $var->value;
		}
	}
	return "";
}


?><!DOCTYPE html>
<!--[if lt IE 7]><html lang="en-us" class="ie6"><![endif]-->
<!--[if IE 7]><html lang="en-us" class="ie7"><![endif]-->
<!--[if IE 8]><html lang="en-us" class="ie8"><![endif]-->
<!--[if gt IE 8]><!--><html lang="en-us"><!--<![endif]-->
	<head>
		<!-- Custom icons -->
		<link href="/css/workview.css" rel="stylesheet">
		<link href="/css/workview_themes/default.css" rel="stylesheet">
		
		<link rel="shortcut icon" href="/images/favicon.ico">
		<link href='https://fonts.googleapis.com/css?family=Droid+Sans+Mono' rel='stylesheet' type='text/css'>
		<link href="https://netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.css" rel="stylesheet">
		<link href="/css/modeler_theme/jquery-ui-1.10.3.custom.css" rel="stylesheet">
		<!-- <link href="/css/nicetree-style.css" rel="stylesheet"> -->
		
		
		<title>MODLR » Modelling » <? echo $name;?></title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
		
		<!-- jQuery Framework -->
		<script src="/js/jquery-1.11.1.js"></script>
		<script src="/js/jquery-ui-1.10.3.custom.min.js"></script>
		<script src="/js/jquery.animate-colors-min.js"></script>
		
		<!-- <script src="/js/jquery-tree-1.2.min.js"></script> -->
		
		<script src="/js/d3/d3.min.js" type="text/javascript"></script>
		<script src="/js/nvd3/nv.d3.min.js" type="text/javascript"></script>
		<link href="/js/nvd3/nv.d3.min.css" rel="stylesheet">
		
		<!-- jQuery Chosen Plugin -->
		<script src="/js/chosen.jquery.min.js"></script>
		<link href="/css/chosen.min.css" rel="stylesheet">
		
		<style>
			.chosen-container .chosen-results {
				max-height: 160px;
			}
		</style>
		
    	<script src="/js/contextMenu/jquery.ui.position.js" type="text/javascript"></script>
    	<script src="/js/contextMenu/jquery.contextMenu.js" type="text/javascript"></script>
    	<link href="/js/contextMenu/jquery.contextMenu.css" rel="stylesheet" type="text/css" />
    	<script src="/js/jquery.ui.touch-punch.min.js" type="text/javascript"></script>
    	
		<!--icheck-->
		<link href="/js/iCheck/skins/flat/grey.css" rel="stylesheet">
		<link href="/js/iCheck/skins/flat/red.css" rel="stylesheet">
		<link href="/js/iCheck/skins/flat/green.css" rel="stylesheet">
		<link href="/js/iCheck/skins/flat/blue.css" rel="stylesheet">
		<link href="/js/iCheck/skins/flat/yellow.css" rel="stylesheet">
		<link href="/js/iCheck/skins/flat/purple.css" rel="stylesheet">
		<script src="/js/iCheck/jquery.icheck.js"></script>
		<script src="/js/icheck-init.js"></script>
	
		<script>
		<?
		if( $workview_load != null ) {
			echo "var workview_definition_loaded = ".json_encode($workview_load->results[0]).";\r\n";
		} else {
			echo "var workview_definition_loaded = null;";
		}
	
		echo "  var workview_metadata = ".json_encode($workview_metadata_contents).";\r\n";
		echo "	var model_detail = ".json_encode($contents).";\r\n";
		echo "  var server_id = 0;\r\n";
		echo "  var server_url = '" . session("server_address") . "';\r\n";
		?>
		</script>
		
		<style>
		<?
		
		if( property_exists($contents,"styles") ) {
			for($i=0;$i<count($contents->styles);$i++) {
				$style = $contents->styles[$i];
				echo ".".$style->name." {".$style->css."}\r\n";
			}
		}
		
		$headerBackground = variableGet("headerBackground");
		$headerBorder = variableGet("headerBorder");
		$fontColor = variableGet("fontColor");
		
		if( $headerBackground == "" ) 
			$headerBackground = "#EEE";
		if( $headerBorder == "" ) 
			$headerBorder = "#DDD";
		if( $fontColor == "" ) 
			$fontColor = "#000";
		
		echo "td.h {border:1px solid ".$headerBorder.";background-color:".$headerBackground.";color:".$fontColor.";}\r\n";
		
		?>
		
		.formula {
			opacity: 0.65;
		}
		</style>
		
		
		<!-- Pandora Library -->
		
		<script src="/js/numberFormat.js"></script>
		<script src="/js/service/lib.js?v=2"></script>
		<!-- <script src="/js/ribbon.js"></script> -->
		
		<script src="/js/service/workview_editor.js?v=1.3"></script>
		<script src="/js/service/workview_set_editor.js?v=1.3"></script>
		<script src="/js/service/workview_dimension_editor.js?v=1.3"></script>
		<script src="/js/service/workview_data_editor.js?v=1.3"></script>
		<script src="/js/service/workview_formula_editor.js?v=1.3"></script>
		<script src="/js/service/workview_launcher_editor.js?v=1.3"></script>
		<script src="/js/service/workview_chart_classes.js?v=1.3"></script>
		<script src="/js/service/workview_chart_editor.js?v=1.3"></script>
		<script src="/js/service/workview_heading_editor.js?v=1.3"></script>
		
		
		<link href="/font-awesome/css/font-awesome.css" rel="stylesheet" />
		<link rel="apple-touch-icon" href="/img/apple-touch-icon-precomposed.png"/>
		<link rel="apple-touch-icon" sizes="72x72" href="/img/apple-touch-icon-72x72-precomposed.png" />
		<link rel="apple-touch-icon" sizes="114x114" href="/img/apple-touch-icon-114x114-precomposed.png" />
		<link rel="apple-touch-icon" sizes="144x144" href="/img/apple-touch-icon-144x144-precomposed.png" />
		<meta name="apple-mobile-web-app-capable" content="yes">
		<meta name="apple-mobile-web-app-status-bar-style" content="black">
	
		<link href="/font-awesome/css/font-awesome.css" rel="stylesheet">
		
		
	</head>
	<body>
	<div id='heading-banner'>
		<div class='heading-text'>
			<!--<a onclick='window.location="/model/?id=<? echo $id;?>";' href='/model/?id=<? echo $id;?>' target='_self' style='color: #fff;'><? echo $name;?></a>--> » <? echo $workview_load->results[0]->name;?>
		
			<div class='heading-icons'>
				
			
				<i class="fa fa-leaf"  style='margin-right:16px;' onclick='displayManageDimensions();' title='Manage Dimensions'></i>
				
				<i class="fa fa-bar-chart-o" onclick='displayChartDeveloper();' style='margin-right:16px;' title='Build Chart'></i>
				<i class="fa fa-caret-square-o-down" onclick='showDataValidation();' id='btnValidation' style='margin-right:16px;' title='Manage Data Validation'></i>
				<i class="fa fa-play" onclick='btnDisableSetExpressions();' id='btnDisableSetExp' style='margin-right:16px;' title='Disable Set Instructions'></i>
				
				
				<i class="fa fa-ellipsis-v" style='margin-right:16px;cursor:default;'></i>
				
				<i class="fa fa-cloud-download" onclick='workviewExport();' style='margin-right:16px;' title='Export to Excel'></i>
				<i class="fa fa-refresh" onclick='workviewSave();' style='margin-right:16px;' title='Refresh Workview'></i>
				<i class="fa fa-times" onclick='window.location="/model/?id=<? echo $id;?>";' title='Close Workview'></i>
			</div>
		</div>
		
	</div>
	
	
	<div id='left-sidebar'>
		<div class='panel_title'>Dimension Management</div>
		
		<table class='tblManageDimensions' style='width:210px;padding-top:5px;margin-left:10px;'>
 			<tr>
 				<td><b>Selectable Dimension</b></td>
 			</tr>
 			<tr>
 				<td class='top'>Dimension positioned here appear at the top of the workview.</td>
 			</tr>
 			<tr>
 				<td class='top' width='200' style='width:200px;'>
 					<div style=''>
 					<ul id="sortable1" class="draggable" style='min-height:80px;'>
					</ul>
					</div>
 				</td>
			</tr>
			<tr>
 				<td><b>On Rows</b></td>
 			</tr>
 			<tr>
 				<td class='top'>Row situated dimensions expand the report downward.</td>
 			</tr>
			<tr>
 				<td class='top' width='200' style='width:200px;'>
 					<div style=''>
 					<ul id="sortable2" class="draggable" style='min-height:80px;'>
					</ul>
					</div>
 				</td>
			</tr>
			<tr>
 				<td><b>On Columns</b></td>
 			</tr>
 			<tr>
 				<td class='top'>Column situated dimensions expand the report to the right.</td>
 			</tr>
			<tr>
				<td class='top' width='200' style='width:200px;'>
 					<div style=''>
 					<ul id="sortable3" class="draggable" style='min-height:80px;'>
					</ul>
					</div>
 				</td>
 			</tr>
			<tr>
 				<td><b>Toolkit</b></td>
 			</tr>
			<tr>
 				<td class='top'>To add a dimension to the cube, drag the tile to the intended target position.</td>
 			</tr>
			<tr>
 				<td>
 					<ul class='draggable'>
					  <li id="draggable_dimension" class="draggable" style='border:1px solid #333;background:null;background-color:#6dba89;color:#fff;' data-dimension="Add a New Dimension">Add a New Dimension</li>
					</ul>
 				</td>
			</tr>
			<tr>
 				<td>
 					<ul class='draggable'>
					  <li id="draggable_dimension_existing" class="draggable" style='border:1px solid #333;background:null;background-color:#6dba89;color:#fff;' data-dimension="Add an Existing Dimension">Add an Existing Dimension</li>
					</ul>
 				</td>
			</tr>
			<tr>
 				<td>
 					<ul class='draggable'>
					  <li id="draggable_header" class="draggable" style='border:1px solid #333;background:null;background-color:#6d89ba;color:#fff;' data-dimension="Custom Header">Custom Header</li>
					</ul>
 				</td>
 			</tr>
			<tr>
 				<td><b>Hidden Dimensions</b></td>
 			</tr>
 			<tr>
 				<td class='top'>Dimensions which do not display anywhere on the Report.</td>
 			</tr>
			<tr>
				<td class='top' width='200' style='width:200px;'>
 					<div style=''>
 					<ul id="sortable4" class="draggable" style='min-height:40px;'>
					</ul>
					</div>
 				</td>
 			</tr>
 		</table>
	
	</div>
	
	<div id='workview'></div>
	<div id='right-sidebar'>
		<div class='panel_title'>Visualisation Developer</div>
		<div style='padding:10px' id='visualisation_form'>
			
		</div>
	</div>
	
	<div id="dlgManageDimensions" title="Manage Dimensions" style='display:none;'>
  		<div id='dimMessage' style='display:none;'></div>
  		
  		
 
 		
 		
 		
 		<!-- <p style="margin-top: 0px;">To add a dimension to the cube, drag the tile to the intended target position.</p> -->
  		
	</div>
	
	<div id="dialog-confirm-dimension-removal" title="Remove Dimension from Cube?" style='display:none;'>
		<p><b>Caution:</b> This will affect all objects which utilise this cube (processes, workviews and workflow).<br/><br/>Are you sure you want to remove the "<span id='spanRemovalDim'></span>" dimension from this cube?</p>
	</div>
	<div id="dialog-confirm-hierarchy-removal" title="Remove Hierarchy from Dimension?" style='display:none;'>
		<p><b>Caution:</b> This action is permanent, all consolidations from this hierarchy used in this or any other workview will no longer calculate.<br/><br/>Are you sure you want to remove the "<span id='spanRemovalHier'></span>" hierarchy from this dimension?</p>
	</div>
	
	<div id="dialog-add-existing-dimension" title="Select an Existing Dimenison" style='display:none;'>
		<p>	
			Select an existing dimension from the list.<br/><br/><b>Dimension:</b> 
			<select id='dimension-select' style='width:280px;'>
				
			</select>
		</p>
	</div>

	<div id="dialog-dimension-element-select" title="Element Selection" style='display:none;'>
		<table class='setEditorTable' >
			<tr>
				<td>
				
					<select id='hierarchy-select' style='width:320px;'>
			
					</select><br/>
					<div id='element-tree-view'>
		
					</div>
		
					<div id='element-toolbar' onclick="addSetInstruction();">
						<img src='/img/icons/16/arrow-skip.png' class='toolbar_picker_icon'  title='Add to Set'/> Add to Selection Instructions
					</div>
				
				</td>
				<td id='tdAdvancedEditor' style='display:none;' style='width:345px;'>
					<b>Set Instructions:</b>
					
					<ul class="instruction-list" id="sortable-instructions">

					</ul>
				
					<table style='width:100%;'>
						<tr>
							<td>
								<select id='instruction-function' style='width:320px;'>
									<option value='blank'>Add Blank Space</option>
									<option value='drillable' >Enable Drill Down</option>
									<option value='expand' >Expand</option>
									<option value='expand-all' >Expand All</option>
									<option value='indent' >Indent prior set</option>
									<option value='remove' >Remove Items using the Next Instruction</option>
									<option value='remove-all-consolidations' >Remove Parents</option>
									<option value='reset-indents' >Reset prior set Indents</option>
									<option value='expand-above' >Reverse (Subtotals at the Bottom)</option>
									<option value='sort-by-name' >Sort by Name</option>
									<option value='suppress-zeros' >Suppress Empty Elements</option>
									<option value='suppress-zeros-partial' >Suppress Empty Elements (Except One)</option>
									<option value='use-alias' >Use Alias</option>
								</select>
							</td>
							<td>
								<img src='/img/icons/16/plus-button.png' id='btn_add_set_instruction' class='toolbar_picker_icon'  title='Add Instruction Function' onclick='addInstructionFunction();'/>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</div>
	
	<div id="dialog-loading" title="Loading..." style='display:none;'>
		<p><br/>
			<center><img src='/img/loader.gif'/><br/><br/>
			<span id='txtLoading'>Communicating with the analytics server.<span></center>
			
		</p>
	</div>



	<div id="dlgFormulaEditor" title="Formula Editor" style='display:none;'>
  		<div id="tabsFormula">
			<ul>
			<li><a href="#tabs-1-formula">Current Formula</a></li>
			<li><a href="#tabs-2-formula">Formula List</a></li>
			</ul>
			<div id="tabs-1-formula">
			
				<div id='dimFormulaMessage' style='display:none;'></div>
				<div style='text-align:right;'>
					<b>Description:</b> <input type='text' id='txtFormula' name='txtFormula' value='New Formula' style='width:300px;'/>
				</div>
			
				<div id='divScope'>
					<b>Restrictions:</b><br/><div id='divScopeMembers'></div>
				</div>
			
				<div id='divFormula'>
					<b>Formula:</b><br/>
					<table width="100%">
						<tr>
							<td style='font-size:18px;vertical-align:top;text-align: right;' width='24px'>=</td>
							<td>
								<textarea id="divFormulaContent"></textarea>
							</td>
						</tr>
						<tr>
							<td></td>
							<td>
								<table>
									<tr>
										<td><div class="flat-green"><input name='btnLevel' id='btnLevelOff' type='radio' checked/></div></td>
										<td>Normal Calculation&nbsp;&nbsp;</td>
										<td><div class="flat-green"><input name='btnLevel' id='btnLevelOn' id='btnLevelOn' type='radio'/></div></td>
										<td>Average or Rate Calculation </td>
										<td>&nbsp;</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</div>
			</div>
			<div id="tabs-2-formula">
			
			</div>
		</div>
	</div>
	
	
	<div id="dlgHeaderEditor" title="Heading Editor" style='display:none;'>
  		
		<div id='divHeadingFormula'>
			<p>Heading formulae can refer to adjacent elements from dimensions on the same position and also support a lot of MS Excel functions such as IF, LEN and MID. To refer to a dimension element on at the same position use the ELEMENT function specifying the dimension.</p>
			<table width="100%">
				<tr>
					<td style='font-size:18px;vertical-align:top;text-align: right;' width='24px'>=</td>
					<td>
						<textarea id="divHeadingContent"></textarea>
					</td>
				</tr>
				<tr>
					<td></td>
					<td>
						<table>
							<tr>
								<td><div class="flat-grey"><input name='btnHeadingSet' id='btnHeadingSetOff' type='radio' checked/></div></td>
								<td>Apply to this one Heading&nbsp;&nbsp;</td>
								<td><div class="flat-grey"><input name='btnHeadingSet' id='btnHeadingSetOn' type='radio'/></div></td>
								<td>Apply to the Set </td>
								<td>&nbsp;</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</div>
				
	</div>

	
	<div id="dlgConditionalFormatEditor" title="Conditional Format Editor" style='display:none;'>
  		
		<b>Format Condition:</b><br/>
		Where values is between 
		<input type='text' id='txtLower' name='txtLower' value='0' style="width:80px;"/> 
		and 
		<input type='text' id='txtUpper' name='txtUpper' value='100' style="width:80px;"/>
		<br/><br/>
		<b>Style: </b>
		<select id='conditional-styles' style='width:200px;'>
		</select>
		
	</div>
	
	
	<div id="dlgDimensionEditor" title="Dimension Builder" style='display:none;'>
  		<div id='dimEditorMessage' style='display:none;'></div>
  		
  		<div id='divNewDimension'>
  			<div id='divDimName' style='padding:5px;float:right;'>
  				<b>Dimension Name:</b> <input type='text' id='txtDim' name='txtDim' value='New Dimension'/>
  				<br/><p style="margin-bottom: 0px;"><b>Note:</b> As this is a new dimension the elements above will form the default hierarchy.</p>
  			</div>
  		 
  		 	<div id='divUpdateOptions' style='float:right;display:none;width:400px;padding-left:8px;'>
  				<table cellspacing='0' cellpadding='0'>
  				<tr style='height:20px;'>
  				<td width='120px'><b>Dimension:</b></td><td><span id='spanDimName'></span></td>
  				</tr><tr style='height:20px;'>
  				<td><b>Hierarchy:</b></td><td><span id='spanHierName'></span><img src='/img/icons/16/plus-button.png' style='margin-left:5px;top:5px;position: relative;cursor:pointer;' onclick='btnAddHierarchy();'/><img src='/img/icons/16/cross-button.png' style='margin-left:3px;top:5px;position: relative;cursor:pointer;' onclick='btnRemoveHierarchy();'/></td>
  				</tr>
  				</table>
  				<!--<p style="margin-bottom: 0px;"><input type="checkbox" name="chkDimClean" id="chkDimClean" value="clean"> <b>Clean Up:</b> Remove excluded elements from the dimension entirely (including all other hierarchies).</p>-->
  			</div> 
  		 	
  			<p style="margin-bottom: 0px;"><b>Instructions:</b> Write the names of the elements for this dimension in the text box below using a new line for each element. Use indentation to build any number of totals at any depth.</p>
			<textarea id="dimension-elements" style='height:260px;'></textarea>
			
			
  		</div>
	</div>
	
	
	<div id="dlgFormulaTracer" title="Value and Formula Explanation" style='display:none;'>
  		<div id='divCellTraceResults'>
  			
			
			
  		</div>
	</div>
	
	
	<div id="dlgColumnWidth" title="Set Column Width" style='display:none;'>
  		<center><span>Column width: </span>
  		<input type='text' id='columnWidth' value=''/></center>
	</div>
	
	<div id="dlgSaveAs" title="Workview - Save As" style='display:none;'>
  		<center><span>Save Workview As: </span>
  		<input type='text' id='workviewNewName' value=''/></center>
	</div>
	
	
	<div id="dialog-data-validation" title="Data Validation" style='display:none;overflow: visible;'>
		<span><b>Data Validation for the measure:</b> "<span id='data-validation-measure'></span>"</span><br/>
		<p>Note: Data validation only applies to String measures.</p>
		<span>
		<table width="100%">
			<tr>
				<td>Validation:</td>
				<td><select id='validation-dimension-select' style='width:250px;'></select></td>
			</tr>
			<tr>
				<td>Level:</td>
				<td>
					<select id='validation-dimension-select-level' style='width:250px;'>
						<option value="0">The top level in the dimension</option>
						<option value="1">The top level -1</option>
						<option value="2">The top level -2</option>
						<option value="3">The top level -3</option>
						<option value="4">The top level -4</option>
						<option value="5">The top level -5</option>
						<option value="6">The top level -6</option>
						<option value="7">The top level -7</option>
						<option value="8">The top level -8</option>
						<option value="9">The top level -9</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>Parent Measure:</td>
				<td><input type='text' id='validation-parent' style='width:280px;' value=''></td>
			</tr>
			
		</table>
			<p>Note: The parent measure is an optional field. Parent Measure is used when multiple measures are used together with sequential levels from the same source dimension to produce cascading validation.</p>
		
		</span>
	</div>
	<form method='post' action='/lib/base64.php' name='filedownload' target='_blank'><input type='hidden' id='data' name='data' value=''/><input type='hidden' id='ct' name='ct' value=''/><input type='hidden' id='nm' name='nm' value=''/></form>
	
</body>
</html>