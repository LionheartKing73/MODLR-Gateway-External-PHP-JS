<?
include_once("../lib/lib.php");


$action = querystring("action");
$id = querystring("id");
$activityid = querystring("activityid");
$page_id = querystring("page");
$advanced_editor = querystring("advanced");
$serverside_editor = querystring("serverside");
$dashboard_editor  = querystring("dashboard");

$title = form("title");
$style = form("style");
$header_page = form("header_page");
$footer_page = form("footer_page");
if( form("advanced") != "" ) {
	$advanced_editor = form("advanced");
}
if( form("serverside") != "" ) {
	$serverside_editor = form("serverside");
}
if( form("dashboard") != "" ) {
	$dashboard_editor = form("dashboard");
}

$page_contents = form("page_contents");



if( $page_contents == "" ) {
	
	$page_contents = "<b>Hello Collaborator!</b>";
	
	if( $serverside_editor == "1" ) {
		$page_contents = "//Server side pages allow you to develop restful JSON callables using Javascript.\r\n";
		$page_contents .= "//The script executes within the MODLR instance providing access to the full MODLR Library of functions.\r\n\r\n";
		$page_contents .= "function request(post) {\r\n";
		$page_contents .= "\tvar result = {\"success\" : true};\r\n\t\r\n\t\r\n\treturn JSON.stringify(result)\r\n}\r\n";
	}
}

$type = "simple";
if( $serverside_editor == "1" ) {
	$type = "server-side";
} else if( $advanced_editor == "1" ) {
	$type = "advanced";
} else if( $dashboard_editor == "1" ) {
	$type = "dashboard";
}


