<?php
include_once("lib/lib.php");

if( session("role") != "MODELLER" ) {
	header("Location: /activities/home/" );
	die();
}
?><!DOCTYPE html>
<html lang="en">
<head> 
    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="ThemeBucket">
	<link rel="shortcut icon" href="/images/favicon.ico">

    <title>MODLR » Navigation</title>

    <!--Core CSS -->
    <link href="/bs3/css/bootstrap.min.css" rel="stylesheet">
    <link href="/css/bootstrap-reset.css" rel="stylesheet">
    <link href="/font-awesome/css/font-awesome.css" rel="stylesheet" />

    <!-- Custom styles for this template -->
    <link href="/css/style.css" rel="stylesheet">
    <link href="/css/style-responsive.css" rel="stylesheet" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />

	<link rel="apple-touch-icon" href="/img/apple-touch-icon-precomposed.png"/>
	<link rel="apple-touch-icon" sizes="72x72" href="/img/apple-touch-icon-72x72-precomposed.png" />
	<link rel="apple-touch-icon" sizes="114x114" href="/img/apple-touch-icon-114x114-precomposed.png" />
	<link rel="apple-touch-icon" sizes="144x144" href="/img/apple-touch-icon-144x144-precomposed.png" />
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="black">
	

    <!-- Just for debugging purposes. Don't actually copy this line! -->
    <!--[if lt IE 9]>
    <script src="js/ie8-responsive-file-warning.js"></script><![endif]-->

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
</head>


    
<?

	if( $iPhone ) {
		echo '<body class="login-body" style="margin-top:3px !important;">';
		echo '<div class="container">';
		echo '<form class="form-signin" method="post" action="/" style="margin-top:0px;" >';
	} else {
		echo '<body class="login-body">';
		echo '<div class="container">';
		echo '<form class="form-signin" method="post" action="/" style="max-width: 630px;">';
	}
	
?>
      
      	<input type="hidden" id="ac" name="ac" value="lgn">
        <h2 class="form-signin-heading" style="padding-top: 10px;padding-bottom: 10px;"><img src="/images/logo_small1.png"></h2>
        <div class="login-wrap">
			<p style="text-align: center;">Would you like to build a model or use a model? </p>  
			 
			<b>Build a Model or Administer a Model:</b>
			<p>Modeller view provides access to underlying datasets, database connections, workview development and activity management.</p>
			<button class="btn btn-lg btn-login btn-block" type="button" style="text-transform: none;" onclick="window.location='/home/';">Modeller View </button>

			<br><b>Use a Model:</b>
			<p>Collaborator view displays the end-user front-end for each model.</p>
			<a href='/activities/home/'><button class="btn btn-lg btn-login btn-block" type="button" style="text-transform: none;" onclick="window.location='/activities/home/';">Collaborator View</button></a>
		  
        </div>
		</form>

    </div>


    <!-- Placed js at the end of the document so the pages load faster -->

    <!--Core js-->
    <script src="/js/jquery.js"></script>
    <script src="/bs3/js/bootstrap.min.js"></script>

  </body>
</html>