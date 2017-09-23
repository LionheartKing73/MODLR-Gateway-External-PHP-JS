<?
include_once("../lib/lib.php");

//~~~~~~~~~~~~~~~~~~~~~~~~~~ Find the specified model and load it.
$id = querystring("id");
$workviewid = querystring("workview");
$serverid = querystring("serverid");
$activityid = querystring("activityid");

$header = querystring("header");
$footer = querystring("footer");

$results = null;

$headerContent = "";
$footerContent = "";

if( $id != "" ) {

	echo "<!-- model id provided -->";
	
	
		$json = "{\"tasks\": [";
		$json .= "{\"task\": \"workview.get\", \"id\":\"" . $id . "\", \"workviewid\":\"" . $workviewid . "\", \"activityid\":\"" . $activityid . "\"}";
		$json .= "]}";
		$workview_load = api_short_efficient(SERVICE_COLLABORATOR, $json,$serverid);
		if( intval($workview_load->results[0]->result) == 0 ) {
			header("Location: /model/?id=".$id);
			die();
		} 
		
		$json = "{\"tasks\": [";
		$json .= "{\"task\": \"workview.metadata\", \"id\":\"" . $id . "\", \"workviewid\":\"" . $workviewid . "\", \"activityid\":\"" . $activityid . "\"}";
		$json .= "]}";
		$workview_metadata = api_short_efficient(SERVICE_COLLABORATOR, $json,$serverid);
		if( intval($workview_metadata->results[0]->result) == 0 ) {
			header("Location: /model/?id=".$id);
			die();
		} 
				
		$workview_metadata_contents = $workview_metadata->results[0]->metadata;
		$name = $workview_load->results[0]->name;
		
        if( $header != "" ) {
            $json = "{\"tasks\": [";
            $json .= "{\"task\": \"activity.get\", \"id\":\"" . $id . "\", \"activityid\":\"" . $activityid . "\"}";
            $json .= "]}";

            $results = api_short_efficient(SERVICE_COLLABORATOR, $json , $serverid);
            if( intval($results->results[0]->result) == 1 ) {
                $activity_contents = $results->results[0]->activity;
                if( property_exists( $activity_contents, 'pages' ) ) {
                    
                    $pages = $activity_contents->pages;
                    for($i=0;$i<count($pages);$i++) {
                        if( $pages[$i]->name == $header) {
                            $headerid = $pages[$i]->pageid;
                            
                            $json = "{\"tasks\": [";
                            $json .= "{\"task\": \"activity.page.get\", \"id\":\"" . $id . "\", \"activityid\":\"" . $activityid . "\", \"pageid\":\"" .  $headerid . "\", \"arguments\":{}}";
                            $json .= "]}";

                            $results = api_short_efficient(SERVICE_COLLABORATOR, $json , $serverid);
                            $headerContent = $results->results[0]->contents;
                            
                        }
                    }
                } else {
                    //activity has no screens
                }
            }
            
        }
        if( $footer != "" ) {
            $json = "{\"tasks\": [";
            $json .= "{\"task\": \"activity.get\", \"id\":\"" . $id . "\", \"activityid\":\"" . $activityid . "\"}";
            $json .= "]}";

            $results = api_short_efficient(SERVICE_COLLABORATOR, $json , $serverid);
            if( intval($results->results[0]->result) == 1 && intval($results->results[1]->result) == 1 ) {
                $footerContent =  $results->results[1]->contents;
            }
        }
} else {
	header("Location: /activities/home/");
	die();
}