if( $action == "save" ) {
	
	$encoded = $page_contents;
	
	
	$def = array(
		'pageid' => $page_id,
		'type' => $type,
		'name' => $title,
		'style' => $style,
		'contents' => $encoded,
		'header_page' => $header_page,
		'footer_page' => $footer_page
	);
	
	
	$logRecord = PageChangesLog($encoded, $id, $activityid, $page_id);
	
	$json = "{\"tasks\": [";
	$json .= "{\"task\": \"activity.page.create.update\", \"id\":\"" . $id . "\", \"activityid\":\"" . $activityid . "\", \"definition\":" . json_encode($def, JSON_FORCE_OBJECT) . "}";
	$json .= "]}";

	$results = api_short(SERVICE_MODEL, $json);
	
	if(  intval($results->results[0]->result) == 1  ) {
		$page_id = $results->results[0]->pageid;
	}
	
	/*
	if( $type == "advanced" || $type == "server-side" ) {
		echo $page_id;
		die();
	}
	*/
	
	redirectToPage ("/activity/page/?id=" . $id . "&activityid=" . $activityid . "&page=".$page_id."&action=open" );
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
			
			if( $advanced_editor == "0" && $dashboard_editor == "0" ) {
			
			} else {
				if( $type == "advanced" ) {
					$advanced_editor = "1";
				} else if( $type == "server-side"  ) {
					$serverside_editor = "1";
				} else if( $type == "dashboard" ) {
					$dashboard_editor = "1";
					if( property_exists( $results->results[2], "header_page") ) {
						$header_page = $results->results[2]->header_page;
					}
					if( property_exists( $results->results[2], "footer_page") ) {
						$footer_page = $results->results[2]->footer_page;
					}
					
				} else {
					
				}
			}
			
			$style = "";
			if( property_exists( $results->results[2] , "style" )) {
				$style = $results->results[2]->style;
			}
			$page_contents = $results->results[2]->contents;
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
    	
		<link rel="stylesheet" href="/js/codemirror/addon/display/fullscreen.css" rel="stylesheet">
		<link rel="stylesheet" href="/js/codemirror/addon/dialog/dialog.css">
		<link rel="stylesheet" href="/js/codemirror/addon/search/matchesonscrollbar.css">

		<title>MODLR » <? echo $activity_name;?> » Page Editor</title>
		
		<script>
		var server_id = <? echo session("active_server_id");?>;
		var model_detail = <? echo json_encode($model_contents);?>;
		var debug_mode = true;
		var page_type = "<? echo $type;?>";
		var model_id = "<? echo $id;?>";
		var activity_id = "<? echo $activityid;?>";
		var page_id = "<? echo $page_id;?>";
		var user_id = "<? echo session("uid");?>";
				
		function goFullToggle(bFullScreen) {
			if( bFullScreen ) {
				$(".fixed-top").css("position","initial");
			} else {
				$(".fixed-top").css("position","fixed");
			}
		}
		
		</script>
		<style>
			<?	
			
			
			if( property_exists($model_contents,"styles") ) {
				for($i=0;$i<count($model_contents->styles);$i++) {
					$styleCss = $model_contents->styles[$i];
					echo ".".$styleCss->name." {".$styleCss->css."}\r\n";
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
				cursor: pointer;
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

			.nvtooltip h3 {
				font-size: 14px !important;
			}

			
			td.h {
				cursor: default !important;
			}
			td.c {
				cursor: default !important;
			}
			
			.ui-front {
				z-index: 10000;
			}
			.CodeMirror {
			  border: 1px solid #eee;
			  height: 550px;
			}
		</style>
<?
include_once("../lib/body_start.php");
?>
		<div class="row">
            <div class="col-md-12">
                <section class="panel">
                    <header class="panel-heading">
                        Page Editor
                    </header>
                    <div class="panel-body">
                        <form action="/activity/page/?id=<? echo $id;?>&activityid=<? echo $activityid;?>&page=<? echo $page_id;?>&action=save" class="form-horizontal " method='post' name='pageUpdateForm'>
                        
                        	<div class="form-group">
								<label class="control-label col-md-1" for="title">Page Title</label>
								<div class="col-md-11">
									<input class="form-control" type="text" class="form-control" id="title" name="title" placeholder="Enter Page Title" value="<? echo $title;?>">
									<input type="hidden" id="advanced" name="advanced" value="<? echo $advanced_editor;?>">
									<input type="hidden" id="serverside" name="serverside" value="<? echo $serverside_editor;?>">
                                </div>
							</div>
<?
if( $serverside_editor == "1" ) {
	echo "<input type='hidden' value='' id='style' name='style'/>";
} else {

?>
                        	<div class="form-group">
								<label class="control-label col-md-1" for="title">Page Format</label>
								<div class="col-md-11" >
									<select class="form-control" id='style' name='style'>
										<option value='STANDARD'>Standard White Panel</option>
<?
	$selected = "";
	if( $style == "NONE" )
		$selected = " SELECTED";
?>
										<option value='NONE'<? echo $selected;?>>Blank page</option>
									</select>
                                </div>
							</div>
<?
}
?>
                            <div class="form-group">
                                
<?
if( $dashboard_editor == "0" ||  $dashboard_editor == "" ) {
?>
                                <label class="control-label col-md-1">Page Contents</label>
								<div class="col-md-11">
                                    <textarea class="wysihtml5 form-control" id='page_contents' name='page_contents' rows="45" style=''><? echo htmlentities($page_contents);?></textarea>
								</div>
<?
} else {
?>
                                <label class="control-label col-md-1">Header Page</label>
								<div class="col-md-11">
                                    <select class="form-control" id='header_page' name='header_page'>
								
<?
if( $header_page == "" ) {
	echo "<option value='' selected >None</option>";
} else {
	echo "<option value=''>None</option>";
}

$pages = $activity_contents->pages;
for($i=0;$i<count($pages);$i++) {
	$page = $pages[$i];
	$selected = "";
	if( $page->type != "server-side" && $page->type != "dashboard" ) {
		if( $page->pageid == $header_page ) {
			$selected = " selected";
		}
		echo "<option value='".$page->pageid."'".$selected .">".$page->name."</option>";
	}
}
?>
										
									</select>
								</div>
							</div>
                            <div class="form-group">
                                <label class="control-label col-md-1">Footer Page</label>
								<div class="col-md-11">
                                    <select class="form-control" id='footer_page' name='footer_page'>
								
<?
if( $footer_page == "" ) {
	echo "<option value='' selected >None</option>";
} else {
	echo "<option value=''>None</option>";
}

$pages = $activity_contents->pages;
for($i=0;$i<count($pages);$i++) {
	$page = $pages[$i];
	$selected = "";
	if( $page->type != "server-side" && $page->type != "dashboard" ) {
		if( $page->pageid == $footer_page ) {
			$selected = " selected";
		}
		echo "<option value='".$page->pageid."'".$selected .">".$page->name."</option>";
	}
}
?>
										
									</select>
								</div>
							</div>
                            <div class="form-group">
								
<div class="gridster">
    <ul class="gridsterList">
        <li data-row="1" data-col="1" data-sizex="6" data-sizey="6"></li>
        <li data-row="1" data-col="7" data-sizex="3" data-sizey="6"></li>
 
    </ul>
</div>
<textarea class="wysihtml5 form-control" id='page_contents' name='page_contents' rows="9" style='height:300px;display:none;'>
<? echo $page_contents;?></textarea>
<input type="hidden" id="dashboard" name="dashboard" value="<? echo $dashboard_editor;?>">
<?
}
?>
                                
                            </div>
<?
if( $dashboard_editor == "0" ||  $dashboard_editor == "" ) {
?>
	<div class="form-group">
		<label class="control-label col-md-1" for="title">Revision History</label>
		<div class="col-md-10" >
			<select class="form-control" id='revisions' name='revisions'>
<?
$db = new db_helper();
$db->CommandText("SELECT change_id, users.name ,change_user_id,change_added,users_development_logs.document FROM modlr.users_development_logs LEFT JOIN users ON users.id = users_development_logs.change_user_id WHERE users_development_logs.server_id='%s' AND page_id='%s'  AND page_id <> '' ORDER BY change_added DESC LIMIT 0,20;");
$db->Parameters(session("active_server_id"));
$db->Parameters($page_id);
$db->Execute();
while($r = $db->Rows()) {
	$change_id = $r['change_id'];
	$name = $r['name'];
	$change_user_id = $r['change_user_id'];
	$change_added = $r['change_added'];
	$document = $r['document'];
	
	
	$dateStr = date('F j, Y, g:i a', strtotime($change_added) );
	echo "<option value='".$change_id."' data-document='".htmlentities($document,ENT_QUOTES)."'>".$name." made a change ".time2str($change_added)." (".$dateStr.")</option>";
	
}
?>
			</select>
		</div>
		<div class="col-md-1" >
			<button type="button" class="btn btn-success" style='width:100%;' onclick='viewRevision();'>View</button>
		</div>
	</div>
	
	<div class="form-group" style='display:none;' id='revision_group' >
		<label class="control-label col-md-1">Revision Code</label>
		<div class="col-md-11" id='revision_panel'>
			
		</div>
	</div>
<?
}
?>

						
							<div style='width:100%;text-align:right;'>
								<?
								if( $dashboard_editor == "1"  ) {
								?>
								<button type="button" class="btn btn-default" style='margin-left:10px;' onclick='addWidget();'>Add Tile</button>
								<?
								}
								?>
								
								<button type="button" class="btn btn-success" style='margin-left:10px;' onclick='savePage();'>Save Page</button>
								
								<?
								/*
								if( $advanced_editor == "1"  ) {
								?>
								<button type="button" class="btn btn-default" style='margin-left:10px;' onclick='changeMode(1,0);'>Swap to Dashboard Editor</button>
								<button type="button" class="btn btn-default" style='margin-left:10px;' onclick='changeMode(0,0);'>Swap to Basic Editor</button>
								<?
								} else if( $dashboard_editor == "1"  ) {
								?>
								
								
								
								<button type="button" class="btn btn-default" style='margin-left:10px;' onclick='changeMode(0,1);'>Swap to Advanced Editor</button>
								<button type="button" class="btn btn-default" style='margin-left:10px;' onclick='changeMode(0,0);'>Swap to Basic Editor</button>
								<?
								} else {
								?>
								<button type="button" class="btn btn-default" style='margin-left:10px;' onclick='changeMode(0,1);'>Swap to Advanced Editor</button>
								<button type="button" class="btn btn-default" style='margin-left:10px;' onclick='changeMode(1,0);'>Swap to Dashboard Editor</button>
								<?
								}
								*/
								?>
								<button type="button" class="btn btn-danger" style='margin-left:10px;' onclick='window.location="/activity/?id=<? echo $id;?>&activityid=<? echo $activityid;?>";'>Close</button>
							</div>
                        </form>
                    </div>
                </section>
            </div>
        </div>
<?
include_once("../lib/body_end.php");

if( $dashboard_editor == "" )
	$dashboard_editor = "0";
if( $advanced_editor == "" )
	$advanced_editor = "0";
if( $serverside_editor == "" ) 
	$serverside_editor = "0";
?>
<script>

	
	var dashboard = "<? echo  $dashboard_editor;?>";
	var advanced = "<? echo  $advanced_editor;?>";
	var serverside = "<? echo $serverside_editor;?>";

</script>

<!-- Pandora Library -->
<script type="text/javascript" src="/js/service/page.js"></script>
<script type="text/javascript" src="/js/service/dashboard.js?v=1.1"></script>
<script type="text/javascript" src="/js/service/workview_chart_classes.js?v=1.1"></script>
<script type="text/javascript" src="/js/service/workview_chart_editor.js?v=1.1"></script>
<script type="text/javascript" src="/js/service/workview_editor.js?v=1.1"></script>
<script type="text/javascript" src="/js/service/workview_data_editor.js?v=1.2"></script>
<script type="text/javascript" src="/js/numberFormat.js"></script>


<!-- Theme Library -->
<script type="text/javascript" src="/js/bootstrap-wysihtml5/wysihtml5-0.3.0.js"></script>
<script type="text/javascript" src="/js/bootstrap-wysihtml5/bootstrap-wysihtml5.js"></script>
<script type="text/javascript" src="/js/codemirror/codemirror.js"></script>

		<script src="/js/d3/d3.min.js" type="text/javascript"></script>
		<script src="/js/nvd3/nv.d3.min.js" type="text/javascript"></script>
		
<script type="text/javascript" src="/js/codemirror/xml/xml.js"></script>
<script type="text/javascript" src="/js/codemirror/javascript/javascript.js"></script>
<script type="text/javascript" src="/js/codemirror/css/css.js"></script>
<script type="text/javascript" src="/js/codemirror/vbscript/vbscript.js"></script>
<script type="text/javascript" src="/js/codemirror/htmlmixed/htmlmixed.js"></script>

<script type="text/javascript" src="/js/codemirror/addon/dialog/dialog.js"></script>
<script type="text/javascript" src="/js/codemirror/addon/search/searchcursor.js"></script>
<script type="text/javascript" src="/js/codemirror/addon/search/search.js"></script>
<script type="text/javascript" src="/js/codemirror/addon/scroll/annotatescrollbar.js"></script>
<script type="text/javascript" src="/js/codemirror/addon/search/matchesonscrollbar.js"></script>
<script type="text/javascript" src="/js/codemirror/addon/search/jump-to-line.js"></script>
<script type="text/javascript" src="/js/codemirror/addon/display/fullscreen.js"></script>


<script type="text/javascript" src="/js/gridster/jquery.gridster.min.js"></script>




<?
if( $advanced_editor == "1"  ) {
	//advanced editor
?>

<script>
var editor = null;
$(function(){
  	var te = document.getElementById("page_contents");
	var mixedMode = {
        name: "htmlmixed",
        scriptTypes: [{matches: /\/x-handlebars-template|\/x-mustache/i,
                       mode: null},
                      {matches: /(text|application)\/(x-)?vb(a|script)/i,
                       mode: "vbscript"}]
    };
    editor = CodeMirror.fromTextArea(te, {
		lineNumbers: true,
                mode: mixedMode, 
		viewportMargin: 20,    
		  extraKeys: {
			"F11": function(cm) {
				var newToggle = !cm.getOption("fullScreen");
				goFullToggle(newToggle);
				cm.setOption("fullScreen", newToggle);
			},
			"Esc": function(cm) {
				goFullToggle(false);
				if (cm.getOption("fullScreen")) cm.setOption("fullScreen", false);
			}
		  }
    });
});
</script>
<?
 } else if( $serverside_editor == "1"  ) {
 	//serverside editor
?>

<script>
var editor = null;
$(function(){
  var te = document.getElementById("page_contents");
		var mixedMode = {
        name: "javascript",
        scriptTypes: [{matches: /\/x-handlebars-template|\/x-mustache/i,
					   mode: null},
					  {matches: /(text|application)\/(x-)?vb(a|script)/i,
					   mode: "vbscript"},
					   {matches: /\/text|\/x-rsrc/i,
					   mode: "r"}]
    };
    editor = CodeMirror.fromTextArea(te, {
		lineNumbers: true,
    	mode: mixedMode,
		viewportMargin: 20,
      extraKeys: {
        "F11": function(cm) {
			var newToggle = !cm.getOption("fullScreen");
			goFullToggle(newToggle);
			cm.setOption("fullScreen", newToggle);
        },
        "Esc": function(cm) {
			goFullToggle(false);
			if (cm.getOption("fullScreen")) cm.setOption("fullScreen", false);
        }
      }
    });
});


</script>
<?
 } else if( $dashboard_editor == "1"  ) {
	//dashboard editor
?>

	<div id="dlgEditWidget" title="Tile Editor" style='display:none;'>
  		<div id='dlgEditWidgetMessage' style='display:none;'></div>
  		
		<table width="100%">
			<tr style="height:28px;">
				<td style='width:120px;'><b>Type:&nbsp;</b></td>
				<td>
					<select id='widget-type-select' ><option>Workview</option><option>Workview Visualisation</option><option>Selectable</option><option>Page</option><option>Custom</option></select>
				</td>
			</tr>
			<tr id='workviewSelectRow' style="height:28px;">
				<td><b>Workview:&nbsp;</b></td>
				<td>
					<select id='widget-workview-select' >
						<?
						$workviews = $model_contents->workviews;
						for($i=0;$i<count($workviews);$i++) {
							$workview = $workviews[$i];
							echo "<option value='".$workview->id."'>".$workview->name."</option>";
						}
						?>
					</select>
				</td>
			</tr>
			<tr id='pageSelectRow' style="height:28px;">
				<td><b>Page:&nbsp;</b></td>
				<td>
					<select id='widget-page-select' >
						<?
						$pages = $activity_contents->pages;
						for($i=0;$i<count($pages);$i++) {
							$page = $pages[$i];
							if( $page->type != "server-side" && $page->type != "dashboard" ) {
								echo "<option value='".$page->pageid."'>".$page->name."</option>";
							}
						}
						?>
					</select>
				</td>
			</tr>
			<tr id='customEditorRow'>
				<td colspan='2'>
					<textarea class="form-control" id='custom-widget-editor' name='custom-widget-editor' style='height:180px;'></textarea>
					<script>
					var editor_widget = null;
					$(function(){
						var te = document.getElementById("custom-widget-editor");
						var mixedMode = {
							name: "htmlmixed",
							scriptTypes: [{matches: /\/x-handlebars-template|\/x-mustache/i,
										   mode: null},
										  {matches: /(text|application)\/(x-)?vb(a|script)/i,
										   mode: "vbscript"}]
						};
						editor_widget = CodeMirror.fromTextArea(te, {
							lineNumbers: false,
							height:'180px',
                                                        viewportMargin: 20,
							mode: mixedMode
						});
					});
					</script>
				</td>
			</tr><tr id='selectableEditorRow' style='height:28px;'>
				<td><b>Workview:&nbsp;</b></td>
				<td>
					<select id='widget-existing-workview-select' >
						
					</select>
				</td>
			</tr><tr id='selectableEditorRowDimension' style='height:28px;'>
				<td><b>Title Dimension:&nbsp;</b></td>
				<td>
					<select id='widget-workview-dimension-select' >
						
					</select>
				</td>
			</tr>
			</tr><tr id='selectableEditorRowWidgets' style='height:28px;'>
				<td style="vertical-align: top;"><b>Selected Widgets:&nbsp;</b></td>
				<td id='rowWidgets'>
					
				</td>
			</tr>
		</table>
		
		
	</div>

<script>

	  var tileWidth = 80;
	  var tileHeight = 40;


<?
if( $action == "open" ) {
	echo "var dashboardData = ". $page_contents;
?>
// sort serialization

      dashboardData = Gridster.sort_by_row_and_col_asc(dashboardData);

      $(function(){

        gridster = $(".gridster ul").gridster({
			widget_base_dimensions: [tileWidth, tileHeight],
			widget_margins: [5, 5],
			resize: {
				enabled: true,
				stop: function(e, ui, $widget) {
					refresh();
				}	
			},
			serialize_params: function ($w, wgd) {      
				return {
					id: $w.attr('id'),
					type: $w.data('type'),
					workview: $w.data('workview'),
					workview_name: $w.data('workview_name'),
					dimension: $w.data('dimension'),
					dimension_name: $w.data('dimension_name'),
				    element: $w.data('element'),
					page: $w.data('page'),
					page_name: $w.data('page_name'),
					filter: $w.data('filter'),
					html: $w.data('html'),
					links: $w.data('links'),
					/* defaults */
					col: wgd.col,
					row: wgd.row,
					size_x: wgd.size_x,
					size_y: wgd.size_y
				  }
			  }
        }).data('gridster');

		gridster.remove_all_widgets();
		$.each(dashboardData, function() {
			var widget = gridster.add_widget('<li />', this.size_x, this.size_y, this.col, this.row);
			setDataset(widget[0],"type",this.type);
			setDataset(widget[0],"workview",this.workview);
			setDataset(widget[0],"workview_name",this.workview_name);
			setDataset(widget[0],"page",this.page);
			setDataset(widget[0],"page_name",this.page_name);
			setDataset(widget[0],"dimension",this.dimension);
			setDataset(widget[0],"dimension_name",this.dimension_name);
			setDataset(widget[0],"element",this.element);
			setDataset(widget[0],"filter",this.filter);
			setDataset(widget[0],"id","w" + getDataset(widget[0],"col") + "_" + getDataset(widget[0],"row"));
			setDataset(widget[0],"html",this.html);
		});

      });
<?
} else {
?>
$(function(){ //DOM Ready
    gridster = $(".gridster ul").gridster({
        widget_margins: [5, 5],
        widget_base_dimensions: [tileWidth, tileHeight],
        resize: {
            enabled: true,
			stop: function(e, ui, $widget) {
				refresh();
			}	
        },
		serialize_params: function ($w, wgd) {      
          return {
              id: $w.attr('id'),
			  type: $w.data('type'),
			  workview: $w.data('workview'),
			  workview_name: $w.data('workview_name'),
			  page: $w.data('page'),
			  page_name: $w.data('page_name'),
			  dimension: $w.data('dimension'),
			  dimension_name: $w.data('dimension_name'),
			  element: $w.data('element'),
			  filter: $w.data('filter'),
			  html: $w.data('html'),
			  links: $w.data('links'),
              /* defaults */
              col: wgd.col,
              row: wgd.row,
              size_x: wgd.size_x,
              size_y: wgd.size_y
          }
      }
    });
});
<?
}
?>




	var widgetInnerHTML = '<div class="toolbox"><div style="position:absolute;cursor:pointer;font-size:20px;margin:4px;z-index:1000;"><i class="fa fa-pencil" style="padding:2px;" onclick="editWidget(this);"></i><i class="fa fa-times" style="padding:2px;" onclick="removeWidget(this);"></i></div></div>';

	
	setTimeout(  
		function() {
			finishedUpdatingNotify = updateWidgetEditor;
			refresh();
		}
	,
	500 
	);
	
	
</script>
<!-- jQuery Chosen Plugin -->
<script src="/js/chosen.jquery.min.js"></script>

<?
} else {
	//simple editor
?>
<script>
$(function(){
	$('.wysihtml5').wysihtml5();
});
</script>
<?
}
?>
<script type="text/javascript">

$( document ).ready(function() {
	$(document).bind('keydown', function(e) {
		if((e.ctrlKey || e.metaKey) && e.which == 83 || e.altKey && e.which == 83) {
			e.preventDefault();
			$("button[onclick='savePage();']").click();
			return false;
		   
		}
		if((e.ctrlKey || e.metaKey) && e.which == 81 || e.altKey && e.which == 81) {
			e.preventDefault();
			$("button[class='btn btn-danger']").click();
			return false;
		}
	});
});
</script>
<?
include_once("../lib/footer.php");
?>