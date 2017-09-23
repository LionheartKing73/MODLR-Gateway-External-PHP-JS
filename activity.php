<?
include_once("lib/lib.php");
$model_contents = null;

if( session("role") != "MODELLER" ) {
	header("Location: /home/");
}

$activity_route = form("route");
$activity_name = form("txtNewActivity");
if( $activity_name != "" ) {
	
	$list_model = form("list_model");
	
	$payload = array(
		"tasks" => array(
			array("task" => "activity.create.update", "id" => $list_model, "definition" => array(
					"name"=>$activity_name,
					"route"=>$activity_route
				)
			)
		)
	);
	$json = json_encode($payload);
	
	$results = api_short(SERVICE_MODEL, $json);
	
	if( intval($results->results[0]->result) == 1 ) { 
		$id = $results->results[0]->activityid;
		redirectToPage ("/activity/?id=" . $list_model . "&activityid=" . $id);
		die();
	} else {
		
	}
	
}

$action = querystring("action");
$id = querystring("id");
$activityid = querystring("activityid");

$model_contents = null;
$activity_contents = null;

if( $id != ""  &&  $activityid != "" ) {
	
	$json = "{\"tasks\": [";
	$json .= "{\"task\": \"model.get\", \"id\":\"" . $id . "\"},";
	$json .= "{\"task\": \"activity.get\", \"id\":\"" . $id . "\", \"activityid\":\"" . $activityid . "\"}";
	$json .= "]}";

	$results = api_short(SERVICE_MODEL, $json);
	
	if( intval($results->results[0]->result) == 1 && intval($results->results[1]->result) == 1 ) { 
		$model_contents = $results->results[0]->model;
		$model_name = $model_contents->name;
		
		$activity_contents = $results->results[1]->activity;
		$activity_name = $activity_contents->name;
		
		if( property_exists( $activity_contents , "route" ) ) {
			$activity_route = $activity_contents->route;
		} else {
			$activity_route = str_replace(" ", "-", strtolower($activity_name));
		}
		
		
		if( form("delete") == "ok" ) {
			
			$json = "{\"tasks\": [";
			$json .= "{\"task\": \"activity.delete\", \"id\":\"" . $id . "\", \"activityid\":\"" . $activityid . "\"}";
			$json .= "]}";

			$results = api_short(SERVICE_MODEL, $json);
			//print_r($json);
			redirectToPage ("/home/");
			die();
		}
		
		
	} else {
		//model not found
		redirectToPage ("/home/");
		die();
	}
	
} 

include_once("lib/header.php");

if( $id == "" ) { 
	echo "<title>MODLR » Create an Application</title>";
} else {
	echo "<title>MODLR » ".$activity_name."</title>";
	echo "<script>\r\nvar model_detail = ".json_encode($model_contents).";\r\n</script>";
	?>
	
    <!--icheck-->
    <link href="/js/iCheck/skins/flat/grey.css" rel="stylesheet">
    <link href="/js/iCheck/skins/flat/red.css" rel="stylesheet">
    <link href="/js/iCheck/skins/flat/green.css" rel="stylesheet">
    <link href="/js/iCheck/skins/flat/blue.css" rel="stylesheet">
    <link href="/js/iCheck/skins/flat/yellow.css" rel="stylesheet">
    <link href="/js/iCheck/skins/flat/purple.css" rel="stylesheet">
    
    <!-- select2 -->
    <link rel="stylesheet" type="text/css" href="/js/jquery-multi-select/css/multi-select.css" />
    <link rel="stylesheet" type="text/css" href="/js/jquery-tags-input/jquery.tagsinput.css" />
    <link rel="stylesheet" type="text/css" href="/js/select2/select2.css" />
    
	<?
}

include_once("lib/body_start.php");