?><!DOCTYPE html>
<!--[if lt IE 7]><html lang="en-us" class="ie6"><![endif]-->
<!--[if IE 7]><html lang="en-us" class="ie7"><![endif]-->
<!--[if IE 8]><html lang="en-us" class="ie8"><![endif]-->
<!--[if gt IE 8]><!--><html lang="en-us"><!--<![endif]-->
	<head>
		<!-- Custom icons -->
		
		<link rel="shortcut icon" href="/images/favicon.ico">
		<link href='https://fonts.googleapis.com/css?family=Droid+Sans+Mono' rel='stylesheet' type='text/css'>
		<link href="/css/modeler_theme/jquery-ui-1.10.3.custom.css" rel="stylesheet">
		<!-- <link href="/css/nicetree-style.css" rel="stylesheet"> -->
		
        <!--Core CSS -->
        <link href="/bs3/css/bootstrap.min.css" rel="stylesheet">
        <link href="/css/bootstrap-reset.css" rel="stylesheet">
        <link href="/font-awesome/css/font-awesome.css" rel="stylesheet" />

        <!-- Custom styles for this template -->
        <link href="/css/style.css" rel="stylesheet">
        <link href="/css/style-responsive.css" rel="stylesheet" />
        <link href="/css/modeler_theme/jquery-ui-1.10.3.custom.css" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="/js/gritter/css/jquery.gritter.css">
        
		<title>MODLR » Activities » <? echo $name;?></title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
		
		<!-- jQuery Framework -->
		<script src="/js/jquery.js"></script>
		<!--<script src="/js/jquery-1.10.2.min.js"></script>-->
		<script src="/js/jquery.animate-colors-min.js"></script>
		
		<script src="/js/jquery-ui-1.10.3.custom.min.js"></script>
		<script src="/bs3/js/bootstrap.min.js"></script>

		<script>
		$(document).ready(function() {
			 var bootstrapButton = $.fn.button.noConflict()
			 $.fn.bootstrapBtn = bootstrapButton;
		 });
		</script>
        
		
		<style>
			.chosen-container .chosen-results {
				max-height: 100px;
			}
		</style>
		
    	<script src="/js/contextMenu/jquery.ui.position.js" type="text/javascript"></script>
    	<script src="/js/contextMenu/jquery.contextMenu.js" type="text/javascript"></script>
    	<link href="/js/contextMenu/jquery.contextMenu.css" rel="stylesheet" type="text/css" />
    	<script src="/js/jquery.ui.touch-punch.min.js" type="text/javascript"></script>
		
	
		<!-- jQuery Chosen Plugin -->
		<script src="/js/chosen.jquery.min.js"></script>
		<link href="/css/chosen.min.css" rel="stylesheet">
        
		<link href="/css/workview.css" rel="stylesheet">
		<link href="/css/workview_themes/default.css" rel="stylesheet">
        
		<script>
		<?
		if( $workview_load != null ) {
			echo "var workview_definition_loaded = ".json_encode($workview_load->results[0]).";\r\n";
		} else {
			echo "var workview_definition_loaded = null;";
		}
	
		echo "  var workview_metadata = ".json_encode($workview_metadata_contents).";\r\n";
		echo "	var model_detail = {\"id\" : \"".$id."\"};\r\n";
		echo "  var server_id = ".$serverid.";\r\n";
		echo "  var activity_id = '".$activityid."';\r\n";
		?>
		</script>
		
		<style>
		<?
		
		if( property_exists($workview_metadata_contents,"styles") ) {
			for($i=0;$i<count($workview_metadata_contents->styles);$i++) {
				$style = $workview_metadata_contents->styles[$i];
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
		
		function variableGet($key) {
			global $workview_metadata_contents;
			for($i=0;$i<count($workview_metadata_contents->variables);$i++) {
				$var = $workview_metadata_contents->variables[$i];
				if( $var->key == $key ) {
					return $var->value;
				}
			}
			return "";
		}
		//$workview_metadata_contents
		?>
		</style>
		
		<!-- Pandora Library -->
		
		<script src="/js/numberFormat.js"></script>
		<script src="/js/service/lib.js?v=1"></script>
		
		<script src="/js/service/workview_editor.js?v=1.2"></script>
		<script src="/js/service/workview_data_editor.js?v=1.2"></script>
		<script src="/js/service/workview_formula_editor.js?v=1.2"></script>
		<script src="/js/service/workview_launcher_planner.js?v=1.3"></script>
		
		<link rel="apple-touch-icon" href="/img/apple-touch-icon-precomposed.png"/>
		<link rel="apple-touch-icon" sizes="72x72" href="/img/apple-touch-icon-72x72-precomposed.png" />
		<link rel="apple-touch-icon" sizes="114x114" href="/img/apple-touch-icon-114x114-precomposed.png" />
		<link rel="apple-touch-icon" sizes="144x144" href="/img/apple-touch-icon-144x144-precomposed.png" />
		<meta name="apple-mobile-web-app-capable" content="yes">
		<meta name="apple-mobile-web-app-status-bar-style" content="black">
	
		<link href="/font-awesome/css/font-awesome.css" rel="stylesheet">
		
		
	</head>
	<body style='background: #fff;'>
    <?php
    if( $headerContent != "" )
        echo $headerContent;
    ?>
    
	<div id='workview' style='min-height: 200px;overflow: visible;'></div>
	
	
	<div id="dialog-loading" title="Loading..." style='display:none;'>
		<p><br/>
			<center><img src='/img/loader.gif'/><br/><br/>
			<span id='txtLoading'>Communicating with the analytics server.<span></center>
			
		</p>
	</div>
	
	<div id="dlgFormulaTracer" title="Value and Formula Explanation" style='display:none;'>
  		<div id='divCellTraceResults'>	
  		</div>
	</div>
	<form method='post' action='/lib/base64.php' name='filedownload' target='_blank'><input type='hidden' id='data' name='data' value=''/><input type='hidden' id='ct' name='ct' value=''/><input type='hidden' id='nm' name='nm' value=''/></form>
	
    <?php
    if( $footerContent != "" )
        echo $footerContent;
    ?>
</body>
</html>