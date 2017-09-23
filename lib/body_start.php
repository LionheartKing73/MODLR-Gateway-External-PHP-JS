</head>

<body>

<?
$json = "{\"tasks\": [";
$json .= "{\"task\": \"home.directory\"}, {\"task\": \"server.jobs\"}";
$json .= "]}";

$results = api_short(SERVICE_SERVER, $json);
$result = null;

$server_is_down = false;
if( property_exists( $results, 'results' ) ) {
	$result = $results->results;
	if( count($result) > 0 ) {
		$resultTask = $results->results[0];
		if( property_exists( $resultTask, 'error' ) ) {
			$server_is_down = true;
		}
	} else {
		$server_is_down = true;
	}
}

?>

<section id="container" >
<!--header start-->
<header class="header fixed-top clearfix">
<!--logo start-->
<div class="brand">

    <a href="/home/" class="logo">
        <img src="/images/logo.png" alt="">
    </a>
    <div class="sidebar-toggle-box">
        <div class="fa fa-bars"></div>
    </div>
</div>
<!--logo end-->



<div class="nav notify-row" id="top_menu" style="width:auto;">
    <!--  notification start -->
    <ul class="nav top-menu">
        
        <!-- notification dropdown start-->
        <?
        if( !$server_is_down ) {
         	$jobs = $results->results[1]->jobs;
        ?>
		<!--
        <li id="header_notification_bar" class="dropdown">
            <a data-toggle="dropdown" class="dropdown-toggle" href="#">

                <i class="fa fa-bell-o"></i>
                <?
                if( count($jobs) > 0 ) {
                ?>
                <span class="badge bg-error"><? echo count($jobs);?></span>
                <?
                }
                ?>
            </a>
            <ul class="dropdown-menu extended notification">
                <li>
                    <p>Notifications</p>
                </li>
                <?
                $count = 0;
                for($i=count($jobs)-1;$i>=0;$i--) {
                	$job = $jobs[$i];
                	
                	$statusStr = $job->tags->status;
                	if( strlen($statusStr) > 25 ) {
						$pos = strpos($statusStr," ",25);
						if( $pos !== false ) {
							$statusStr = substr($statusStr,0,$pos);
						}
					}
                	
                	
                	$status = ucwords(strtolower($statusStr)) . ' ('.$job->tags->rows . ' rows)';
                	
                	$color = "info";
                	if( $job->tags->status == "process complete" ) {
                		$color = "success";
                	}
                	if (strpos($statusStr,'aborted:') !== false) {
                		$color = "danger";
                		$status = "The process aborted. <br/><a href='/logs/'>View Errors</a>";
                	}
                	
                	echo '<li>
						<div class="alert alert-'.$color.' clearfix">
							<span class="alert-icon"><i class="fa fa-bolt"></i></span>
							<div class="noti-info">
								<a href="/logs/"> '.$job->name.'<br/>'.$status.'</a>
							</div>
						</div>
					</li>';
                	
                	$count++;
                	if( $count > 4 ) {
                		break;
                	}
                }
                ?>
            </ul>
        </li>
		-->
        <?
        }
        ?>
        
        <li id="header_notification_bar" class="dropdown">
            <a data-toggle="dropdown" class="dropdown-toggle" href="#" title="View Processes and Logs" onclick='window.location = "/logs/";'>
                <i class="fa fa-gear"></i>
            </a>
        </li>
        <li id="header_notification_bar" class="dropdown">
            <a data-toggle="dropdown" class="dropdown-toggle" href="#" title="Refresh" onclick='window.location.reload(true);'>
                <i class="fa fa-refresh"></i>
            </a>
        </li>
		<!--
        <li id="header_notification_bar" class="dropdown">
            <a data-toggle="dropdown" class="dropdown-toggle" href="#" onclick='fullscreenToggle();'>
                <i class="fa fa-expand"></i>
            </a>
        </li>
		-->
        
        <!-- notification dropdown end -->
    </ul>
    <!--  notification end -->
</div>

