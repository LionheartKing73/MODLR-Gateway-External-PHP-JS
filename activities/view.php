<?
include_once("../lib/lib.php");
$id = querystring("id");
$activity_id = querystring("activityid");
$server_id = querystring("serverid");
$title = querystring("title");

$header = querystring("header");
$footer = querystring("footer");

$activity_contents = null;
$screen = null;
if( $id != ""  &&  $activity_id != ""  &&  $server_id != "" ) {

	$json = "{\"tasks\": [";
	$json .= "{\"task\": \"activity.get\", \"id\":\"" . $id . "\", \"activityid\":\"" . $activity_id . "\"}";
	$json .= "]}";

	$resultsActivity = api_short_efficient(SERVICE_COLLABORATOR, $json , $server_id);
	
	if( !property_exists( $resultsActivity->results[0] , "result" ) ) {
		echo "<!-- ";
		print_r($resultsActivity);
		echo "-->";
		die();
		$resultsActivity = api_short_efficient(SERVICE_COLLABORATOR, $json , $server_id);
	}
	
	
	if(  intval($resultsActivity->results[0]->result) == 1 ) { 
		
		$activity_contents = $resultsActivity->results[0]->activity;
		$activity_name = $activity_contents->name;
		
		$model_name = $activity_contents->model_name;
		
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
				if( $screen->title == $title || $screen->id == $title ) {
					$bFound = true;
					$title = $screen->title;
					break;
				}
			}
			if( !$bFound ) {
				if( count($screens) == 0 ) {
					$title = "Limited Access";
					$screen = null;
				} else {
					$title = $screens[0]->title;
					$screen = $screens[0];
				}
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

$navigation_mode = "true";
if( property_exists($activity_contents, "navigation" ) ) {
	$navigation_mode = $activity_contents->navigation;
}

include_once("../lib/header.php");
?>
		<style>
		
		
		@media print and (color) {
		   * {
			  -webkit-print-color-adjust: exact;
			  print-color-adjust: exact;
		   }
		}
		
		
		
		
		#page0 {
			/*overflow:scroll;*/
			-webkit-overflow-scrolling:touch
		}
		#page1 {
			/*overflow:scroll;*/
			-webkit-overflow-scrolling:touch
		}
		#page2 {
			/*overflow:scroll;*/
			-webkit-overflow-scrolling:touch
		}
		
		nav.navbar-inverse {
			border-radius: 0px;
			padding-left:15px;
			padding-right:15px;
			margin-bottom: 0px;
		}
		
		li.nav-bar-button {
			color: #fff;
			padding: 10px;
			margin-top: 8px;
			cursor:pointer;
		}
		
		li.dropdown {
			font-size:14px;
		}
		
		
		.navbar-inverse .navbar-nav .open .dropdown-menu > li > span {
			
			border-radius: 0px;
			padding: 10px;
			line-height: 25px;
			cursor: pointer;
		}
		
		@media (max-width: 479px) {
			body{
				margin-top:0px !important;
			}
			.navbar-brand {
				float: none;
				padding-left: 1px;
				padding-top: 3px;
			}
			
			
			nav.navbar-inverse {
				padding-top: 3px;
				padding-left:5px;
				padding-right:5px;
				color: #fff;
			}
			
			div.col-md-12 {
				padding-left: 0px;
				padding-right: 0px;
			}
			
			li.nav-bar-button {
				padding: 6px;
				margin-left: 8px;
				display:inline;
				float: right;
				font-size:23px;
			}
		}
		
		.dropdown-menu {
			min-width:270px !important;
			padding: 8px;
			cursor: pointer;
		}
				
				
		@media (min-width: 100px) and (max-width: 767px) {
			section.panel {
				overflow:scroll !important;
			}
			
			a.navbar-brand {
				position: relative;
				top:15px;
				overflow:hidden;
			}
		}
		
		
		</style>
			
		<!--Core js-->
		<script src="/js/jquery.js"></script>
		<script src="/bs3/js/bootstrap.min.js"></script>
		<script class="include" type="text/javascript" src="/js/jquery.dcjqaccordion.2.7.js"></script>
		<script src="/js/jquery.scrollTo.min.js"></script>
		<script src="/js/jQuery-slimScroll-1.3.0/jquery.slimscroll.js"></script>
		<script src="/js/jquery.nicescroll.js"></script>
		<script type="text/javascript" src="/js/gritter/js/jquery.gritter.js"></script>


		<script src="/js/jquery-ui-1.10.3.custom.min.js"></script>

		<script src="/js/contextMenu/jquery.ui.position.js" type="text/javascript"></script>
		<script src="/js/contextMenu/jquery.contextMenu.js" type="text/javascript"></script>
		<script src="/js/jquery.ui.touch-punch.min.js" type="text/javascript"></script>

		<!-- Pandora Library -->
		<script src="/js/service/lib.js"></script>
		<script src="/js/service/activities.view.js"></script>

		<script>
		
			 
		function resizeWindow() {
			var win = $(this);		  
				  
<?
if( $screen == null ) {
	//do nothing
	
	
	
} else {
	//update heights
	
	if( $navigation_mode == "true" ) {
		echo "var toolbarHeight = 60;";
	} else {
		echo "var toolbarHeight = 8;";
	}
	
	if( $screen->page_type == "single"  || $screen->page_type == "hidden"  ) {
		echo "$('#page0').css('height',(window.innerHeight-toolbarHeight) + 'px');";
	} else if( $screen->page_type == "two_vertical" ) {
		echo "$('#page0').css('height',(window.innerHeight-toolbarHeight) + 'px');";
		echo "$('#page1').css('height',(window.innerHeight-toolbarHeight) + 'px');";
	} else if( $screen->page_type == "two_horizontal" ) {
		$page_1_size = $screen->page_1_size;
        if( $page_1_size == "stack" ) {
            echo "$('#page0').css('height',(window.innerHeight-toolbarHeight) + 'px');";
        } else {
            $page_0_size = 100 - $page_1_size;
            echo "$('#page0').css('height',((window.innerHeight-toolbarHeight) * 0.".$page_0_size.") + 'px');";
            echo "$('#page1').css('height',((window.innerHeight-toolbarHeight) * 0.".$page_1_size.") + 'px');";
        }
	} else if( $screen->page_type == "three_vertical" ) {
		echo "$('#page0').css('height',(window.innerHeight-toolbarHeight) + 'px');";
		echo "$('#page1').css('height',(window.innerHeight-toolbarHeight) + 'px');";
		echo "$('#page2').css('height',(window.innerHeight-toolbarHeight) + 'px');";
	} else if( $screen->page_type == "three_horizontal" ) {
		$page_1_size = $screen->page_1_size;
		$page_2_size = $screen->page_2_size;
		$page_0_size = 100 - $page_1_size - $page_2_size;
		echo "$('#page0').css('height',((window.innerHeight-toolbarHeight) * 0.".$page_0_size.") + 'px');";
		echo "$('#page1').css('height',((window.innerHeight-toolbarHeight) * 0.".$page_1_size.") + 'px');";
		echo "$('#page2').css('height',((window.innerHeight-toolbarHeight) * 0.".$page_2_size.") + 'px');";
	}
}

?>
		}
		
		</script>
		<title>MODLR » <? echo $model_name;?> » <? echo $activity_name;?> » <? echo $title;?></title>		
