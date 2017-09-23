<?
include_once("../lib/lib.php");


$action = querystring("action");
$id = querystring("id");
$activityid = querystring("activityid");
$page_id = querystring("page");

$pageSuccess = form("pageSuccess");
$pageBack  = form("pageBack");

$title = form("title");
$style = form("style");
$table = form("table");
$method = form("method");
$fieldsStr = form("fields");
if( $fieldsStr == "" ) 
	$fields = array();
else
	$fields = json_decode($fieldsStr, true);

$page_contents_prior = form("page_contents_prior");
if( $page_contents_prior == "" ) {
	$page_contents_prior = "<b>Add a new Record</b><br/>";
	$page_contents_prior .= "<p>You can use the below form to add a new record to the table.</p>";
}

$page_contents_post = form("page_contents_post");
if( $page_contents_post == "" ) {
	$page_contents_post .= "<p>For assistance using the form above please contact Billy Bob in Marketing.</p>";
}

if( $action == "save" ) {
	
	$encoded_prior = $page_contents_prior;
	$encoded_post = $page_contents_post;
	
	$type = "form";
	
	//the dataset defining the form
	$form_data = array();
	
	//the page save package
	$def = array(
		'pageid' => $page_id,
		'type' => $type,
		'name' => $title,
		'table' => $table,
		'method' => $method,
		'style' => "STANDARD",
		'contents_prior' => $encoded_prior,
		'contents_post' => $encoded_post,
		'pageSuccess' => $pageSuccess,
		'pageBack' => $pageBack,
		'form' => $form_data,
		'fields' => $fields
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
	redirectToPage ("/activity/form/?id=" . $id . "&activityid=" . $activityid . "&page=".$page_id."&action=open" );
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
			$title = $results->results[2]->name;
			$type = $results->results[2]->type;
			$style = $results->results[2]->style;
			
			$page_contents_prior = $results->results[2]->contents_prior;
			$page_contents_post = $results->results[2]->contents_post;
			
			$table = $results->results[2]->table;
			$method = $results->results[2]->method;
			
			$pageSuccess = "";
			$pageBack = "";
			
			if( property_exists( $results->results[2] , "pageSuccess" ) ) {
				$pageSuccess = $results->results[2]->pageSuccess;
				$pageBack = $results->results[2]->pageBack;
			}
			
			if( property_exists( $results->results[2] , "fields" ) ) {
				//this is returned in an odd way due to how php json_decode works without the second argument defined.
				$fieldsObject = $results->results[2]->fields;
				
				if( is_array( $fieldsObject ) ) {
					$fields = $fieldsObject;
				} else {
					//fix this into the javascript array string. 
					for($i=0;$i<100;$i++) {
						if( property_exists( $fieldsObject , "" . $i ) ) {
							array_push($fields, $fieldsObject->{ "" . $i});
						} else {
							break;
						}
					}
				}

			}
			
		}
		
	} else {
		//model not found
		redirectToPage ("/home/");
		die();
	}
	
} 

include_once("../lib/header.php");
?>
    	<link rel="stylesheet" type="text/css" href="/js/bootstrap-wysihtml5/bootstrap-wysihtml5.css" />
    	<link rel="stylesheet" type="text/css" href="/js/codemirror/codemirror.css" />
		
    	<link rel="stylesheet" type="text/css" href="/js/gridster/jquery.gridster.min.css" />
		<link href="/js/nvd3/nv.d3.min.css" rel="stylesheet">
		
		<!-- jQuery Chosen Plugin -->
		<link href="/css/chosen.min.css" rel="stylesheet">
		<link href="/css/workview.css" rel="stylesheet">
		<link href="/css/workview_themes/default.css" rel="stylesheet">
    	
		<title>MODLR » <? echo $activity_name;?> » Form Editor</title>
		
		<script>
		var server_id = <? echo session("active_server_id");?>;
		var model_detail = <? echo json_encode($model_contents);?>;
		var model_id = "<? echo $model_contents->id;?>";
		
		var form_id = "dummy_value";
		var forms = [];
		</script>
		
		<style>
			div.preview {
				padding: 10px;
				margin: 10px;
				border: 2px solid #EEE;
				background-color: #FBFBFB;
			}
		</style>
		
<?
include_once("../lib/body_start.php");
?>
		<div class="row">
            <div class="col-md-12">
                <section class="panel">
                    <header class="panel-heading">
                        Form Editor
                    </header>
                    <div class="panel-body">
                        <form action="/activity/form/?id=<? echo $id;?>&activityid=<? echo $activityid;?>&page=<? echo $page_id;?>&action=save" class="form-horizontal " method='post' name='pageUpdateForm'>
                        
                        	<div class="form-group">
								<label class="control-label col-md-2" for="title">Form Title</label>
								<div class="col-md-10">
									<input class="form-control" type="text" class="form-control" id="title" name="title" placeholder="Enter Page Title" value="<? echo $title;?>">
									<input type="hidden" id="style" name="style" value="STANDARD">
									<input type="hidden" id="fields" name="fields" value="">
                                </div>
                            </div>

                        	<div class="form-group">
								<label class="control-label col-md-2" for="title">Form Table</label>
								<div class="col-md-10" >
									<select class="form-control" id='table' name='table' onchange='listTableChange();'>
