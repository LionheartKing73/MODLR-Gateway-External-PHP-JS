<?
include_once("lib/lib.php");
$model_contents = null;

if( session("role") != "MODELLER" ) {
	header("Location: /home/");
	die();
}

$json = "{\"tasks\": [";
$json .= "{\"task\": \"server.jobs\"},";
$json .= "{\"task\": \"server.logs\"}";
$json .= "]}";

$resultsJobs = api_short(SERVICE_SERVER, $json);
if( !property_exists($resultsJobs->results[0],"jobs") ) {
	header("Location: /home/");
	die();
}


$action = querystring("action");
$id = querystring("id");

include_once("lib/header.php");

?>
<title>MODLR » Jobs</title>

<style>
div.dataTables_length select {
  display: inline;
}
.dataTables_filter label input {
  display: inline;
}

</style>
    <!--dynamic table-->
    <link href="/js/advanced-datatable/css/demo_page.css" rel="stylesheet" />
    <link href="/js/advanced-datatable/css/demo_table.css" rel="stylesheet" />
    <link rel="stylesheet" href="/js/data-tables/DT_bootstrap.css" />
<?
include_once("lib/body_start.php");



?>

				<div class="row">
				
					<div class="col-md-12">
						<section class="panel">
							<header class="panel-heading">
								Process Execution Monitor
							</header>
							<div class="panel-body" style='overflow-x:scroll;'>
							
<table width='100%' id='table-process-logs' class='display table table-bordered table-striped'>
	<thead>
		<tr>
			<td><b>Process</b></td>
			<td><b>Start Time</b></td>
			<td><b>Execution Time</b></td>
			<td><b>Rows Processed</b></td>
			<td><b>Status</b></td>
			<td><b>Initiator</b></td>
		</tr>
	</thead>
	<tbody>
	<?
	
	function timestr($str) {
		$ftime = strptime($str, '%Y-%m-%d %H:%M:%S');
		$unxTimestamp = mktime( 
                    $ftime['tm_hour'], 
                    $ftime['tm_min'], 
                    $ftime['tm_sec'], 
                    1 , 
                    $ftime['tm_yday'] + 1, 
                   $ftime['tm_year'] + 1900 
                 ); 
		return $unxTimestamp;
	}
	
	function outputJob($job, $depth) {
		$exec_time = "0 Seconds";
		$rows = "None";
		$status = $job->status;
		$coloring = "";
		
		if( property_exists($job,"tags") ) {
			if( $job->tags->rows > 0 ) {
				$rows = $job->tags->rows;
			}
			$status = $job->tags->status;
			
			
			//TODO: Calc Diff between time and finish time
			
			$finish_time_tstr = timestr($job->tags->last_update_time);
			$start_time_tstr = timestr($job->start_time);
			
			
			$exec_time = number_format(($finish_time_tstr-$start_time_tstr),0) . " Seconds"; //$job->last_update_time
			
		} else {
			
			
			//TODO: Calc Diff between time and now
			$exec_time = number_format(time() - timestr($job->start_time),0) . " Seconds";
			$coloring = "background-color: #FEA;";
		}
		
		$initator = "Parent Process";
		if( property_exists($job,"initator") ) { 
			$initator = $job->initator;
		}
		
		$status_icon = "<img src='/img/icons/16/arrow-transition.png'/>";
		if( strtolower($status) == "process complete" ) {
			$status_icon = "<img src='/img/icons/16/tick-button.png'/>";
		}
		if( strtolower($status) == "aborted" ) {
			$status_icon = "<img src='/img/icons/16/cross-button.png'/>";
		}			
		
		$indent = (10 + ($depth * 20)) . "px";
		
		$sub_icon = "<img src='/img/icons/16/script-text.png'/>";
		if( $initator == "Parent Process" ) {
			$sub_icon = "<img src='/img/icons/16/chain.png'/>";
		}
		
		
		$rowStyle = "";
		if( $depth == 0 ) {
			$rowStyle = "border-top: 2px solid #999;";
		}
		
		
		echo "<tr style='".$rowStyle."'>";
		
		echo "<td style='padding-left:".$indent.";".$coloring."'>" . $sub_icon . " " . $job->name . "</td>";
		echo "<td>" . $job->start_time . "</td>";
		echo "<td align='center'>" . $exec_time . "</td>";
		if( is_numeric($rows) ) {
			echo "<td align='right'>" . number_format($rows,0) . "</td>";
		} else {
			echo "<td align='right'>" . $rows . "</td>";
		}
		echo "<td>" . $status_icon . " " . $status . "</td>";
		echo "<td>" . $initator . "</td>";
		
		echo "</tr>";
		
		if( property_exists($job,"subroutines") ) { 
			for($k=0;$k<count($job->subroutines);$k++) {
				outputJob($job->subroutines[$k],$depth+1);
			}
		}
	}
	
	
		$jobs = $resultsJobs->results[0]->jobs;
		for($i=count($jobs)-1;$i>=0;$i--) {
			$job = $jobs[$i];
			outputJob($job, 0) ;
		}
	?>
	</tbody>