<?
$isiPad = (bool) strpos($_SERVER['HTTP_USER_AGENT'],'iPad');




?>
</head>
<body style='overflow:hidden;'>

  <section id="container">
      
      <!--main content start-->
      <section id="main-content" style="margin-left: 0px;">
          <section class="wrapper" style='margin-top: 0px;padding:0px;'>
              <!-- page start-->

<?
//are we displaying the navigation bar?
if( $navigation_mode == "true" ) {
?>
			  
			<div class="row" style="margin-right: 0px;margin-left: 0px;">
				<!--navigation start-->
				<nav class="navbar navbar-inverse" role="navigation" style=''>
					<!-- Brand and toggle get grouped for better mobile display -->
					<div class="navbar-header">
						<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
							<span class="sr-only">Toggle navigation</span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
						</button>
						<a class="navbar-brand" href="#"><!-- <? echo $model_name;?> » --><? echo $activity_name;?> » <span id='screen_title'><? echo $title;?></span></a>
					</div>

					<!-- Collect the nav links, forms, and other content for toggling -->
					<div class="collapse navbar-collapse navbar-ex1-collapse">
						
					
						<ul class="nav navbar-nav navbar-right">
							<li class="dropdown">
								<a href="javascript:;" onclick="toggleMenu('dropmenu');" class="dropdown-toggle" data-toggle="dropdown">Screens <b class="caret"></b></a>
								<ul class="dropdown-menu" id='dropmenu'>
									<?
if( property_exists( $activity_contents, 'screens' ) ) {
	$screens = $activity_contents->screens;
	for($i=0;$i<count($screens);$i++) {
		$screenMenu = $screens[$i];
		$screenType = $screenMenu->page_type;
		
		if( $screenType != "hidden" ) {
			$screenUrl = '/activities/view/?id='.$id.'&activityid='.$activity_id.'&serverid='.$server_id.'&title='.urlencode($screenMenu->title);
			
			echo '<li><span onclick="window.location=\'' . $screenUrl .'\';toggleMenu(\'dropmenu\');">'.$screenMenu->title.'</span></li>';
		}
	}
}
									?>
								</ul>
							</li>
							<li class="nav-bar-button">
								<span class='' onclick='btnExport();'>
									<i style='color:#888;' id='btnExport' class="fa fa-download"></i>
								</span>
							</li>
							<li class="nav-bar-button">
								<span class='' onclick='refreshAll();'>
									<i class="fa fa-refresh"></i>
								</span>
							</li>
							<li class="nav-bar-button">
								<span class='' onclick="window.location='/activities/home/';">
									<i class="fa fa-times"></i>
								</span>
							</li>
						
						</ul>
					</div><!-- /.navbar-collapse -->
				</nav>
				<!--navigation end-->
			</div>

<?
//are we displaying the navigation bar?
}
?>
			<div class="row" style="margin-right: 0px;margin-left: 0px;">
				<div class="col-md-12" style="padding-right: 0px;padding-left: 0px;">
					<section class="" style='margin-bottom: 0px;'>
						<div class="" style="padding: 0px;-webkit-overflow-scrolling:touch;min-height: 200px;">
