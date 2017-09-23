<?
include_once("../lib/lib.php");

$action = querystring("action");
$id = querystring("id");
$activityid = querystring("activityid");
$page_id = querystring("page");
$title = "";

$table_contents = new stdClass();


if( $action == "save" ) {
	
	$type = "table";
	$title = form("title");
	$style = "NONE"; //form("style");
	
	$definitionStr = form("definition");
	if( $definitionStr != "" ) 
		$table_contents = json_decode($definitionStr, true);

	//the dataset defining the form
	
	$def = array(
		'pageid' => $page_id,
		'type' => $type,
		'name' => $title,
		'style' => $style,
		'contents' => $table_contents
	);
	
	//api call
	$json = "{\"tasks\": [";
	$json .= "{\"task\": \"activity.page.create.update\", \"id\":\"" . $id . "\", \"activityid\":\"" . $activityid . "\", \"definition\":" . json_encode($def, JSON_FORCE_OBJECT) . "}";
	$json .= "]}";

	$results = api_short(SERVICE_MODEL, $json);
	
	if(  intval($results->results[0]->result) == 1  ) {
		$page_id = $results->results[0]->pageid;
	}
	
	//refresh the page.
	redirectToPage ("/activity/table/?id=" . $id . "&activityid=" . $activityid . "&page=".$page_id."&action=open" );
	die();
	
}


if( $action == "open" ) {
	//handled elsewhere
	
} else if( $action == "delete" ) {
	
	$json = "{\"tasks\": [";
	$json .= "{\"task\": \"activity.page.delete\", \"id\":\"" . $id . "\", \"activityid\":\"" . $activityid . "\", \"pageid\":\"" . $page_id . "\"}";
	$json .= "]}";

	$results = api_short(SERVICE_MODEL, $json);
	
	redirectToPage ("/activity/?id=" . $id . "&activityid=" . $activityid);
	die();
}

if( $id != ""  &&  $activityid != "" ) {
	
	$json = "{\"tasks\": [";
	$json .= "{\"task\": \"model.get\", \"id\":\"" . $id . "\"}";
	$json .= ",{\"task\": \"activity.get\", \"id\":\"" . $id . "\", \"activityid\":\"" . $activityid . "\"}";
	if( $page_id != "" ) { 
		$json .= ",{\"task\": \"activity.page.get\", \"id\":\"" . $id . "\", \"activityid\":\"" . $activityid . "\", \"pageid\":\"" . $page_id . "\"}";
	}
	$json .= "]}";

	$results = api_short(SERVICE_MODEL, $json);
	
	if( intval($results->results[0]->result) == 1 && intval($results->results[1]->result) == 1 ) { 
		$model_contents = $results->results[0]->model;
		$model_name = $model_contents->name;
		
		$activity_contents = $results->results[1]->activity;
		$activity_name = $activity_contents->name;
		 
		if( $page_id != "" ) { 
			
			//LOADING LOGIC - TODO
			
			$page_id = $results->results[2]->pageid;
			$title = $results->results[2]->name;
			$type = $results->results[2]->type;
			$style = $results->results[2]->style;
			
			$table_contents = $results->results[2]->table;
			
			
		}
		
	} else {
		//model not found
		redirectToPage ("/home/");
		die();
	}
	
} 

include_once("../lib/header.php");
?>
		
		<!-- jQuery Chosen Plugin -->
		<link href="/css/chosen.min.css" rel="stylesheet">
		<link href="/css/workview.css" rel="stylesheet">
		<link href="/css/workview_themes/default.css" rel="stylesheet">
    	
		<title>MODLR » <? echo $activity_name;?> » Table View Editor</title>
		
		<script>
		var server_id = <? echo session("active_server_id");?>;
		var model_detail = <? echo json_encode($model_contents);?>;
		var model_id = "<? echo $model_contents->id;?>";
		
		var table_id = "dummy_value";
		var tables = [];
		
		var definition = <? echo json_encode($table_contents);?>;
		var pageid = "<? echo $page_id;?>";
		
		
		</script>
		
		<style>
			div.preview {
				padding: 10px;
				margin: 10px;
				border: 2px solid #EEE;
				background-color: #FBFBFB;
			}
			
			#fields_sortable { list-style-type: none; margin: 0; padding: 0; width: 100%; }
			#fields_sortable li { cursor:move; margin: 0 3px 3px 3px;  height: 18px; border: 2px solid #FAFAFA; background-color: #FAFAFA; padding: 5px; height: 34px; white-space: nowrap; overflow: hidden;}
			
			#orders_sortable { list-style-type: none; margin: 0; padding: 0; width: 100%; }
			#orders_sortable li { cursor:move; margin: 0 3px 3px 3px;  height: 18px; border: 2px solid #FAFAFA; background-color: #FAFAFA; padding: 5px; height: 34px; white-space: nowrap; overflow: hidden;}
			
			.btn-xs {
				margin-left:3px;
			}
		</style>
		
