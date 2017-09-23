<?
include_once("../lib/lib.php");

$servers = userServers();
if( $_SESSION['role'] == "PLANNER" ) {
	$servers = thisUsersServers();
}


$activities = array();

$json = "{\"tasks\": [";
$json .= "{\"task\": \"home.directory\"}";
$json .= "]}";

for($i=0;$i<count($servers);$i++) {
	$server_id = $servers[$i];
	
	$results = api_short_efficient(SERVICE_COLLABORATOR, $json, $server_id);
	
	$server_is_down = false;
	
	if( property_exists( $results, 'results' ) ) {
		$result = $results->results;
		if( count($result) > 0 ) {
			$resultTask = $results->results[0];
			if( property_exists( $resultTask, 'error' ) ) {
				$server_is_down = true;
			} else {
				$contents = $results->results[0]->activities;
				
				for($k=0;$k<count($contents);$k++) {
					$activity = $contents[$k];
					$activity->server_id = intval($server_id);
					array_push($activities,$activity);
					$activity = null;
				}
				
			}
		} else {
			$server_is_down = true;
			echo "<!-- ".$server_id." down. -->";
		}
	}

}


if( count($activities) == 1 ) {
	$activity = $activities[0];
	$url = '/activities/view/?id='.$activity->modelid.'&activityid='.$activity->id.'&serverid='.$activity->server_id;
	header('Location: '.$url);
	die();
}

include_once("../lib/header.php");


?>
    	<!--external css-->
    	<link rel="stylesheet" type="text/css" href="/js/fuelux/css/tree-style.css" />
    	
		<title>MODLR » Activities</title>
		
<?
$isiPad = (bool) strpos($_SERVER['HTTP_USER_AGENT'],'iPad');
include_once("../lib/body_start_horizontal.php");
?>          

<div class="row">
		
			<div class="col-lg-12" style="margin-bottom:15px;">
                <button type="button" class="btn btn-success btn-lg btn-block">ACTIVITIES</button>
            </div>
		<?
	
		 for( $i=0; $i<count($activities); $i++ ) {
            $activity = $activities[$i];
			
			if( $iPad ) {
				echo '<div class="col-md-4">';
			} else {
				echo '<div class="col-md-3">';
			}
			 
			?>
			
				<section class="panel">
					<div class="panel-body">
						<div class="gauge-canvas">
							<h4 class="widget-h" style="min-height: 34px;"><a href='/activities/view/?id=<? echo $activity->modelid;?>&activityid=<? echo $activity->id;?>&serverid=<? echo $activity->server_id;?>'><? echo $activity->name; ?></a></h4>
							
							<div class="weather-category twt-category" style="margin-top: 0px;margin-bottom: 0px;padding-bottom: 0px;padding-top: 0px;">
								<ul>
									<li class="active">
										<h5><? echo count($activity->screens);?></h5>
										Screens
									</li>
								</ul>
							</div>
						
						</div>
					</div>
				</section>
			</div>
			<?
		}
		
?>
			

<?
include_once("../lib/body_end_horizontal.php");
?>
<!--tree-->
<script src="/js/fuelux/js/tree.min.js"></script>
<!--script for this page-->
<script src="/js/tree.js"></script>
<?
include_once("../lib/footer.php");
?>
