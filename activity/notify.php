<?
include_once("../lib/lib.php");


$id = querystring("id");
$activityid = querystring("activityid");

$action = form("action");





if( $action == "preview" ) {
	
	$html = form("body");
	$html = '<html><head><style>body {font-size:13px;font-family: "Open Sans", "Segoe UI", "Calibri Light","Calibri", Arial, sans-serif;}</style></head><body>' . $html . '</body></html>';
	
	$subject = form("subject");
	$id = form("id");
	$activityid = form("activityid");
	
	$to = session("username");
	$client_id = session('active_client_id');
	$server_ip = "GATEWAY";
	$version = "GATEWAY";
	
	$from = "info@modlr.co";
	
	queueNotification($client_id, $server_ip, $version, $from, $to, $subject, $html);
	
	header("Content-Type: text/json");
	echo "{\"result\":1}";
	
	die();
} else if( $action == "send" ) {
	
	$html = form("body");
	$html = '<html><head><style>body {font-size:13px;font-family: "Open Sans", "Segoe UI", "Calibri Light","Calibri", Arial, sans-serif;}</style></head><body>' . $html . '</body></html>';
	
	$subject = form("subject");
	$id = form("id");
	$activityid = form("activityid");
	$tag = form("tag");
	
	$client_id = session('active_client_id');
	$server_ip = "GATEWAY";
	$version = "GATEWAY";
	
	$from = "info@modlr.co";
	
	$contents = activityData($id, $activityid);
	for($i=0;$i<count($contents->users);$i++) {
		$user = $contents->users[$i];
		
		if( $tag == "" ) {
			//send to all users
			$to = getUserEmailById($user->id);
			queueNotification($client_id, $server_ip, $version, $from, $to, $subject, $html);
		} else {
			//send to users with the selected tag.
			if( in_array($tag, $user->tags) ) {
				$to = getUserEmailById($user->id);
				queueNotification($client_id, $server_ip, $version, $from, $to, $subject, $html);
			}
		}
	}
	
	header("Content-Type: text/json");
	echo "{\"result\":1}";
	
	die();
}

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
		
	} else {
		//model not found
		redirectToPage ("/home/");
		die();
	}
} 

function queueNotification($client_id, $server_ip, $version, $from, $to, $subject, $html) {
	$db = new db_helper();
	$db->CommandText("INSERT INTO email_queue (client_id,server_ip,server_version,email_from,email_to,email_subject,email_html) VALUES ('%s','%s','%s','%s','%s','%s','%s');");
	$db->Parameters($client_id);
	$db->Parameters($server_ip);
	$db->Parameters($version);
	$db->Parameters($from);
	$db->Parameters($to);
	$db->Parameters($subject);
	$db->Parameters($html);
	$db->Execute();
}

function activityData($id, $activityid) {
	$json = "{\"tasks\": [";
	$json .= "{\"task\": \"activity.get\", \"id\":\"" . $id . "\", \"activityid\":\"" . $activityid . "\"}";
	$json .= "]}";

	$results = api_short(SERVICE_MODEL, $json);
	return $results->results[0]->activity;
}


$title = $activity_name . " Notice";
$page_contents = '<p>' . $activity_name . ' Notice: </p><p></p>'; 

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
    	
		<title>MODLR » <? echo $activity_name;?> » Broadcast to Collaborators</title>
		
		<script>
		var server_id = <? echo session("active_server_id");?>;
		var debug_mode = true;
		var model_id = "<? echo $id;?>";
		var activity_id = "<? echo $activityid;?>";
		</script>
		
<?
include_once("../lib/body_start.php");
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

		<div class="row">
            <div class="col-md-12">
                <section class="panel">
                    <header class="panel-heading">
                        Broadcast to Collaborators
                    </header>
                    <div class="panel-body">
                        <form action="/activity/page/?id=<? echo $id;?>&activityid=<? echo $activityid;?>&page=<? echo $page_id;?>&action=save" class="form-horizontal " method='post' name='pageUpdateForm'>
                        
                        	<div class="form-group">
								<label class="control-label col-md-1" for="title">Subject</label>
								<div class="col-md-11">
									<input class="form-control" type="text" class="form-control" id="title" name="title" placeholder="Subject Line" value="<? echo $title;?>">
                                </div>
							</div>
							
                        	<div class="form-group">
								<label class="control-label col-md-1" for="title">Access Tag:</label>
								<div class="col-md-11">
									<select id="tag" class="form-control">
										<option value=''>Send to all Collaborators</option>
<?
for($i=0;$i<count($activity_contents->tags);$i++) {
	$tag = $activity_contents->tags[$i];
	echo "<option value='".$tag->id."'>Collaborators with a tag: ".$tag->name."</option>";
}

?>
									</select>
                                </div>
							</div>

                            <div class="form-group">
                                <label class="control-label col-md-1">Notification</label>
								<div class="col-md-11">
                                    <textarea class="wysihtml5 form-control" id='page_contents' name='page_contents' rows="45" style='height:200px;'><? echo htmlentities($page_contents);?></textarea>
								</div>
                            </div>

							<div style='width:100%;text-align:right;'>
								<button type="button" class="btn btn-success" style='margin-left:10px;' onclick='previewNotification();'>Preview Notification</button>
								<button type="button" class="btn btn-warning" style='margin-left:10px;' onclick='sendNotification();'>Send Notification</button>
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
<!-- Theme Library -->
<script type="text/javascript" src="/js/bootstrap-wysihtml5/wysihtml5-0.3.0.js"></script>
<script type="text/javascript" src="/js/bootstrap-wysihtml5/bootstrap-wysihtml5.js"></script>

<!-- jQuery Chosen Plugin -->
<script src="/js/chosen.jquery.min.js"></script>

<script>
$(function(){
	$('.wysihtml5').wysihtml5();
});

function previewNotification() {
	var body = $(".wysihtml5-sandbox").contents().find("body").html();
	var subject = $("#title").val();
	var tag = $("#tag").val();
	var data = { "action": "preview","tag":tag, "subject":subject, "body":body, "id": model_id, "activityid": activity_id };
	//console.log(data);
	
	showLoading();
	$.post( "/activity/notify/", data ).done(function( data ) {
		hideLoading();
		$("#dialog-inner").html("The notification has been successfully sent to only your email address for review.");
		$( "#dialog-message" ).dialog({
		  modal: true,
		  title: "Preview Sent",
		  buttons: {
			Ok: function() {
			  $( this ).dialog( "close" );
			}
		  }
		});
		//console.log("Preview Sent");
	});
}

function sendNotification() {
	var body = $(".wysihtml5-sandbox").contents().find("body").html();
	var subject = $("#title").val();
	var tag = $("#tag").val();
	var data = { "action": "send","tag":tag, "subject":subject, "body":body, "id": model_id, "activityid": activity_id };
	//console.log(data);
	
	showLoading();
	$.post( "/activity/notify/", data ).done(function( data ) {
		hideLoading();
		$("#dialog-inner").html("The notification has been successfully sent.");
		$( "#dialog-message" ).dialog({
		  modal: true,
		  title: "Notification Broadcast",
		  buttons: {
			Ok: function() {
			  $( this ).dialog( "close" );
			}
		  }
		});
		//console.log("Preview Sent");
	});
}

</script>
<div id="dialog-message" title="">
  <div id='dialog-inner'>

  </div>
</div>
<?
include_once("../lib/footer.php");
?>