<?

function parsePageUrl($page, $url, $order,$id,$activity_id,$server_id) { 
	global $title;
    
    $querystring = $_SERVER["QUERY_STRING"];
    if( strpos( $querystring ,"title=") === false ) {
        $querystring .= "&title=".$title;
    }
    
	if( substr($page,0,1) == "P" ) {
		$page_id = substr($page,2,strlen($page)-2);
		//$url = "/activities/custom/?id=".$id."&serverid=".$server_id."&activityid=".$activity_id."&page=".$page_id."&title=".$title;
		$url = "/activities/custom/?page=".$page_id."&".$querystring;
        
		//this could be a form page in which case we need to submit the querystring so as to provide context for a update form.
		parse_str($_SERVER['QUERY_STRING'], $output);

		$options = "";
		foreach($output as $key => $value){
			if( $key != "id" && $key != "activityid" && $key != "title" && $key != "serverid" && $key != "page" ) {
				$url .= "&".$key."=".$value;
			}
		}
		
	} else if( substr($page,0,1) == "W" ) {
		$page_id = substr($page,2,strlen($page)-2);
		$url = "/activities/workview/?id=".$id."&workview=".$page_id."&serverid=".$server_id."&activityid=".$activity_id;
	} else if( $page == "social" ) {
		$url = "/activities/social/?id=".$id."&serverid=".$server_id."&activityid=".$activity_id."&title=".$title;
	} 
	return $url;
}