if( $activityid == "" ) {
?>

				<div class="row">
				
					<div class="col-lg-6">
						<section class="panel">
							<header class="panel-heading">
								Create a new Application
							</header>
							<div class="panel-body">
				
									<form action="/activity/" method='post' class="form-horizontal">

										<div class="form-group">
											<label for="input1" class="col-lg-2 control-label">Activity Name:</label>
											<div class="col-lg-10">
												<input type="input" class="form-control" id="txtNewActivity" name="txtNewActivity" value="" placeholder="New Activity Name" />
												<span class="help-block">Example: Annual Budget, Quarterly Forecast, Weekly Estimate</span>
											</div>
										</div>
										
										<div class="form-group">
											<label class="control-label col-md-2" for="route">Route</label>
											<div class="col-md-10">
												<div class="input-group">
													<div class="input-group-addon">/</div>
													<input class="form-control" type="text" class="form-control" id="route" name="route" placeholder="Default Route" value="<? echo $activity_route;?>">
												</div>
											</div>
										</div>
									
										<div class="form-group">
											<label for="select1" class="col-lg-2 control-label">Model:</label>
											<div class="col-lg-10">
												<select class="form-control" id='list_model' name='list_model'>
<?
$contents = $results->results[0]->models;
for($i=0;$i<count($contents);$i++) {
	$model = $contents[$i];
	echo "<option value='".$model->id."'>".$model->name."</option>";
}
?>							
												</select>
											</div>
										</div>
										
										<div class="form-group">
											<div class="col-lg-offset-2 col-lg-10">
												<button class="btn btn-primary" type='submit'>Save</button>
												<span class="btn btn-primary" onclick="window.location='/home/';">Cancel</span>
											</div>
										</div>
									</form>

							</div>
						</section>
					</div>
					<!-- /Basic forms -->
					
					<div class="col-lg-6">
						<section class="panel">
							<header class="panel-heading">
								What is in an Application?
							</header>
							<div class="panel-body">
								<p><? echo C_APP_NAME_SHORT;?> applications are built from four components.</p>
								
								<table class="table table-striped">
									<thead>
										<tr>
											<th>Component</th>
											<th>Description</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td>Contributors</td>
											<td>People who can access the reports, enter information and play with modelling workviews are called contributors.</td>
										</tr>
										<tr>
											<td>Planning Screens</td>
											<td>Planning Screens are the pages within the Activity which collaborators can see. They are either custom pages containing text, instructions or visualisations or workviews for planning. Typically Workviews are categorised as either a report of useful information for the audience or a data-entry screen.</td>
										</tr>
										<tr>
											<td>Access Tags</td>
											<td>Your contributors are tagged with access writes so that they can read and write to their responsibility areas. You can provide or deny access on the following areas of a model:
												<ul>
													<li>Workviews - Reports and data-entry templates.</li>
													<li>Intersections of information within a cube.</li>
													<ul>
														<li>e.g. Users can change the budget in FY2014 and have read only access to Actuals.</li>
													</ul>
												</ul>
											</td>
										</tr>
										<tr>
											<td>Custom Pages</td>
											<td>These are webpages which can display simple text and images created using a document editor or HTML, CSS and Javascript coded to create beautiful visualisations and interactive planning systems. 
											</td>
										</tr>
									</tbody>
								</table>


							</div>
						</section>
					</div>
					
				</div>
	
<?
} else {
?>
				<div class="row">
					<!--navigation start-->
					<nav class="navbar navbar-inverse" role="navigation" style='border-radius: 0px;top:-15px;position:relative;'>
						<!-- Brand and toggle get grouped for better mobile display -->
						<div class="navbar-header">
							<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
								<span class="sr-only">Toggle navigation</span>
								<span class="icon-bar"></span>
								<span class="icon-bar"></span>
								<span class="icon-bar"></span>
							</button>
							<a class="navbar-brand" href="#"><? echo $model_name;?> » <? echo $activity_name;?></a>
						</div>

						<!-- Collect the nav links, forms, and other content for toggling -->
						<div class="collapse navbar-collapse navbar-ex1-collapse">
							<ul class="nav navbar-nav">
								<li class="dropdown">
									<a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown">Other Activities: <b class="caret"></b></a>
									<ul class="dropdown-menu">
<?
	
		$contents = $results->results[0]->models;
		for($i=0;$i<count($contents);$i++) {
			$model = $contents[$i];
			for($k=0;$k<count($model->activities);$k++) {
				$activity = $model->activities[$k];
				echo "<li><a href='/activity/?id=".$model->id."&activityid=".$activity->activityid."'>".$activity->name."</a></li>";
			}
			
		}
?>
									</ul>
								</li>
							</ul>
					
							<ul class="nav navbar-nav navbar-right">
								<!--
								<li class="" id='btnLaunch'>
									<a href="/activities/home/" target='_blank'>
										<i class="fa fa-rocket"></i>
										Launch
									</a>
								</li>
								-->
								<li class="dropdown">
									<a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown">New <b class="caret"></b></a>
									<ul class="dropdown-menu">
										<li><a data-toggle="modal" href="#addPlanningScreenForm" onclick='add_page_click();'>Activity Screen</a></li>
										<li><a data-toggle="modal" href="#addTagForm" onclick="tag_form_reset();">Security Tag</a></li>
										<li><a data-toggle="modal" href="#addUsersForm" onclick="userFormReset();">User / Collaborator</a></li>
										<li><a data-toggle="modal" href="/activity/page/?id=<? echo $id;?>&activityid=<? echo $activityid;?>&action=new">Custom Page: Formatted Page</a></li>
										<li><a data-toggle="modal" href="/activity/page/?advanced=1&id=<? echo $id;?>&activityid=<? echo $activityid;?>&action=new">Custom Page: HTML Page</a></li>
										<li><a data-toggle="modal" href="/activity/page/?serverside=1&id=<? echo $id;?>&activityid=<? echo $activityid;?>&action=new">Custom Page: Server JavaScript</a></li>
										<li><a data-toggle="modal" href="/activity/page/?dashboard=1&id=<? echo $id;?>&activityid=<? echo $activityid;?>&action=new">Custom Page: Dashboard</a></li>
										<li><a data-toggle="modal" href="/activity/form/?form=1&id=<? echo $id;?>&activityid=<? echo $activityid;?>&action=new">Custom Page: Form</a></li>
										<!--
										<li><a data-toggle="modal" href="#uploadForm">Upload Contributors in Bulk</a></li>
										<li><a href="/files/users.csv">Download the Bulk Load Template</a></li>
										-->
									</ul>
								</li>
								
								<li class="dropdown">
									<a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown">Control <b class="caret"></b></a>
									<ul class="dropdown-menu">
										<!--
										<li><a href="#addUsersForm">Switch Application to Enabled</a></li>
										<li><a href="#addUsersForm">Switch Application to Read-Only Enabled</a></li>
										<li><a href="#addUsersForm">Switch Application to Disabled</a></li>
										-->
										<li><a href="/activity/notify?id=<? echo $id;?>&activityid=<? echo $activityid;?>">Broadcast to Collaborators</a></li>
										
										<li><a href="/activity/?action=manage&id=<? echo $id;?>&activityid=<? echo $activityid;?>">Manage Application</a></li>
										<li><a href="/activities/view/?id=<? echo $id;?>&activityid=<? echo $activityid;?>&serverid=<? echo session("active_server_id");?>">Jump to Application</a></li>
									</ul
								</li>
							</ul>
						</div><!-- /.navbar-collapse -->
					</nav>
					<!--navigation end-->
				</div>
<?
	if( $action == "manage" ) {
		$navigation_mode = "true";
		if( property_exists($activity_contents, "navigation" ) ) {
			$navigation_mode = $activity_contents->navigation;
		}
		
?>


				<div class="row">
					
					<!-- Basic forms -->
					<div class="col-lg-12">
						<section class="panel">
							<header class="panel-heading">
								Manage <? echo $activity_name;?> Application
							</header>
							<div class="panel-body">

								<form action="/activity/?action=manage&id=<? echo $id;?>&activityid=<? echo $activityid;?>" method='post' class="form-horizontal">
									
									<div class="form-group">
										<label for="input1" class="col-lg-2 control-label">Name:</label>
										<div class="col-lg-10">
											<input type="input" class="form-control" id="name" value="<? echo $activity_name;?>" placeholder="Model" />
										</div>
									</div>
									
									<div class="form-group">
										<label class="control-label col-md-2" for="route">Route</label>
										<div class="col-md-10">
											<div class="input-group">
												<div class="input-group-addon">/</div>
												<input class="form-control" type="text" class="form-control" id="route" name="route" placeholder="Default Route" value="<? echo $activity_route;?>">
											</div>
										</div>
									</div>
									
									<div class="form-group">
										<label for="input1" class="col-lg-2 control-label">Show Navigation Bar:</label>
										<div class="col-lg-10">
											<select name="navigation-show" id="navigation-show" class="form-control">
											
											<?
												if( $navigation_mode == "true"  ) {
													echo "<option value='true' selected>Show Navigation Bar [Default]</option>";
													echo "<option value='false'>Hide Navigation Bar</option>";
												} else {
													echo "<option value='true'>Show Navigation Bar [Default]</option>";
													echo "<option value='false' selected>Hide Navigation Bar</option>";
												}
											?>
											
											</select>
											<p class="help-block">If the navigation bar is set to hidden. You as the developer will need to include a hyperlink or button which will direct the user back to the Collaboration Homepage: https://go.modlr.co/activities/home/.</p>
										</div>
									</div>

									<div class="form-group">
										<div class="col-lg-offset-2 col-lg-10">
											<span class="btn btn-primary" onclick="save();" id='btnSave'>Save</span>
											<span class="btn btn-warning" onclick="window.location='/activity/?id=<? echo $id;?>&activityid=<? echo $activityid;?>';">Close</span>
											<span class="btn btn-danger" onclick="func_delete();" id='btnDelete'>Delete</span>
										</div>
									</div>

									<div class="form-group" id='testBox' style='display:none;'>
										<label for="input2" class="col-lg-2 control-label">Results:</label>
										<div class="col-lg-10" id='testResult'>
											<p></p>
										</div>
									</div>
									
								</form>

							</div>
						</section>
					</div>

				</div>
				
				<form action='/activity/?id=<? echo $id;?>&activityid=<? echo $activityid;?>' method='post' id='formDelete'>
					<input type='hidden' name='delete' id='delete' value='ok'/>
				</form>
				

<?
	} else {
?>

				<div class="row">
					
					<div class="col-md-12">
						<div class="module no-padding">
						
							
							<div class="module-header no-padding">
								<ul id="myTab" class="nav nav-tabs">
								    <li class="dropdown active">
								      <a href="#" id="myTabDrop1" class="dropdown-toggle" data-toggle="dropdown">View: <span id='module_heading'>Overview</span><b class="caret"></b></a>

								      <ul class="dropdown-menu" role="menu" aria-labelledby="myTabDrop1" style="left:30px;">
								        <li><a class='tabHeading' href="#overview" data-index="0" tabindex="-1" data-toggle="tab" onclick="module_heading.innerText = 'Overview';">Overview</a></li>
								        <li><a class='tabHeading' href="#contributors" data-index="1" tabindex="-1" data-toggle="tab" onclick="module_heading.innerText = 'Collaborators';">Collaborators</a></li>
								        <li><a class='tabHeading' href="#workviews" data-index="2" tabindex="-1" data-toggle="tab" onclick="module_heading.innerText = 'Planning Screens';">Planning Screens</a></li>
								        <li><a class='tabHeading' href="#accesstags" data-index="3" tabindex="-1" data-toggle="tab" onclick="module_heading.innerText = 'Access Tags';">Access Tags</a></li>
								        <li><a class='tabHeading' href="#custompages" data-index="4" tabindex="-1" data-toggle="tab" onclick="module_heading.innerText = 'Custom Pages';">Custom Pages</a></li>
								      </ul>
								    </li>
								</ul>

							</div>
							
							
							<div class="module-content" id='node_content'>
								
								<div id="myTabContent" class="tab-content">

									<div class="tab-pane fade in active" id="overview" style='padding:15px;'>
									
						
										<center>
											<a class='tabHeading' style='cursor: default;color:white;' href="#contributors" data-index="1" tabindex="-1" data-toggle="tab" onclick="module_heading.innerText = 'Collaborators';">
												<button class="btn btn-success">Manage Collaborators</button> 
											</a>
											<a class='tabHeading' style='cursor: default;color:white;' href="#workviews" data-index="1" tabindex="-1" data-toggle="tab" onclick="module_heading.innerText = 'Planning Screens';">
												<button class="btn btn-success">Manage Screens</button> 
											</a>
											<a class='tabHeading' style='cursor: default;color:white;' href="#accesstags" data-index="1" tabindex="-1" data-toggle="tab" onclick="module_heading.innerText = 'Access Tags';">
												<button class="btn btn-success">Manage Access Tags</button> 
											</a>
											<a class='tabHeading' style='cursor: default;color:white;' href="#custompages" data-index="1" tabindex="-1" data-toggle="tab" onclick="module_heading.innerText = 'Custom Pages';">
												<button class="btn btn-success">Manage Custom Pages</button> 
											</a>
											
											<br/><br/>
											<button class="btn btn-xs btn-grey" id='collaboratorCount'>? Collaborators</button> 
											<button class="btn btn-xs btn-grey" id='screenCount'>? Screens</button> 
											<button class="btn btn-xs btn-grey" id='tagsCount'>? Access Tags</button> 
											<button class="btn btn-xs btn-grey" id='pagesCount'>? Custom Pages</button> 
											
										</center>
										
										<table class="table table-striped">
											<thead>
												<tr>
													<th width='20%'>Component</th>
													<th>Description</th>
												</tr>
											</thead>
											<tbody>
												<tr>
													<td width='20%'>Collaborators</td>
													<td>People who can access the reports, enter information and play with modelling workviews are called contributors.</td>
												</tr>
												<tr>
													<td>Planning Screens</td>
													<td>Planning Screens are what Collaborators within the Activity can see. They are either custom pages containing text, instructions or visualisations or workviews for planning. Typically Workviews are categorised as either a report of useful information for the audience or a data-entry screen.</td>
												</tr>
												<tr>
													<td>Access Tags</td>
													<td>Your contributors are tagged with access writes so that they can read and write to their responsibility areas. You can provide or deny access on the following areas of a activity:
														<ul>
															<li>Planning Screens - Hide or Display specific screens to tagged Collaborators.</li>
															<li>Specific Members of a Model Dimension.</li>
															<ul>
																<li>e.g. Users can change the budget in FY2014 and have read only access to Actuals.</li>
															</ul>
														</ul>
													</td>
												</tr>
												<tr>
													<td>Custom Pages</td>
													<td>These are webpages which can display simple text and images created using a document editor or HTML, CSS and Javascript coded to create beautiful visualisations and interactive business solutions. 
													</td>
												</tr>
											</tbody>
										</table>
										
									
									</div>
									<div class="tab-pane fade" id="contributors" style='height:450px;overflow:scroll;'>
										<div style='padding:0px;display:none;' id='contributors_table'>

										</div>
										<div style='padding:15px;display:none;' id='contributors_none'>
										<h3>Uh Oh, Feels a little lonely here.</h3>
										<p class="lead">
											To get started you will need to add people who will help you in performing this activity.
										</p>
										<b>You can either ...</b>
										<ul>
											<li>Add users one by one <a data-toggle="modal" href="#addUsersForm">here</a> or...</li>
											<li>Upload a list of users using <a href="/files/users.csv">this template</a>.</li>
										</ul>
										</div>

									</div>
									<div class="tab-pane fade" id="workviews" style='height:450px;overflow:scroll;'>
										<div style='padding:0px;display:none;' id='screens_table'>

										</div>
										
										<div style='padding:15px;display:none;' id='screens_none'>
										<h3>Uh Oh, No planning screens have been added yet.</h3>
										<p class="lead">
											To get started you will need to add screens which collaborators will see when opening this activity.
										</p>
										<b>You can add a screen <a data-toggle="modal" href="#addPlanningScreenForm">here</a></b>
										</div>
										

									</div>
									<div class="tab-pane fade" id="accesstags" style="height:450px;overflow:scroll;">
										
										<div style='padding:0px;display:none;' id='tags_table'>

										</div>
										
										<div style='padding:15px;display:none;' id='tags_none'>
										<strong>This activity presently has no tags.</strong>
										<br/><br/>
										<strong>What are access tags?</strong>
										<p>
											Tags are used to restrict user access to workviews or dimensions members.
										</p>
										You can add a tag <a data-toggle="modal" href="#addTagForm">here</a>
										</div>
										
										

									</div>
									<div class="tab-pane fade" id="custompages" style="height:450px;overflow:scroll;">

										<div style='padding:0px;' id='pages_table'>
										</div>
										<div style='padding:15px;' id='pages_none'>
											<h3>Uh Oh, No Custom Pages are in this Activity.</h3>
											<p class="lead">
												What are pages?
											</p>
											Pages are an optional feature in Activities. These are predefined contents you can display as part of a Planning Screen.<br/>
											<b>You can add a page <a data-toggle="modal" href="/activity/page/?id=<? echo $id;?>&activityid=<? echo $activityid;?>&action=new">here</a></b>
										</div>

									</div>
								</div>
								
								
								

							</div>
						</div>
					</div>
					
					
					
					
				</div>


		<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="addTagForm" class="modal fade">
			<div class="modal-dialog" style='width: 800px;'>
				<div class="modal-content">
					<div class="modal-header">
						<button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
						<h4 class="modal-title">Add Access Tags</h4>
					</div>
					<div class="modal-body">
						
						<div id='page_tags'>
							
							<form role="form">
								<div class="form-group">
									<label for="txtName">Tag</label>
									<input class="form-control" type="text" id="txtTagName" name="txtTagName" value="" placeholder="my_tag (no spaces or symbols other than underscore)">
								</div>
								<div class="form-group">
									<label for="lstTables">Tag Scope</label>
									<select class="form-control" id="lstTagType" onchange='changedTagType(this);'>
										<option value='screen'>Screen Access</option>
										<option value='element'>Element Access</option>
									</select>
								</div>
								
								
								<div class="form-group" id="element_tag_dimension" style="display:none;">
									<label for="lstTables">Dimension</label>
									<select class="form-control" id="lstTagDimension" onchange='changedDimension(this);'>
<?
for($i=0;$i<count($model_contents->dimensions);$i++) {
	$dim = $model_contents->dimensions[$i];
	echo "<option value='".$dim->id."'>".$dim->name."</option>";
}
?>
									</select>
								</div>
								
								
								<div class="form-group" id="element_tag_heirarchy" style="display:none;">
									<label for="lstTables">Hierarchy</label>
									<select class="form-control" id="lstTagHeirarchy" onchange='changedHierarchy(this);'>
									</select>
								</div>
								
								<!--
								if workview
								Workview: Provide a table of workviews with an overlay of access: read / write / none
								-->
								<div id='screens_tag_settings' style='overflow-y:scroll;height:200px;width:100%;border:2px solid #EEE;margin-bottom:10px;'>

								</div>
								
								<!--
								if element
								Dimension: Provide a list of dimensions
								Hierarchies: Provide a list of hierarchies
								Element: Provide a table of elements with an overlay of access: read / write / none
								-->
								<div id='element_tag_settings' style='overflow-y:scroll;height:200px;width:100%;border:2px solid #EEE;margin-bottom:10px;display:none;'>

								</div>
								
								
								
								<button type="button" data-dismiss="modal" onclick='add_tag();' id='btnTagAdd' class="btn btn-default">Save</button>
								<button type="button" data-dismiss="modal" class="btn btn-default">Cancel</button>
							</form>
							
						</div>
						
						
					</div>
				</div>
			</div>
		</div>

		<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="addPlanningScreenForm" class="modal fade">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
						<h4 class="modal-title">Add Planning Screen</h4>
					</div>
					<div class="modal-body">
						
						<div id='page_workviews'>
							
							<form role="form">
								<div class="form-group">
									<label for="txtName">Title</label>
									<input class="form-control" type="text" id="txtPageName" name="txtPageName" value="" placeholder="Initial Screen">
								</div>
								
								<div class="form-group">
									<label for="txtPageRoute">Page Route</label>
									
									<div class="input-group">
										<div class="input-group-addon"><?php echo $activity_route;?>/</div>
										<input class="form-control" type="text" class="form-control" id="txtPageRoute" name="txtPageRoute" placeholder="Route" value="">
									</div>
								</div>
								
								<div class="form-group">
									<label for="lstTables">Page Layout</label>
									<select class="form-control" id="lstPageType" onchange='changedPageType(this);'>
										<option value='single'>Single page</option>
										<option value='two_vertical'>Two pages split vertically</option>
										<option value='two_horizontal'>Two pages split horizontally</option>
										<option value='three_vertical'>Three pages split vertically</option>
										<option value='three_horizontal'>Three pages split horizontally</option>
										<option value='hidden'>Hidden (No Menu Item)</option>
									</select>
								</div>
								
<?
function outputFormBlock($viewname,$viewid, $default) {
	global $model_contents, $activity_contents, $activityid;
	
	$styleList = '';
	$listAdd = '';
	$defaultDisplay = "block";
	if( $viewid == "1" || $viewid == "2" ) {
		$defaultDisplay = "none";
		$styleList = ' style="display:inline-block;width:70%;" ';
		$listAdd = '<select class="form-control" id="lstViewSize'.$viewid.'" style="display:inline-block;width:29%;>';
		for($i=0;$i<100;$i=$i+5) {
			$listAdd .= '<option value="'.$i.'">Size: '.$i.'%</option>';
		}
		$listAdd .= '</select>';
	}
	
	
	echo "<div class='form-group' id='page_".$viewid."' style='display:".$defaultDisplay.";'>";
	echo '<label for="lstTables">'.$viewname.'</label><br/>';
	echo '<select class="form-control" id="lstView'.$viewid.'" '.$styleList.'>';
	//echo '<option value="social">Activity Stream</option>';

//$contents
$views = $model_contents->workviews;
for($i=0;$i<count($views);$i++) {
	$view = $views[$i];
	echo "<option value='W_".$view->id."'>Workview: ".$view->name."</option>";
}

//$contents
$pages = $activity_contents->pages;
for($i=0;$i<count($pages);$i++) {
	$page = $pages[$i];
	
	$type = "Page: ";
	if( $page->type == "dashboard" )
		$type = "Dashboard: ";
	
	if( $page->type != "server-side" ) {
		echo "<option value='P_".$page->pageid."'>".$type.$page->name."</option>";
	}
}


/*
$db = new db_helper();
$db->CommandText("SELECT title, custom_page_id FROM custom_pages WHERE activity_id = '%s' AND server_id='%s' ORDER BY title ASC;");
$db->Parameters($activityid);
$db->Parameters(session('active_server_id'));
$db->Execute();
if ($db->Rows_Count() > 0) {
	while( $r = $db->Rows() ) {
		$title = $r['title'];
		$custom_page_id = $r['custom_page_id'];
		
		echo "<option value='P_".$custom_page_id."'>Custom: ".$title."</option>";
	}
}
*/
	echo "</select>" . $listAdd;
?>
								</div>
<?
}								

outputFormBlock("Primary View","0","");
outputFormBlock("Secondary View","1","");
outputFormBlock("Third View","2","");
			
?>								
								
								<button type="button" data-dismiss="modal" onclick='add_page();' id='btnPageAdd' class="btn btn-default">Add</button>
								<button type="button" data-dismiss="modal" class="btn btn-default">Cancel</button>
							</form>
							
						</div>
						
						
					</div>
				</div>
			</div>
		</div>

		<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="changeUsersForm" class="modal fade">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
						<h4 class="modal-title">Change Collaborator Tags</h4>
					</div>
					<div class="modal-body">
						
						<form role="form">
							<div class="form-group">
								<label for="next" id='changeUserLabel'>User</label>
								
							</div>
							<div class="form-group" style='height:100px;overflow-y:scroll;'>
								
								<label class="">Access Tags</label>
                                <div class="col-lg-6">
                                    <select multiple name="txtTagsChange" id="txtTagsChange" style="width:480px" class="populate">
                                        
                                    </select>
								</div>
								
							</div>
							<button type="button" data-dismiss="modal" onclick='update_existing_user();' class="btn btn-default">Update Tags</button>
						</form>
						
						
					</div>
				</div>
			</div>
		</div>

		<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="addUsersForm" class="modal fade">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
						<h4 class="modal-title">Add Collaborator</h4>
					</div>
					<div class="modal-body">
						<div id='user_form_default'>
							<strong>What would you like to do?</strong>
							<br/></br>
							<p><button type="button" onclick='userFormAddNew();' class="btn btn-default">Click here</button> to add a new user to this activity</p>
							<br/>
							<center><strong>OR</strong></center>
							<br/>
							<p><button type="button" onclick='userFormAddExisting();' class="btn btn-default">Click here</button> to add an existing user to this activity</p>
						</div>
						<form role="form" style='display:none;' id='existing_user_form'>
							<div class="form-group">
								<label for="lstTables">Existing User</label>
								<select class="form-control" id="lstUsers">

								</select>
							</div>
							<div class="form-group" style='height:100px;overflow-y:scroll;'>
								
								<label class="">Access Tags</label>
                                <div class="">
                                    <select multiple name="txtTags" id="txtTags" style="width:480px" class="populate">
                                        
                                    </select>
								</div>
								
							</div>
							<button type="button" data-dismiss="modal" onclick='add_existing_user();' class="btn btn-default">Add Existing User</button>
						</form>
						
						<form role="form" style='display:none;' id='new_user_form'>
							<div class="form-group">
								<label for="txtName">Full Name</label>
								<input class="form-control" type="text" id="txtName" name="txtName" value="" placeholder="John Doe">
							</div>
							<div class="form-group">
								<label for="txtEmail">Email Address</label>
								<input class="form-control" type="text" id="txtEmail" name="txtEmail" value="" placeholder="john.doe@abcxyz.com">
							</div>
							<div class="form-group">
								<label for="txtPhone">Contact Number</label>
								<input class="form-control" type="text" id="txtPhone" name="txtPhone" value="" placeholder="+61 4 12 345 678">
							</div>
							<div class="form-group" style='height:100px;overflow-y:scroll;'>
								
								<label class="">Access Tags</label>
                                <div class="">
                                    <select multiple name="txtTagsAdd" id="txtTagsAdd" style="width:480px" class="populate">
                                        
                                    </select>
								</div>
								
							</div>
							<button type="button" data-dismiss="modal" onclick='add_user();' class="btn btn-default">Add User</button>
						</form>
						
						
						
					</div>
				</div>
			</div>
		</div>

		<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="uploadForm" class="modal fade">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
						<h4 class="modal-title">Upload Users List (.csv)</h4>
					</div>
					<div class="modal-body">
						
						<form role="form">
							<div class="form-group">
								<label for="fileUpload">Data File:</label>
								<input type="file" id="fileUpload">
								<p class="help-block">Presently the only supported file format is '.csv'.</p>
							</div>
							<div class="checkbox">
								<label>
									<input type="checkbox" id='wipeAllCheck'> Wipe all other users from this activity before loading this list.
								</label>
							</div>
							<button type="button" data-dismiss="modal" onclick='upload_user_file();' class="btn btn-default">Upload List</button>
						</form>
					</div>
				</div>
			</div>
		</div>



<?
	}
}