<?
	
		$contents = $model_contents->tables;
		for($i=0;$i<count($contents);$i++) {
			$tableObj = $contents[$i];
			$selected = "";
			
			if( $table == $tableObj->id ) {
				$selected = " SELECTED";
			}
			
			echo '<option value="'.$tableObj->id.'" '.$selected.'>'.$tableObj->name.'</option>';
		}
?>
									</select>
                                </div>
							</div>

                        	<div class="form-group">
								<label class="control-label col-md-2" for="title">Form Method</label>
								<div class="col-md-10" >
									<select class="form-control" id='method' name='method'>
										<option value='CREATE'>Create a new Record</option>
										<?
											if( $method == "UPDATE" ) {
												$selected = " SELECTED";
											} else {
												$selected = "";
											}
										?>
										<option value='UPDATE'<? echo $selected;?>>Edit an existing Record</option>
									</select>
                                </div>
							</div>
							
                            <div class="form-group">
                                <label class="control-label col-md-2">Contents Above the Form:</label>
								<div class="col-md-10">
                                    <textarea class="wysihtml5 form-control" id='page_contents_prior' name='page_contents_prior' rows="4" style='height:150px;'><? echo htmlentities($page_contents_prior);?></textarea>
								</div>
                            </div>
							
                            <div class="form-group">
                                <label class="control-label col-md-2">Table Fields:</label>
								<div class="col-md-10" id='table_fields'>
									
								</div>
                            </div>
						
                        
                            <div class="form-group">
                                <label class="control-label col-md-2">Form Preview:</label>
								<div class="col-md-10" id='table_fields'>
									<div id='form_preview' class='preview'>
										
									</div>
								</div>
                            </div>
						
                            <div class="form-group">
                                <label class="control-label col-md-2">Contents Below the Form:</label>
								<div class="col-md-10">
                                    <textarea class="wysihtml5 form-control" id='page_contents_post' name='page_contents_post' rows="4" style='height:150px;'><? echo htmlentities($page_contents_post);?></textarea>
								</div>
                            </div>
							
                            <div class="form-group">
                                <label class="control-label col-md-2">Success Page:</label>
								<div class="col-md-10">
                                    <!-- <input class="form-control" type="text" class="form-control" id="pageSuccess" name="pageSuccess" placeholder="Enter the Relative URL for Successful Record Creation" value="<? echo $pageSuccess;?>">
									-->
									
									<select class="form-control" id='pageSuccess' name='pageSuccess'>
										<?
										for($i=0;$i<count($activity_contents->screens);$i++) {
											$screen = $activity_contents->screens[$i];
											
											$selected = "";
											if( $pageSuccess == $screen->id ) {
												$selected = " SELECTED ";
											}
											echo "<option value='".$screen->id."'".$selected.">".$screen->title."</option>";
											
										}
										?>
									</select>
									<? //$activity_contents ?>
								</div>
                            </div>
							
							<div class="form-group">
                                <label class="control-label col-md-2">Cancel Page</label>
								<div class="col-md-10">
                                    <!--<input class="form-control" type="text" class="form-control" id="pageBack" name="pageBack" placeholder="Enter the Relative URL for Cancelled Operation" value="<? echo $pageBack;?>">
									-->
									
									<select class="form-control" id='pageBack' name='pageBack'>
										<option value=''>Do not display a Close Button</option>
										<?
										for($i=0;$i<count($activity_contents->screens);$i++) {
											$screen = $activity_contents->screens[$i];
											
											$selected = "";
											if( $pageBack == $screen->id ) {
												$selected = " SELECTED ";
											}
											echo "<option value='".$screen->id."'".$selected.">".$screen->title."</option>";
										}
										?>
									</select>
								</div>
                            </div>
							
							<div style='width:100%;text-align:right;'>
								<button type="button" class="btn btn-success" style='margin-left:10px;' onclick='savePage();'>Save</button>
								<button type="button" class="btn btn-danger" style='margin-left:10px;' onclick='window.location="/activity/?id=<? echo $id;?>&activityid=<? echo $activityid;?>";'>Close</button>
							</div>
                        </form>
                    </div>
                </section>
            </div>
        </div>
<?
include_once("../lib/body_end.php");

?>

<!-- Pandora Library -->
<script type="text/javascript" src="/js/service/page.form.js"></script>
<script type="text/javascript" src="/js/service/activities.form.js"></script>

<script type="text/javascript" src="/js/numberFormat.js"></script>

<!-- Theme Library -->
<script type="text/javascript" src="/js/bootstrap-wysihtml5/wysihtml5-0.3.0.js"></script>
<script type="text/javascript" src="/js/bootstrap-wysihtml5/bootstrap-wysihtml5.js"></script>

<script>
var editor_prior = null;
var editor_post = null;
var table_definition = null;

var fieldList = <? echo json_encode($fields);?>;

$(function(){
	editor_prior = $('#page_contents_prior').wysihtml5();
	editor_post = $('#page_contents_post').wysihtml5();
});
</script>

<?
include_once("../lib/footer.php");
?>