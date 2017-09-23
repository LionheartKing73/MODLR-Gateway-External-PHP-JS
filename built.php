<?php
include_once("lib/lib.php");

$msg = querystring("msg");
$api = form("api");

$action = form("ac");
if( $action == "" ) {
	$action = querystring("ac");
}

switch (strtolower($action)) {
    case "lgn" :
        $username = form("u");
        $password = form("p");
        if (validator::isEmpty($username) || validator::isEmpty($password)) $msg = "Login Failed - Username / Password Invalid.";
        else {
            $u_obj = new user_helper();
            $uid = $u_obj->Login($username, $password, getRealIpAddr());
            
            
            if ($uid > 0) {
				
				if( $api != "" ) {
					header("Content-Type","application/json");
					
					$serverStr = "";
					
					$sql = "SELECT server_id,server_codename,server_ip,server_port,clients.client_name FROM modlr.servers LEFT JOIN clients ON clients.client_id = servers.client_id WHERE servers.client_id  IN (SELECT client_id FROM users_clients WHERE user_id='%s')  AND server_is_deleted=0;";
					$db = new db_helper();
					$db->CommandText($sql);
					$db->Parameters(session('uid'));
					$db->Execute();
					if ($db->Rows_Count() != 0) {
						while( $r = $db->Rows() ) {

							
							$server_id = $r['server_id'];
							$server_ip = $r['server_ip'];
							$server_codename = $r['server_codename'];
							$server_port = $r['server_port'];
							$client_name= $r['client_name'];
							
							$serverStr .= '{"url":"'.$server_ip.':'.$server_port.'","id":"'.$server_id.'","name":"'.$server_codename.'","account":"'.$client_name.'"},';
							
						}
						$serverStr = substr($serverStr,0,strlen($serverStr)-1);
					}
					
					echo '{"result":1,"servers" : ['.$serverStr.']}';
					die();
				} else {
					if( session("role") == "MODELLER" ) {
						header("Location: /navigation/" );
						die();
					} else {
						header("Location: /activities/home/" );
						die();
					}	
				}
            } else {
				if( $api != "" ) {
					header("Content-Type","application/json");
					echo '{"result":0,"error" :"'.$msg.'"}';
					die();
				}
			}
        }
        break;
    case "rcvr" :
        $email = form("email_reset");
        if (validator::isEmpty($email)) $msg = "Email is required";
        else {
            $u_obj = new user_helper();
            $u_obj->GetByEmail($email);
            if ($u_obj->Id() > 0) {
                //if ($u_obj->SetRecoverStamp() == true) {
                    sendPasswordResetMail($email, $u_obj->Id());
                    header("Location: /?ac=rcvr-k" );
            		die();
                //}
                //else $msg = "We have just sent a password recovery email to you. Please check your junk mail folder if this is not in your inbox in the next 5min. Password resets are limited to one per hour per account.";
            }
            else $msg = "Profile not found";
        }
        break;
    case "rcvr-k" :
        $msg = "Password reset details sent to your email.";
        break;
    case "chngpsswrd" :
        $uid = intval(querystring("u"));
        $key = querystring("k");

        $passwordnew = form("p-new");
        $passwordconfirm = form("p-confirm");

        if (validator::isEmpty($passwordnew) || validator::isEmpty($passwordconfirm)) $msg = "Password Change failed - Please provide the required fields";
        else if ($passwordnew != $passwordconfirm) $msg = "Please confirm your password";
        else {
            $u_obj = new user_helper($uid);
            if (md5($u_obj->Email()) != $key) $msg = "Key mismatch - Identity not verified";
            else {
                if ($u_obj->PasswordChange($passwordconfirm)) redirectToPage ("?ac=chngpsswrd-k");
                else $msg = "Password Change failed";
            }
        }
        break;
    case "chngpsswrd-k" :
        $msg = "Password changed";
        break;
}
$log = querystring("logout");
if ($log == "true") {
    unset($_SESSION['uid']);
    unset($_SESSION['user_cookie']);
    session_destroy();
    
    $msg = "You've been logged out from system";
}


?><!DOCTYPE html>
<html lang="en">
<head> 
    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="ThemeBucket">
	<link rel="shortcut icon" href="/images/favicon.ico">

    <title>MODLR » Login</title>

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
	
	<style>
	/*built colors*/
	.form-signin .btn-login { 
		background-color: #7C6C5C !important;
		border-color: #7C6C5C !important;
	}
	.form-signin h2.form-signin-heading {
		border-bottom: 10px solid #7C6C5C;
	}
	.form-signin a, .form-signin a:hover {
		color: #7C6C5C;
	}
	</style>

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
		echo '<form class="form-signin" method="post" action="/built/" style="margin-top:0px;" >';
	} else {
		echo '<body class="login-body">';
		echo '<div class="container">';
		echo '<form class="form-signin" method="post" action="/built/" >';
	}
	
?>
      
      	<input type='hidden' id='ac' name='ac' value='lgn'/>
        <h2 class="form-signin-heading" style="padding-top: 10px;padding-bottom: 10px;"><img width='200' src='/images/BuiltWellLarge.png'/></h2>
        <div class="login-wrap">
            <div class="user-login-info">
<?
if( querystring("registered") != "" ) {
	echo "<div><h4 class='alert-heading'>Important Information:</h4>Your account has been registered. You will recieve an activation email shortly. Once your account has been activated you can access your environment.</div><br/>";
}
if( $msg != "" ) {
	echo "<div><h4 class='alert-heading'>Please Note:</h4>".$msg."</div><br/>";
}
?>
                <input type="text" class="form-control" placeholder="Email" id='u' name='u' autofocus>
                <input type="password" class="form-control" id='p' name='p' placeholder="Password">
            </div>
            <label class="checkbox">
                <!--<input type="checkbox" value="remember-me"> Remember me-->
                <span class="pull-right">
                    <a data-toggle="modal" href="#myModal"> Forgot Password?</a>
                </span>
            </label>
            <button class="btn btn-lg btn-login btn-block" type="submit">Sign in</button>
            <button class="btn btn-lg btn-login btn-block" type="button" onclick='window.location="http://www.built.com.au/"'>Built.com.au</button>
			<br/>
			<span class="pull-right">
                <a hred='http://www.modlr.co'> Powered by &nbsp;<img src="/images/logo_small1.png" width="100"></a>
            </span><br/>
        </div>
		</form>
		<form class="form-signin" method="post" action="/built/" >
			<input type='hidden' id='ac' name='ac' value='rcvr'/>
          <!-- Modal -->
          <div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="myModal" class="modal fade">
              <div class="modal-dialog">
                  <div class="modal-content">
                      <div class="modal-header">
                          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                          <h4 class="modal-title">Forgot Password ?</h4>
                      </div>
                      <div class="modal-body">
                          <p>Enter your e-mail address below to reset your password.</p>
                          <input type="text" name="email_reset" id="email_reset" placeholder="Email" autocomplete="off" class="form-control placeholder-no-fix">

                      </div>
                      <div class="modal-footer">
                          <button data-dismiss="modal" class="btn btn-default" type="button">Cancel</button>
                          <button class="btn btn-success" type="submit">Submit</button>
                      </div>
                  </div>
              </div>
          </div>
          <!-- modal -->

		</form>

    </div>



    <!-- Placed js at the end of the document so the pages load faster -->

    <!--Core js-->
    <script src="/js/jquery.js"></script>
    <script src="/bs3/js/bootstrap.min.js"></script>

  </body>
</html>