include_once("lib/body_end.php");

?>

	<script src="/js/service/lib.js"></script>
	<script src="/js/iCheck/jquery.icheck.js"></script>
	<script src="/js/icheck-init.js"></script>
	<script src="/js/service/activity.js?v=1.1"></script>
	<script src="/js/select2/select2.js"></script>
	<script src="/js/select-init.js"></script>
	<script>
	
<?
	echo "var full_user_list = [";

	$db = new db_helper();
	$db->CommandText("SELECT users.id, users.email, users.name FROM modlr.users WHERE users.id IN (SELECT user_id FROM modlr.users_clients WHERE client_id='%s') AND account_disabled=0 ORDER BY users.name ASC;");
	$db->Parameters($_SESSION['active_client_id']);
	$db->Execute();
	if ($db->Rows_Count() > 0) {
		$output = "";
		while( $r = $db->Rows() ) {
			$name = $r['name'];
			$name = str_replace('"',"",$name);
			$output .= '{"id" : "' . $r['id'] . '","email" : "' . $r['email'] . '","name" : "' . $name . '"},';
		}
		$output = substr($output,0,strlen($output)-1);
		echo $output;
	}
	
	echo "];\r\n";
?>
		updatePage();
		var server_id = <? echo session("active_server_id");?>;
		
		window.onresize = function(event) {
			windowResize();
		}
		$( document ).ready(function() {
			windowResize();
		});
		function windowResize() {
			$(".tab-pane").css("height", $(window).height()-230 );
		}
	</script>

<?
include_once("lib/footer.php");
?>
