<?
include_once("../lib/lib.php");
$id = querystring("id");
$activity_id = querystring("activityid");
$server_id = querystring("serverid");
$title = querystring("title");
$page_id = querystring("page");

$model_name  = "";
$activity_contents = null;
$page_contents = null;
$screen = null;



if( $id != ""  &&  $activity_id != ""  &&  $server_id != "" ) {
	
	//this could be a form page in which case we need to submit the querystring so as to provide context for a update form.
	parse_str($_SERVER['QUERY_STRING'], $output);

    $post = file_get_contents('php://input');
	$querystring = $_SERVER['QUERY_STRING'];
    
    /*
	$options = "";
	foreach($output as $key => $value){
		if( $key != "id" && $key != "activityid" && $key != "title" && $key != "serverid" && $key != "page" ) {
			$options .= "{\"key\":\"". $key."\",\"value\":\"". $value."\"},";
		}
	}
	if( $options == "" ) {
		$options = "[]";
	} else {
		$options = "[" . substr($options,0,strlen($options)-1) . "]";
	}
	*/
	//echo "<!-- ".$options."-->";
    
    $payload = array(
        "tasks" => array(
            array( "task" => "activity.get", "id" => $id, "activityid" => $activity_id, "id" => $id ),
            array( "task" => "activity.page.get", "id" => $id, "activityid" => $activity_id, "id" => $id, "pageid" => $page_id, "querystring" => $querystring, "form" => $post )
        )
    );
	/*
	$json = "{\"tasks\": [";
	$json .= "{\"task\": \"activity.get\", \"id\":\"" . $id . "\", \"activityid\":\"" . $activity_id . "\"},";
	$json .= "{\"task\": \"activity.page.get\", \"id\":\"" . $id . "\", \"activityid\":\"" . $activity_id . "\", \"pageid\":\"" . $page_id . "\", \"arguments\":" . $options . "}";
	$json .= "]}";
    */
    $json = json_encode($payload);
	$results = api_short_efficient(SERVICE_COLLABORATOR, $json , $server_id);
	
	if( intval($results->results[0]->result) == 1 && intval($results->results[1]->result) == 1 ) { 
		$model_name = $results->results[0]->activity->model_name;
		
		$activity_contents = $results->results[0]->activity;
		$activity_name = $activity_contents->name;
		
		
		$page_contents =  $results->results[1];
		
		if( property_exists( $activity_contents, 'screens' ) ) {
			if( $title == "" ) {
				$screens = $activity_contents->screens;
				if( count($screens) > 0 ) {
					$title = $screens[0]->title;
				}
			}
			
			$bFound = true;
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


$navigation_mode = "true";
if( property_exists($activity_contents, "navigation" ) ) {
	$navigation_mode = $activity_contents->navigation;
}

include_once("../lib/header.php");
?>
		<!--Core js-->
		<script src="/js/jquery.js"></script>
		<script class="include" type="text/javascript" src="/js/jquery.dcjqaccordion.2.7.js"></script>
		<script src="/js/jquery.scrollTo.min.js"></script>
		<script src="/js/jQuery-slimScroll-1.3.0/jquery.slimscroll.js"></script>
		<script src="/js/jquery.nicescroll.js"></script>
		<script type="text/javascript" src="/js/gritter/js/jquery.gritter.js"></script>

    	<link rel="stylesheet" type="text/css" href="/js/gridster/jquery.gridster.min.css" />

		<script src="/js/jquery-ui-1.10.3.custom.min.js"></script>
		<script src="/bs3/js/bootstrap.min.js"></script>

		<script>
		$(document).ready(function() {
			 var bootstrapButton = $.fn.button.noConflict()
			 $.fn.bootstrapBtn = bootstrapButton;
		 });
		</script>
		
		<script src="/js/contextMenu/jquery.ui.position.js" type="text/javascript"></script>
		<script src="/js/contextMenu/jquery.contextMenu.js" type="text/javascript"></script>
		<script src="/js/jquery.ui.touch-punch.min.js" type="text/javascript"></script>

		<script src="/js/d3/d3.min.js" type="text/javascript"></script>
		<script src="/js/nvd3/nv.d3.min.js" type="text/javascript"></script>
		<link href="/js/nvd3/nv.d3.min.css" rel="stylesheet">
		
		<script type="text/javascript" src="/js/gridster/jquery.gridster.min.js"></script>
		
		<!-- Pandora Library -->
		<script src="/js/service/lib.js?v=1.1"></script>
		<script src="/js/service/extension.js?v=1.1"></script>
		<script src="/js/service/dashboard.js?v=1.1"></script>
		<script src="/js/service/workview_chart_classes.js?v=1.1"></script>
		<script src="/js/service/workview_chart_editor.js?v=1.1"></script>
		<script src="/js/service/workview_editor.js?v=1.1"></script>
		<script src="/js/service/workview_data_editor.js?v=1.2"></script>
		<script src="/js/service/activities.form.js" type="text/javascript"></script>
		<script src="/js/numberFormat.js"></script>

		<link href="/css/workview.css" rel="stylesheet">
		<link href="/css/workview_themes/default.css" rel="stylesheet">
    	<link rel="stylesheet" type="text/css" href="/js/bootstrap-wysihtml5/bootstrap-wysihtml5.css" />
				
		<!-- WYSI Editor Plugin -->
		<script type="text/javascript" src="/js/bootstrap-wysihtml5/wysihtml5-0.3.0.js"></script>
		<script type="text/javascript" src="/js/bootstrap-wysihtml5/bootstrap-wysihtml5.js"></script>

		<!-- jQuery Chosen Plugin -->
		<script src="/js/chosen.jquery.min.js"></script>
		<link href="/css/chosen.min.css" rel="stylesheet">
		
		
		
		<!-- <script type="text/javascript" src="/js/data-tables/DT_bootstrap.js"></script> -->

		<script>
		var server_id = <? echo $server_id;?>;
		<?
		echo "	var model_detail = {\"id\" : \"".$id."\"};\r\n";
		?>
		var forms = [];
		var debug_mode = null;
		</script>
		
		<style>
			i.toggle {
				cursor:pointer;
			}
			
			.chosen-container {
				text-align: left;
			}
			<?	
				
				
			if( property_exists($activity_contents,"styles") ) {
				for($i=0;$i<count($activity_contents->styles);$i++) {
					$style = $activity_contents->styles[$i];
					echo ".".$style->name." {".$style->css."}\r\n";
				}
			}
			
			$headerBackground = "#EEE";
			$headerBorder = "#DDD";
			$fontColor = "#000";
			
			echo "td.h {border:1px solid ".$headerBorder.";background:".$headerBackground.";color:".$fontColor.";}\r\n";
			?>
					
			/* Gridster styles */
			.demo {
				margin: 3em 0;
				padding: 7.5em 0 5.5em;
				background: #004756;
			}

			.gridster {
				width: 100%;
				margin: 0 auto;
			}

			.gridster .gs-w {
				background: #FFF;
				-webkit-box-shadow: 0 0 5px rgba(0,0,0,0.3);
				box-shadow: 0 0 5px rgba(0,0,0,0.3);
			}

			.gridster .player {
				-webkit-box-shadow: 3px 3px 5px rgba(0,0,0,0.3);
				box-shadow: 3px 3px 5px rgba(0,0,0,0.3);
			}


			.gridster .gs-w.try {
				background-image: url(../img/sprite.png);
				background-repeat: no-repeat;
				background-position: 37px -169px;

			}
			
			ul.gridsterList {
				list-style: none;
			}

			.gridster .preview-holder {
				border: none!important;
				border-radius: 0!important;
				background: rgba(255,255,255,.2)!important;
			}

			
			.row {
				margin-left: 0px !important;
				margin-right: 0px !important;
			}
			.col-md-12 {
				margin-left: 0px !important;
				margin-right: 0px !important;
			}
			.nvtooltip h3 {
				font-size: 14px !important;
			}
			
			td.h {
				cursor: default !important;
			}
			td.c {
				cursor: default !important;
			}
			
			
			@media (max-width: 479px) {
				body{
					margin-top:0px !important;
				}
			}
		</style>
		
		<title>MODLR » <? echo $model_name;?> » <? echo $activity_name;?> » <? echo $title;?></title>		
<?
$isiPad = (bool) strpos($_SERVER['HTTP_USER_AGENT'],'iPad');
?>
</head>
<body>

  <section id="container">
      
      <!--main content start-->
      <section id="main-content" style="margin-left: 0px;">
          <section class="wrapper" style='margin: 0px;padding: 0px;'>
              <!-- page start-->
<?



if( $page_contents->style == "STANDARD" ) {
			?>
<div class="row">
	<div class="col-md-12">

		<section class="panel" style="margin: 0px;">
			<div class="panel-body btn-gap" style="padding: 10px;margin-top: 15px;margin-bottom: 10px;">
	<?
} 
		
		if( $page_contents->type == "dashboard" ) {
			
			if( property_exists($page_contents,"header") ) {
				echo $page_contents->header->contents;
			}
			
		?>
		<div class="gridster" style='margin-top:10px;'>
			<ul class="gridsterList">
			</ul>
		</div>
		<script>
		<?
			echo "\r\n";
			echo "var dashboardData = ". $page_contents->contents;
?>
		// sort serialization
      dashboardData = Gridster.sort_by_row_and_col_asc(dashboardData);

	  var tileWidth = 80;
	  var tileHeight = 40;
	  
	  var gridster = null;
      $(function(){
	  
		var win = $(this);
		var windowWidth = win.width();
		
		var bOverrideSettings = false;
		
		var maxTileWidth = 0;
		$.each(dashboardData, function() {
			var size = this.size_x * tileWidth+65;
			if( size > maxTileWidth )
				maxTileWidth = size;
		});
		
		
		if( windowWidth < maxTileWidth+20 ) {
			bOverrideSettings = true;
			tileWidth = windowWidth-65;
			
		}
		
		
		

        gridster = $(".gridster ul").gridster({
			widget_base_dimensions: [tileWidth, tileHeight],
			widget_margins: [5, 5]
        }).data('gridster');
		gridster.disable();
		gridster.remove_all_widgets();
		var count = 1;
		$.each(dashboardData, function() {
			if( bOverrideSettings ) {
				this.size_x = 1;
				this.row = count;
				this.col = 1;
				count+=this.size_y;
			}
			var widget = gridster.add_widget('<li />', this.size_x, this.size_y, this.col, this.row);
			setDataset(widget[0],"type",this.type);
			setDataset(widget[0],"workview",this.workview);
			setDataset(widget[0],"dimension",this.dimension);
			setDataset(widget[0],"element",this.element);
			setDataset(widget[0],"html",this.html);
			setDataset(widget[0],"filter",this.filter);
			setDataset(widget[0],"id","w" + getDataset(widget[0],"col") + "_" + getDataset(widget[0],"row"));
			processWidget(widget[0]);
		});
		processQueue();
		
		});
		</script>
		
		<?
		
			if( property_exists($page_contents,"footer") ) {
				echo $page_contents->footer->contents;
			}
		
		} else if( $page_contents->type == "form" ) {
			
			echo "<div style='margin-left: 10px;margin-right: 10px;'>";
			echo $page_contents->contents_prior;
			
			$table_definition = $page_contents->table_definition;
			
			$primary_id = "";
			$record = array();
			if( property_exists( $page_contents , "record" ) ) {
				$record = $page_contents->record;
				$primary_id = $page_contents->primary_id;
			}
			
			$fields = array();
			if( property_exists( $page_contents , "fields" ) ) {
				//this is returned in an odd way due to how php json_decode works without the second argument defined.
				$fieldsObject = $page_contents->fields;
				
				if( !is_array($fieldsObject) ) {
					//fix this into the javascript array string. 
					for($i=0;$i<100;$i++) {
						if( property_exists( $fieldsObject , "" . $i ) ) {
							array_push($fields, $fieldsObject->{ "" . $i});
						} else {
							break;
						}
					}
				} else {
					$fields = $fieldsObject;
				}
			}
			
?>
<div class="panel-body" id="panel<? echo $page_id;?>">
	<form action="#" class="form-horizontal " method="post" name="form<? echo $page_id;?>" id="form<? echo $page_id;?>">
		
	</form>
</div>
<script type='text/javascript'>

//define the form
var form = {};

form.id = "<? echo $page_id;?>";

form.fieldList = <? echo json_encode($fields);?>;
form.record = <? echo json_encode($record);?>;
form.method = "<? echo $page_contents->method;?>";

form.primary_id = "<? echo $primary_id;?>";

form.pageSuccess = "<? echo $page_contents->pageSuccess;?>";
form.pageBack = "<? echo $page_contents->pageBack;?>";
form.table_definition = <? echo json_encode($table_definition);?>;

forms[forms.length] = form;

$( document ).ready(function() {
	setupForm(form.id);
});

</script>

<?
			echo $page_contents->contents_post;
			echo "</div>";
			
		} else {
			echo $page_contents->contents;
		}
		
		if( $page_contents->style == "STANDARD" ) {
			?>
			</div>
		</section>
	</div>
</div>
			<?
			
		} 

?>
		<!-- page end-->
        </section>
    </section>
    <!--main content end-->
</section>		

<div id="dialog-loading" title="Loading..." style='display:none;'>
	<p><br/>
		<center>
		<span class='loadingAnimation'><img src='/img/loader.gif'/><br/><br/></span>
		<span id='txtLoading'>Communicating with the analytics server.</span></center>
		
	</p>
</div>

<?
include_once("../lib/footer.php");
?>