<?
include_once("../lib/body_start.php");
?>
		<div class="row">
            <div class="col-md-6">
                <section class="panel">
                    <header class="panel-heading">
                        Table Page Editor
                    </header>
                    <div class="panel-body">
                        <form action="/activity/table/?id=<? echo $id;?>&activityid=<? echo $activityid;?>&page=<? echo $page_id;?>&action=save" class="form-horizontal " method='post' name='pageUpdateForm'>
                        
                        	<div class="form-group">
								<label class="control-label col-md-2" for="title">Page Title</label>
								<div class="col-md-10">
									<input class="form-control" type="text" class="form-control" id="title" name="title" placeholder="Enter Page Title" value="<? echo $title;?>">
									<input type="hidden" id="style" name="style" value="STANDARD">
									<input type="hidden" id="definition" name="definition" value="">
                                </div>
                            </div>

                        	<div class="form-group">
								<label class="control-label col-md-2" for="title">Primary Table</label>
								<div class="col-md-10" >
									<select class="form-control" id='table' name='table' onchange='listPrimaryTableChange();'>
<?

		$contents = $model_contents->tables;
		for($i=0;$i<count($contents);$i++) {
			$tableObj = $contents[$i];
			$selected = "";
			
			if( $table_contents->primary == $tableObj->id ) {
				$selected = " SELECTED";
			}
			
			echo '<option value="'.$tableObj->id.'" '.$selected.'>'.$tableObj->name.'</option>';
		}
