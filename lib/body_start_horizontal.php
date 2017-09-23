<?

?>
</head>
<body class="full-width">

  <section id="container" class="hr-menu">
      <!--header start-->
      
      
      <header class="header fixed-top">
          <div class="navbar-header">
              <button type="button" class="navbar-toggle hr-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                  <span class="fa fa-bars"></span>
              </button>

              <!--logo start-->
              <!--logo start-->
              <div class="brand ">
                  <a href="/home/" class="logo">
                      <img src="/images/logo.png" alt="">
                  </a>
              </div>
              <!--logo end-->
              <!--logo end-->
              <div class="horizontal-menu navbar-collapse collapse ">
                  <ul class="nav navbar-nav">
<?
if( count($activities) > 0 ) {
?>
                      <li class="dropdown">
                          <a data-toggle="dropdown" data-hover="dropdown" class="dropdown-toggle" href="#">Activities <b class=" fa fa-angle-down"></b></a>
                          <ul class="dropdown-menu">
                              <?	
                              for( $i=0; $i<count($activities); $i++ ) {
                              		$activity = $activities[$i];
                              		echo "<li><a href='/activities/view/?id=".$activity->modelid."&activityid=".$activity->id."&serverid=".$activity->server_id."'>".$activity->name."</a></li>";
                              }

							  ?>
                          </ul>
                      </li>
<?
}


?>
                  </ul>

              </div>
              <div class="top-nav hr-top-nav">
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
<?
if( session("role") == "MODELLER" ) {
	echo '<li><a href="/home/"><i class=" fa fa-wrench"></i>Modeller View</a></li>';
}
?>
							<li><a href="/?logout=true"><i class="fa fa-key"></i> Log Out</a></li>
						</ul>
                      </li>
                      <!-- user login dropdown end -->
                  </ul>
              </div>

          </div>

      </header>
      <!--header end-->
      <!--sidebar start-->

      <!--sidebar end-->
      <!--main content start-->
      <section id="main-content">
          <section class="wrapper">
              <!-- page start-->