function outputFrame($page,$url,$order) {
    global $header, $footer;
    
    if( $header != "" )
        $url .= "&header=".$header;
    if( $footer != "" )
        $url .= "&header=".$footer;
    
	if( substr($page,0,1) != "P" && $page != "social" && $header == "") {
		echo '<section class="panel" style="margin-bottom: 0px;margin-left:5px;margin-right:5px;">';
	}

	echo "<iframe id='page".$order."' src='".$url."' style='width:100%;height:500px;border:0px;'></iframe>";

	if( substr($page,0,1) != "P" && $page != "social" && $header == "") {
		echo '</section>';
	}

}

if( $screen == null ) {
	echo '<section class="panel" style="margin-bottom: 0px;margin-left:5px;margin-right:5px;">';
	echo "<br/><br/><p>Unfortunately there is nothing to be displayed. One of two things has occurred:<ol><li>This activity has no screens within which you can collaborate, or</li><li>The administrator has not yet assigned you with any access to any screens.</li></ol></p>";
	echo '</section>';
} else {

	
	
	$title = $screen->title;
	$page_type = $screen->page_type;
	$page_0 = $screen->page_0;
	$page_1 = $screen->page_1;
	$page_2 = $screen->page_2;
	$page_1_size = $screen->page_1_size;
	$page_2_size = $screen->page_2_size;
	
	if( trim($page_type) == "single" || trim($page_type) == "hidden" ) {
	
		$url_0 = "/activities/workview/?id=".$id."&workview=".$page_0."&serverid=".$server_id."&activityid=".$activity_id;
		$url_0 = parsePageUrl($page_0,$url_0,0,$id,$activity_id,$server_id);
		
		outputFrame($page_0,$url_0,0);
		//echo "<iframe id='page0' src='".$url_0."' style='width:100%;height:500px;border:0px;'></iframe>";
	} else if( $screen->page_type == "two_vertical" ) {
		$page_1_size = $screen->page_1_size;
		$page_0_size = 100 - $page_1_size;
		
		$url_0 = "/activities/workview/?id=".$id."&workview=".$page_0."&serverid=".$server_id."&activityid=".$activity_id;
		$url_1 = "/activities/workview/?id=".$id."&workview=".$page_1."&serverid=".$server_id."&activityid=".$activity_id;
		
		$url_0 = parsePageUrl($page_0,$url_0,0,$id,$activity_id,$server_id);
		$url_1 = parsePageUrl($page_1,$url_1,1,$id,$activity_id,$server_id);
		
		echo "<table width='100%' cellpadding='0' cellspacing='0' style='background: #f1f2f7;'><tr><td width='".$page_0_size."%'>";
		//echo "<iframe id='page0' src='".$url_0."' style='width:100%;height:500px;border:0px;'></iframe>";
		outputFrame($page_0,$url_0,0);
		echo "</td><td width='".$page_1_size."%'>";
		outputFrame($page_1,$url_1,1);
		//echo "<iframe id='page1' src='".$url_1."' style='width:100%;height:500px;border:0px;'></iframe>";
		echo "</td></tr></table>";
		
	} else if( $screen->page_type == "two_horizontal" ) {
		$page_1_size = $screen->page_1_size;
    
        $page_0_size = 100 - $page_1_size;
        
        $url_0 = "/activities/workview/?id=".$id."&workview=".$page_0."&serverid=".$server_id."&activityid=".$activity_id;
        $url_1 = "/activities/workview/?id=".$id."&workview=".$page_1."&serverid=".$server_id."&activityid=".$activity_id;
        
        $url_0 = parsePageUrl($page_0,$url_0,0,$id,$activity_id,$server_id);
        $url_1 = parsePageUrl($page_1,$url_1,1,$id,$activity_id,$server_id);
        
        echo "<table width='100%' cellpadding='0' cellspacing='0' style='background: #f1f2f7;'><tr><td>";
        outputFrame($page_0,$url_0,0);
        
        echo "</td></tr><tr><td>";
        outputFrame($page_1,$url_1,1);
        
        echo "</td></tr></table>";
        
	} else if( $screen->page_type == "three_vertical" ) {
		$page_1_size = $screen->page_1_size;
		$page_2_size = $screen->page_2_size;
		$page_0_size = 100 - $page_1_size - $page_2_size;
		
		$url_0 = "/activities/workview/?id=".$id."&workview=".$page_0."&serverid=".$server_id."&activityid=".$activity_id;
		$url_1 = "/activities/workview/?id=".$id."&workview=".$page_1."&serverid=".$server_id."&activityid=".$activity_id;
		$url_2 = "/activities/workview/?id=".$id."&workview=".$page_2."&serverid=".$server_id."&activityid=".$activity_id;
		
		$url_0 = parsePageUrl($page_0,$url_0,0,$id,$activity_id,$server_id);
		$url_1 = parsePageUrl($page_1,$url_1,1,$id,$activity_id,$server_id);
		$url_2 = parsePageUrl($page_2,$url_2,2,$id,$activity_id,$server_id);
		
		
		echo "<table width='100%' cellpadding='0' cellspacing='0' style='background: #f1f2f7;'><tr><td width='".$page_0_size."%'>";
		outputFrame($page_0,$url_0,0);
		//echo "<iframe id='page0' src='".$url_0."' style='width:100%;height:500px;border:0px;'></iframe>";
		echo "</td><td width='".$page_1_size."%'>";
		outputFrame($page_1,$url_1,1);
		//echo "<iframe id='page1' src='".$url_1."' style='width:100%;height:500px;border:0px;'></iframe>";
		echo "</td><td width='".$page_2_size."%'>";
		outputFrame($page_2,$url_2,2);
		//echo "<iframe id='page2' src='".$url_2."' style='width:100%;height:500px;border:0px;'></iframe>";
		echo "</td></tr></table>";
		
	} else if( $screen->page_type == "three_horizontal" ) {
		$page_1_size = $screen->page_1_size;
		$page_2_size = $screen->page_2_size;
		$page_0_size = 100 - $page_1_size - $page_2_size;
		
		$url_0 = "/activities/workview/?id=".$id."&workview=".$page_0."&serverid=".$server_id."&activityid=".$activity_id;
		$url_1 = "/activities/workview/?id=".$id."&workview=".$page_1."&serverid=".$server_id."&activityid=".$activity_id;
		$url_2 = "/activities/workview/?id=".$id."&workview=".$page_2."&serverid=".$server_id."&activityid=".$activity_id;
		
		$url_0 = parsePageUrl($page_0,$url_0,0,$id,$activity_id,$server_id);
		$url_1 = parsePageUrl($page_1,$url_1,1,$id,$activity_id,$server_id);
		$url_2 = parsePageUrl($page_2,$url_2,2,$id,$activity_id,$server_id);
		
		echo "<table width='100%' cellpadding='0' cellspacing='0' style='background: #f1f2f7;'><tr><td>";
		outputFrame($page_0,$url_0,0);
		//echo "<iframe id='page0' src='".$url_0."' style='width:100%;height:500px;border:0px;'></iframe>";
		echo "</td></tr><tr><td>";
		outputFrame($page_1,$url_1,1);
		//echo "<iframe id='page1' src='".$url_1."' style='width:100%;height:500px;border:0px;'></iframe>";
		echo "</td></tr><tr><td>";
		outputFrame($page_2,$url_2,2);
		//echo "<iframe id='page2' src='".$url_2."' style='width:100%;height:500px;border:0px;'></iframe>";
		echo "</td></tr></table>";
	}
	

}

echo "<script>";
echo 'var page_0 = "'.$page_0.'";';
echo 'var page_1 = "'.$page_1.'";';
echo 'var page_2 = "'.$page_2.'";';
echo "</script>";
?>
						</div>
					</section>
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