?>
									</select>
                                </div>
							</div>

                        </form>
                    </div>
                </section>
            </div>
            <div class="col-md-6">
                <section class="panel">
                    <div class="panel-body">
					
						<h4>What are Table Pages?</h4>
						<p>A Tables Page is a type of page which displays to the end use a table of information from one or more tables from the underlying Model. This Design interface takes advantage of the known fields which are references to other tables allowing you to easily blend data for presentation purposes.</p>
					
					
                    </div>
                </section>
            </div>
        </div>
		<div class="row">
			<div class="col-md-6">
                <section class="panel">
					<header class="panel-heading">
						Active Table: &nbsp;&nbsp;
						<select id='active_table' onchange='listActiveTableChange();'>
							<option value='primary'>Primary Table</option>
						</select>
						&nbsp;&nbsp; Field Selector
					</header>
                    <div class="panel-body">
			
			
						<table width='100%'>
							<thead>
								<tr>
									<td width='45%' style='border-bottom: 1px solid #FAFAFA;'><b>Field</b></td>
									<td width='35%' style='border-bottom: 1px solid #FAFAFA;'><b>Type</b></td>
									<td width='20%' style='border-bottom: 1px solid #FAFAFA;'><b>Options</b></td>
								</tr>
							</thead>
						</table>
						<div class='table_fields' style='overflow-y:scroll;height:300px;'>
							<table id='fields' width='100%'>
							</table>
						</div>
						
			
                    </div>
                </section>
            </div>
			
			<div class="col-md-3">
                <section class="panel">
					<header class="panel-heading">
						Table Blending Options
					</header>
                    <div class="panel-body">

						<div id='table_joins' style='overflow-y:scroll;height:324px;'>
							
						</div>
						
                    </div>
                </section>
            </div>
			
			<div class="col-md-3">
                <section class="panel">
					<header class="panel-heading">
						Selected Fields and Display Order
					</header>
                    <div class="panel-body" >
			
						<div id='field_selection' style='overflow-y:scroll;height:324px;'>
							
						</div>
						
                    </div>
                </section>
            </div>
			
			
        </div>
		<div class="row">
		
			<div class="col-md-3">
                <section class="panel">
					<header class="panel-heading">
						Blanky Blank Blank
					</header>
                    <div class="panel-body" >
			
						<div id='field_blank' style='overflow-y:scroll;height:324px;'>
							
						</div>
						
                    </div>
                </section>
            </div>
				
			<div class="col-md-3">
                <section class="panel">
					<header class="panel-heading">
						Table Action Buttons
						<button type="button" class="btn btn-info btn-xs" title="Sort Descending" style="float:right">Add Button</button>
					</header>
                    <div class="panel-body" >
			
						<div id='field_actions' style='overflow-y:scroll;height:324px;'>
							
						</div>
						
                    </div>
                </section>
            </div>
				
		
			<div class="col-md-3">
                <section class="panel">
					<header class="panel-heading">
						Result Ordering
					</header>
                    <div class="panel-body" >
			
						<div id='field_ordering' style='overflow-y:scroll;height:324px;'>
							
						</div>
						
                    </div>
                </section>
            </div>
			
		
			<div class="col-md-3">
                <section class="panel">
					<header class="panel-heading">
						Result Filters
						
						<button type="button" class="btn btn-info btn-xs" title="Sort Descending" style="float:right">Add Brackets</button>
					</header>
                    <div class="panel-body" >
			
						<div id='field_filtering' style='overflow-y:scroll;height:324px;'>
							
						</div>
						
                    </div>
                </section>
            </div>
		
        </div>
		<div class="row">
		
			<div class="col-md-9">
                <section class="panel">
					<header class="panel-heading">
						Table Preview
					</header>
                    <div class="panel-body" id='table_preview' style='overflow-y:scroll;height:230px;'>
			
						
                    </div>
                </section>
            </div>
		
			<div class="col-md-3">
                <section class="panel">
                    <div class="panel-body" >
			
						<button type="button" class="btn btn-success" style='margin-left:10px;' onclick='savePage();'>Save and Preview Table</button>
						<button type="button" class="btn btn-danger" style='margin-left:10px;' onclick='window.location="/activity/?id=<? echo $id;?>&activityid=<? echo $activityid;?>";'>Close</button>
								
                    </div>
                </section>
            </div>
		
        </div>
	
<div id="dialog-filter" title="Filter Creator" style='display:none;'>
	<p></p>
	<form>
		<fieldset>
			<label for="filterType" style='display:block;'>Filter Type</label>
			<select name="filterType" id="filterType" class="text ui-widget-content ui-corner-all" style="width:100%;padding: 2px; margin-bottom: 5px;" >
			</select>
			
			<div id='filterExpressionDiv' style="display:none;">
				<label for="filterValue" style='display:block;'>Expression</label>
				<input type="text" name="filterValue" id="filterValue" value="" class="text ui-widget-content ui-corner-all" style='display:block;  width: 100%; margin-bottom: 5px;'>
				<p><b>Note: </b> For Date Filtering please adopt the format 'YYYY-MM-DD' and for time 'HH:MM:SS'. </p>
			</div>
			
			<div id='filterOptionsDiv' style="display:none;">
				<label for="filterOptions" style='display:block;'>Selection</label>
				<select name="filterOptions" id="filterOptions" class="text ui-widget-content ui-corner-all" style="width:100%;padding: 2px;margin-bottom: 5px;" ></select>
				<p id='filterDateTip' style='display:none;'><b>Note: </b> Hold shift to select multiple items. </p>
			</div>
			
			
			
		</fieldset>
	</form>
</div>

	
		
<?
include_once("../lib/body_end.php");

?>

<!-- Pandora Library -->
<script type="text/javascript" src="/js/service/page.table.js"></script>
<script type="text/javascript" src="/js/service/activities.table.js"></script>


<?
include_once("../lib/footer.php");
?>