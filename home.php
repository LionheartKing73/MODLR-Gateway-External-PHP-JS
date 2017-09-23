<?
include_once("lib/lib.php");
include_once("lib/header.php");
?>
    	<!--external css-->
    	<link rel="stylesheet" type="text/css" href="/js/fuelux/css/tree-style.css" />
    	<script>
			var server_id = <? echo session("active_server_id");?>;
		</script>
		<title>MODLR » Modeller Home</title>
		
<?
$isiPad = (bool) strpos($_SERVER['HTTP_USER_AGENT'],'iPad');
include_once("lib/body_start.php");
?>

            
        <div class="row">
<?
	if( !$server_is_down ) {
	
		?>
		
		
			<div class="col-md-4">
				<section class="panel">
					<div class="panel-body" style='min-height: 160px;max-height: 200px;'>
						<p class="lead">Step 1. Manage Data</p>
						<p>Connect operational business systems to include in modelling. Business Structures, Accounts and Historic Information can all import regularly and automatically once linked.</p>
					</div>
				</section>
			</div>
			<div class="col-md-4">
				<section class="panel">
					<div class="panel-body" style='min-height: 160px;max-height: 200px;'>
						<p class="lead">Step 2. Create a Model</p>
						<p>Using business structures from operational datasets create simulations of business processes. <a href='/model/'>Click here.</a></p>
					</div>
				</section>
			</div>
			<div class="col-md-4">
				<section class="panel">
					<div class="panel-body" style='min-height: 160px;max-height: 200px;'>
						<p class="lead">Step 3. Create a Application</p>
						<p>Collect budgets, estimates and set targets though collaborative and social planning and reporting on models. <a href='/activity/'>Click here.</a></p>
					</div>
				</section>
			</div>
		</div>
		<div class="row">
		
			<div class="col-lg-12" style="margin-bottom:15px;">
                <button type="button" class="btn btn-success btn-lg btn-block">MODELS</button>
            </div>
		<?
	
		$contents = $results->results[0]->models;
		for($i=0;$i<count($contents);$i++) {
			$model = $contents[$i];
			
			if( $iPad ) {
				echo '<div class="col-md-4">';
			} else {
				echo '<div class="col-md-3">';
			}
			
			?>
			
				<section class="panel">
					<div class="panel-body">
						<div class="gauge-canvas">
							<h4 class="widget-h" style="min-height: 34px;"><a  target='_self' href='/model/?id=<? echo $model->id;?>'><? echo $model->name;?></a></h4>
							
							<div class="weather-category twt-category" style="margin-top: 0px;margin-bottom: 0px;padding-bottom: 0px;padding-top: 0px;">
								<ul>
									<li class="active">
										<h5><? echo $model->cubesCount;?></h5>
										Cubes
									</li>
									<li>
										<h5><? echo $model->workviewsCount;?></h5>
										Workviews
									</li>
									<li>
										<h5><? echo $model->processesCount;?></h5>
										Processes
									</li>
								</ul>
							</div>
						
						</div>
					</div>
				</section>
			</div>
			<?
		}
		
		
if( $iPad ) {
	echo '<div class="col-md-4">';
} else {
	echo '<div class="col-md-3">';
}

?>
			
				<section class="panel">
					<div class="panel-body">
						<div class="gauge-canvas" style="min-height: 86px;">
							<h4 class="widget-h" style="min-height: 34px;margin-bottom:0px;"><a href='/model/create/' target='_self'>Create a new Model</a></h4>
							
							<div class="weather-category twt-category" style="margin-top: 0px;margin-bottom: 0px;padding-bottom: 0px;padding-top: 0px;">
								<ul>
									<li class="active">
										<i class="fa fa-pencil-square" style='color:#118dc6;font-size:50px' onclick='window.location="/model/create/";'></i>
										&nbsp;
									</li>
								</ul>
							</div>
						
						</div>
					</div>
				</section>     
        	</div>
        </div>
        <div class="row">	
			<div class="col-lg-12" style="margin-bottom:15px;">
                <button type="button" class="btn btn-success btn-lg btn-block">APPLICATIONS</button>
            </div>
<?
		$contents = $results->results[0]->models;
		for($i=0;$i<count($contents);$i++) {
			$model = $contents[$i];
			for($k=0;$k<count($model->activities);$k++) {
				$activity = $model->activities[$k];
				//echo "<li><a href='/activity/?id=".$model->id."&activityid=".$activity->activityid."'>".$activity->name."</a></li>";
				
				if( $iPad ) {
					echo '<div class="col-md-4">';
				} else {
					echo '<div class="col-md-3">';
				}
			
				?>
			
					<section class="panel">
						<div class="panel-body">
							<div class="gauge-canvas">
								<h4 class="widget-h" style="min-height: 34px;"><a href='/activity/?id=<? echo $model->id."&activityid=".$activity->activityid;?>' target='_self'><? echo $activity->name;?></a></h4>
							
								<div class="weather-category twt-category" style="margin-top: 0px;margin-bottom: 0px;padding-bottom: 0px;padding-top: 0px;">
									<ul>
										<li>
											<h5><? 
											if( property_exists($activity,"screens") ) {
												echo count($activity->screens);
											} else {
												echo "0";
											}
											
											?></h5>
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
			
		}
		

if( $iPad ) {
	echo '<div class="col-md-4">';
} else {
	echo '<div class="col-md-3">';
}

?>
			
				<section class="panel">
					<div class="panel-body">
						<div class="gauge-canvas" style="min-height: 86px;">
							<h4 class="widget-h" style="min-height: 34px;margin-bottom:0px;"><a href='/activity/' target='_self'>Create a new Application</a></h4>
							
							<div class="weather-category twt-category" style="margin-top: 0px;margin-bottom: 0px;padding-bottom: 0px;padding-top: 0px;">
								<ul>
									<li class="active">
										<i class="fa fa-pencil-square" style='color:#118dc6;font-size:50px' onclick='window.location="/activity/";'></i>
										&nbsp;
									</li>
								</ul>
							</div>
						
						</div>
					</div>
				</section>     
        	</div>
	
  
<?
} else {
	//server is down
	$servers = userServers();
	include_once("lib/server_functions.php");
	
	if( count($servers) == 0 ) {
		//user has no servers presently
?>
            <div class="col-sm-12">
                <section class="panel">
                    <header class="panel-heading">
						This account presently doesn't have a server provisioned
                        <span class="tools pull-right">
                            <a href="javascript:;" class="fa fa-chevron-down"></a>
                         </span>
                    </header>
                    <div class="panel-body">
                        <div class="tab-pane" id="workviews" style=''>
							<div class="row" style="margin-left: 0px;margin-right: 0px">
								
								<?
								 serverSetupCol();
								?>
								<div class="col-md-6">
									
									<div class="prf-contacts">
										<h2> <span><i class="fa fa-money"></i></span> Pricing Information</h2>
										<div class="location-info" >
											
											<?
											outputServerPricing();
											?>
				
												
										</div>
									</div>
								</div>
							</div>
						</div>
                    </div>
                </section>
            </div>
        </div>

<?
	} else {
		//user has servers, just none which are active
	
?>
            <div class="col-sm-12">
                <section class="panel">
                    <header class="panel-heading">
                        Modlr Engine Notice
                        <span class="tools pull-right">
                            <a href="javascript:;" class="fa fa-chevron-down"></a>
                         </span>
                    </header>
                    <div class="panel-body">
                        <div class="tab-pane" id="workviews" style='height:300px;overflow:scroll;'>
							<div class="row" style="margin-left: 0px;margin-right: 0px">
								
								<div class="col-md-6">
<?
if( session("role") == "MODELLER" ) {
?>
									
									<h3>Restarting the MODLR Application Server</h3>
									<p>
										You can restart the MODLR Application Server through the "Manage Account" page. 
									</p>
<?
} else if( session("role") == "PLANNER" ) {
?>
									<h3>Where are my Planning Activities?</h3>
									<p>
										While the Modlr Engine is down your planning activities will be unavailable. 
									</p>
									
									<h3>What can I do to start planning again?</h3>
									<p>
										Your account type is that of a Collaborator. As such you will need to contact one of the Modellers in your organisation. 
										Here is a list of their names:
											<ul>
<?
$db = new db_helper();
$db->CommandText("SELECT users.name FROM modlr.users_clients LEFT JOIN modlr.users ON users.id=users_clients.user_id WHERE users_clients.client_id = '%s' AND users_clients.role = 'MODELLER' ORDER BY users.name ASC;");
$db->Parameters(session("client_id"));
$db->Execute();
while( $r = $db->Rows() ) {
	echo "<li>".$r['name']."</li>";
}
?>
												
											</ul>
									</p>
<?
}
?>
								</div>
								
								<div class="col-md-6">
									<h3>The application server is not responding.</h3>
									<b>What does this mean?</b>
									<p>
										<ol>
											<li>The Server may still be being setup.</li>
											<li>The Server may still be starting up.</li>
										</ol>
									</p>
								</div>
								
							</div>
						</div>
                    </div>
                </section>
            </div>
        </div>

<?
	}
}
?>
		</div>
<?
include_once("lib/body_end.php");
?>

<?
include_once("lib/footer.php");
?>