</table>
							</div>
						</section>
					</div>

				</div>
				<div class="row">

					<div class="col-md-12">
						<section class="panel">
							<header class="panel-heading">
								Application Server Log
							</header>
							<div class="panel-body" style='overflow-x:scroll;'>
							<p><b>Warning:</b> This page does not automatically update. To update the status of each processes execution, click the refresh button at the top of the page.</p>
<table width='100%' id='table-application-logs' class='display table table-bordered table-striped'><thead>
	<tr><td><b>Timestamp</b></td><td><b>Type</b></td><td><b>Message</b></td></tr>
                    </thead>
                    <tbody>
<?
	$logs = $resultsJobs->results[1]->logs;
	for($i=0;$i<count($logs);$i++) {
		$log = $logs[$i];
		$logStr = explode("\t",$log);
		echo "<tr><td>" . $logStr[0] ;
		echo "</td><td align='center'>" . $logStr[1] ;
		echo "</td><td>" . $logStr[2] ;
		echo "</td></tr>";
	}
?>
</tbody>
</table>
							</div>
						</section>
					</div>

					
				</div>
				
				<div class="row">

					<div class="col-md-12">
						<section class="panel">
							<header class="panel-heading">
								Application Server Exceptions (10 Most Recent)
							</header>
							<div class="panel-body" style='overflow-x:scroll;'>
							<p><b>Warning:</b> This page does not automatically update.</p>
<table width='100%' id='table-application-exceptions' class='display table table-bordered table-striped'><thead>
	<tr><td><b>Timestamp</b></td><td><b>Version</b></td><td><b>Type</b></td><td><b>Message</b></td><td><b>Hints</b></td></tr>
                    </thead>
                    <tbody>
<?

	$server_location = explode(":",session('server_address'));

	$db = new db_helper();
	$db->CommandText("SELECT exception, message, date_added, server_version FROM modlr.servers_errors WHERE server_ip = '%s' ORDER BY server_error_id DESC LIMIT 0,10;");
	$db->Parameters($server_location[0]);
	$db->Execute();
	
	
	
	if ($db->Rows_Count() > 0) {
		while( $r = $db->Rows() ) {
			
			$exception = $r['exception'];
			$message = $r['message'];
			$date_added = $r['date_added'];
			$server_version = $r['server_version'];
			
			$hint = "";
			
			
			if( strpos( $message , 'has no public instance field or method named "toJSON"') > -1 ) {
				$hint = "This typically occurs when a value added to the result object is not a class which can be serialised to JSON.";
			}
			
			echo "<tr><td>" . $date_added ;
			echo "</td><td align='right'>" . $server_version;
			echo "</td><td>" . $exception;
			echo "</td><td>" . $message;
			echo "</td><td style=''>" . $hint;
			echo "</td></tr>";
		}
	}

	
?>
</tbody>
</table>
							</div>
						</section>
					</div>

					
				</div>

<?
include_once("lib/body_end.php");
?>

<script type="text/javascript" language="javascript" src="/js/advanced-datatable/js/jquery.dataTables.js"></script>
<script>
	$(document).ready(function() {
		$('#table-application-logs').dataTable( { "aaSorting": [[ 0, "desc" ]] } );
		$('#table-process-logs').dataTable( {bSort: false}  );
	} );
</script>
<?
include_once("lib/footer.php");
?>