<div class="top-nav clearfix">
    <!--search & user info start-->
    <ul class="nav pull-right top-menu">
    	
    	<!--
        <li>
            <input type="text" class="form-control search" placeholder=" Search">
        </li>
       -->
        
        <!-- user login dropdown start-->
        <li class="dropdown">
            <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                <img alt="" src="/images/logo_square-300x300.png" height='29' width='29'>
                <span class="username"><? echo session("name");?></span>
                <b class="caret"></b>
            </a>
            <ul class="dropdown-menu extended logout">
                <li><a href="/activities/home/"><i class=" fa fa-coffee"></i>Collaborator View</a></li>
<?
if( session("client_id") == 1 ) {
	echo '<li><a href="#" onclick="showAsk();"><i class=" fa fa-bullhorn"></i>Ask MODLR</a></li>';
}
?>
                <li><a href="/manage/"><i class="fa fa-sitemap"></i>Manage Account</a></li>
                <li><a href="/sandbox/"><i class="fa fa-puzzle-piece"></i>Sandbox (API)</a></li>
				
                <li><a href="/?logout=true"><i class="fa fa-key"></i> Log Out</a></li>
            </ul>
        </li>
        <!-- user login dropdown end -->
		<!--
        <li>
            <div class="toggle-right-box">
                <div class="fa fa-bars"></div>
            </div>
        </li>
		-->
    </ul>
    <!--search & user info end-->
</div>
</header>
<!--header end-->
<aside>
<?

$main_body_class = "merge-left";
$main_sidebar_class = "hide-left-bar";
/*
$main_body_class = "";
$main_sidebar_class = "";
*/

$mainClass = "hide-left-bar";
if( $iPhone || $iPod ) {
	$mainClass = "";
}
//temp disabled
?>

    <div id="sidebar" class="nav-collapse <? echo $main_sidebar_class;?>">
        <!-- sidebar menu start-->            <div class="leftside-navigation">
            <ul class="sidebar-menu" id="nav-accordion">
			<li>
                <a href="/home/">
                    <i class="fa fa-arrow-circle-right"></i>
                    <span>Home</span>
                </a>
            </li>
<?
if( !$server_is_down ) {
	if( session("role") == "MODELLER" ) {
?>

            <li class="sub-menu">
                <a href="javascript:;">
                    <i class="fa fa-rocket"></i>
                    <span>Start Modelling</span>
                </a>
                <ul class="sub">
                	<li><a href="/model/" style="color: #fff;">Create a New Model</a></li>
<?
	
		$contents = $results->results[0]->models;
		for($i=0;$i<count($contents);$i++) {
			$model = $contents[$i];
			echo "<li><a href='/model/?id=".$model->id."'>".$model->name."</a></li>";
		}
?>
                </ul>
            </li>
<?
	}	//modellers only.
?>
            <li class="sub-menu">
                <a href="javascript:;">
                    <i class="fa fa-check-circle"></i>
                    <span>Start Planning</span>
                </a>
                <ul class="sub">
                	<li><a href="/activity/" style="color: #fff;">Create a New Activity</a></li>
                
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
			
<?
	if( session("role") == "MODELLER" ) {
?>
            <li class="sub-menu">
                <a href="javascript:;">
                    <i class="fa fa-link"></i> 
                    <span>Connectors</span>
                </a>
                <ul class="sub">
                	<li><a href="/connectors/xero/public.php" style="color: #fff;">Xero and Xero Payroll</a></li>
                </ul>
            </li>
<? 
	}
}
if( session("role") == "MODELLER" ) {
	if( !$server_is_down ) {
?>
			<li>
                <a href="/datastore/">
                    <i class="fa fa-th"></i>
                    <span>Manage Data </span>
                </a>
            </li>
<?
?>
            <li>
                <a href="/datasource/">
                    <i class="fa fa-link"></i>
                    <span>Manage Datasources </span>
                </a>
            </li>
<?
	}
}
if( session("role") == "MODELLER" ) {
?>
            <li>
                <a href="/docs/">
                    <i class="fa fa-book"></i>
                    <span>Documentation </span>
                </a>
            </li>
            <li>
                <a href="/forum/index/">
                    <i class="fa fa-bullhorn"></i>
                    <span>Community Forum </span>
                </a>
            </li>
<?
}
?>
            
        </ul></div>        
<!-- sidebar menu end-->
    </div>
</aside>
<!--sidebar end-->
    <!--main content start-->
    <section id="main-content" class="<? echo $main_body_class;?>">
        <section class="wrapper">
        <!-- page start-->