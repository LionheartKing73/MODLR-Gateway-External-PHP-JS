<?
include_once("../lib/lib.php");
$id = querystring("id");
$activity_id = querystring("activityid");
$server_id = querystring("serverid");
$title = querystring("title");

$model_contents = null;
$activity_contents = null;
$screen = null;
if( $id != ""  &&  $activity_id != ""  &&  $server_id != "" ) {
	
	$json = "{\"tasks\": [";
	$json .= "{\"task\": \"model.get\", \"id\":\"" . $id . "\"},";
	$json .= "{\"task\": \"activity.get\", \"id\":\"" . $id . "\", \"activityid\":\"" . $activity_id . "\"}";
	$json .= "]}";

	$results = api_short_efficient(SERVICE_MODEL, $json , $server_id);
	
	if( intval($results->results[0]->result) == 1 && intval($results->results[1]->result) == 1 ) { 
		$model_contents = $results->results[0]->model;
		$model_name = $model_contents->name;
		
		$activity_contents = $results->results[1]->activity;
		$activity_name = $activity_contents->name;
		
		if( property_exists( $activity_contents, 'screens' ) ) {
			if( $title == "" ) {
				$screens = $activity_contents->screens;
				if( count($screens) > 0 ) {
					$title = $screens[0]->title;
				}
			}
		
			$screens = $activity_contents->screens;
			$bFound = false;
			for($i=0;$i<count($screens);$i++) { 
				$screen = $screens[$i];
				if( $screen->title == $title ) {
					$bFound = true;
					break;
				}
			}
			if( !$bFound ) {
				$title = $screens[0]->title;
				$screen = $screens[0];
			}
		} else {
			//activity has no screens
			
		}
	} else {
		//model not found
		redirectToPage ("/activities/home/");
		die();
	}
	
} else {
	//model not found
	redirectToPage ("/activities/home/");
	die();
} 


include_once("../lib/header.php");
?>
		<title>Modlr » <? echo $model_name;?> » <? echo $activity_name;?> » <? echo $title;?></title>		
<?
$isiPad = (bool) strpos($_SERVER['HTTP_USER_AGENT'],'iPad');
?>
</head>
<body style="margin-top: 0px !important;">

  <section id="container">
      
      <!--main content start-->
      <section id="main-content" style="margin-left: 0px;">
          <section class="wrapper" style='margin-top: 0px;'>
              <!-- page start-->

			
			<div class="row">
				
				<div class="col-md-12">
					<div class="panel">
						<div class="panel-heading">
							Activity Stream
						</div>
						<div class="panel-body">
						
							<div class="recent-act">
								<h1>Recent Activity</h1>
								<div class="activity-icon terques">
									<i class="fa fa-check"></i>
								</div>
								<div class="activity-desk">
									<h2>1 Hour Ago</h2>
									<p>Published the "Annual Budget" Collaborative Activity to Contributors</p>
								</div>
								<div class="activity-icon red">
									<i class="fa fa-beer"></i>
								</div>
								<div class="activity-desk">
									<h2 class="red">2 Hour Ago</h2>
									<p> Commentary on a figure: $1,450,250 - <a href="#" class="terques">Steve Martin</a>: I'm not sure this number is correct. Can you please check this ties back to SAP?</p>
								
									<button class="btn btn-xs btn-grey"><a style="cursor: default;color:white;" href="#">May 2014</a></button> 
									<button class="btn btn-xs btn-grey"><a style="cursor: default;color:white;" href="#">Actual</a></button> 
									<button class="btn btn-xs btn-grey"><a style="cursor: default;color:white;" href="#">Finance</a></button> 
									<button class="btn btn-xs btn-grey"><a style="cursor: default;color:white;" href="#">600000 - Total Sales</a></button> 
									<button class="btn btn-xs btn-grey"><a style="cursor: default;color:white;" href="#">Amount</a></button> 
								
								</div>
							

							</div>
						
						
						</div>
					</div>
				</div>
				
			</div>
		<!-- page end-->
        </section>
    </section>
    <!--main content end-->
</section>		


<?
include_once("../lib/footer.php");
?>
