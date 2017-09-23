<?php
include_once("lib/lib.php");

$msg = querystring("msg");
$api = form("api");

$u = intval(querystring("u"));
$k = querystring("k");

if( $u == "" ) {
	$u = intval(form("u"));
	$k = form("k");
}


$action = form("ac");
if( $action == "" ) {
	$action = querystring("ac");
}


$db = new db_helper();
$db->CommandText("SELECT email,client_id FROM users WHERE id = '%s' AND password_reset_hash = '%s'");
$db->Parameters($u);
$db->Parameters($k);
$db->Execute();
$email = "";
$client_id = "";
if ($db->Rows_Count() > 0) {
	$r = $db->Rows();
	$email = $r["email"];
	$client_id = $r["client_id"];
}

if( $email == "" ) {
	header("Location: /?msg=Invalid%20User%20Details." );
    die();
}


switch (strtolower($action)) {
    case "chngpsswrd" :
        $uid = $u;
        $key = $k;

        $passwordnew = form("p1");
        $passwordconfirm = form("p2");

        if (validator::isEmpty($passwordnew) || validator::isEmpty($passwordconfirm)) $msg = "Password Change failed - Please provide the required fields";
        else if ($passwordnew != $passwordconfirm) $msg = "Please confirm your password";
        else {
            $u_obj = new user_helper($uid);
            
                if ($u_obj->PasswordChange($passwordconfirm)) {
					
					//propagate the password change across analytics servers
					$json = '{"tasks": [{"task": "server.security.refresh"}]}';
					$sql = "SELECT server_ip,server_port FROM servers  WHERE client_id='". $client_id ."' AND server_is_deleted=0;";
					$db = new db_helper();
					$db->CommandText($sql);
					$db->Execute();
					if ($db->Rows_Count() > 0) {
						while( $r = $db->Rows() ) {
							$server_address = $r['server_ip'] . ":" . $r['server_port'];
							api($server_address, "server.service", $json, "");
						}
					}
					
					redirectToPage ("/password/?ac=chngpsswrd-k&u=".$u."&k=".$k);
                } else {
					$msg = "Password Change failed";
				}
            
        }
        break;
    case "chngpsswrd-k" :
        $msg = "Your Password has been updated.";
        break;
}
$log = querystring("logout");
if ($log == "true") {
    unset($_SESSION['uid']);
    unset($_SESSION['user_cookie']);
    session_destroy();
    
    $msg = "You've been logged out from system";
}

if( querystring("user") != "" && querystring("code") != "" ) {
	$db = new db_helper();
	$db->CommandText("SELECT email FROM users WHERE id = '%s' AND activation_code = '%s' LIMIT 1");
	$db->Parameters(querystring("user"));
	$db->Parameters(querystring("code"));
	$db->Execute();
	if ($db->Rows_Count() > 0) {
		$db->CommandText("UPDATE users SET activated=1 WHERE id = '%s';");
		$db->Parameters(querystring("user"));
		$db->Execute();
		
		$msg = "Your account has been activated.";
	} else {
		$msg = "We are unable to activate your account please check the link used is valid.";
	}
}



?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="ThemeBucket">
    <link rel="shortcut icon" href="/images/favicon.png">

    <title>MODLR » Account Recovery</title>

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
    <script src="/js/ie8-responsive-file-warning.js"></script><![endif]-->

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
		echo '<form class="form-signin" method="post" action="/password/" style="margin-top:0px;" >';
	} else {
		echo '<body class="login-body">';
		echo '<div class="container">';
		echo '<form class="form-signin" method="post" action="/password/" >';
	}
	
?>
      
      	<input type='hidden' id='ac' name='ac' value='chngpsswrd'/>
      	<input type='hidden' id='u' name='u' value='<? echo $u;?>'/>
      	<input type='hidden' id='k' name='k' value='<? echo $k;?>'/>
        <h2 class="form-signin-heading" style="padding-top: 10px;padding-bottom: 10px;"><img src='/images/logo_small1.png'/></h2>
        <div class="login-wrap">
            <div class="user-login-info">
<?
if( $msg != "" ) {
	echo "<div><h4 class='alert-heading'>Please Note:</h4>".$msg."</div><br/>";
}
?>
<?
if( strtolower($action) != "chngpsswrd-k" ) {
?>
				<div><h4 class='alert-heading'>Account Recovery:</h4>Please enter a new password below.</div><br/>
                <input type="text" class="form-control" placeholder="Email" id='u' name='u' value='<? echo $email;?>'  disabled>
                <input type="password" class="form-control" id='p1' name='p1' placeholder="Password" autofocus>
                <input type="password" class="form-control" id='p2' name='p2' placeholder="Password (Again)">
<?
}
?>
            </div>
<?
if( strtolower($action) != "chngpsswrd-k" ) {
?>
            <button class="btn btn-lg btn-login btn-block" type="submit">Reset Password</button>
<?
}
?>
            <button class="btn btn-lg btn-login btn-block" type="button" onclick='window.location="http://go.modlr.co/"'>Back to Login</button>

        </div>
		</form>
		

    </div>



    <!-- Placed js at the end of the document so the pages load faster -->

    <!--Core js-->
    <script src="/js/jquery.js"></script>
    <script src="/bs3/js/bootstrap.min.js"></script>

  </body>
</html>