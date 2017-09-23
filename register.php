<?php
include_once("lib/lib.php");
include_once("lib/server_functions.php");

$msg  = "";
$u_obj = new user_helper();

$email = form("email");
$name = form("name");
$key = form("key");
$company = form("company");

if( $email != "" && $name != "" ) {

	$subscribed = form("subscribed");
	$agreed = form("agreed");
	
	
	if( $agreed == "" ) {
		$agreed = false;
		$msg .= "You must agree to the terms, conditions and privacy policy.<br/>";
	} else {
		$agreed = true;
		
		//if( $key == "AUTOM8" ) {
		
		
			if( $email == "" ) {
				$msg .= "Please enter a valid email address.<br/>";
			}
			if( $name == "" ) {
				$msg .= "Please enter a your name.<br/>";
			}
			if( $msg == "" ) {
				$msg = $u_obj->Register( $email, $name, $subscribed, $company);
			}
			if( $msg == "" ) {
				
				
				
				header("Location: /?registered=true");
			} else {

			}
		/*
		} else {
			$msg .= "Please note that Modlr is presently in Closed Beta mode. Only people with the Beta Key may create new accounts.<br/>";
		}*/
	}
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="ThemeBucket">
    <link rel="shortcut icon" href="images/favicon.png">

    <title>MODLR » Register</title>

    <!--Core CSS -->
    <link href="/bs3/css/bootstrap.min.css" rel="stylesheet">
    <link href="/css/bootstrap-reset.css" rel="stylesheet">
    <link href="/font-awesome/css/font-awesome.css" rel="stylesheet" />

    <!-- Custom styles for this template -->
    <link href="/css/style.css" rel="stylesheet">
    <link href="/css/style-responsive.css" rel="stylesheet" />

    <!-- Just for debugging purposes. Don't actually copy this line! -->
    <!--[if lt IE 9]>
    <script src="js/ie8-responsive-file-warning.js"></script><![endif]-->

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
</head>
  <body class="login-body">

    <div class="container">

      <form class="form-signin" action="/register/" method="post">
         <h2 class="form-signin-heading" style="padding-top: 10px;padding-bottom: 10px;color:#333;"><img src='/images/logo_small1.png'/><br/><br/>Register Account</h2>
        <div class="login-wrap">
<?
if( $msg != "" ) {
	echo "<div><h4 class='alert-heading' style='color: #BB3333;'>Please Note:</h4>".$msg."</div><br/>";
}
?>
        
		<div><!--<h4 class='info-heading' style='color: #3333BB;'>Please Note:</h4> MODLR is presently in closed beta. We are only accepting pre-approved registrants with whom we have provided a personal Beta Key.</div><br/>-->
		
            <p>Enter your details below</p>
            <input type="text" class="form-control" id='company' name='company' placeholder="Company" value="<? echo $company;?>" autofocus>
            <input type="text" class="form-control" id='name' name='name' placeholder="Full Name" value="<? echo $name;?>" autofocus>
            <input type="text" class="form-control" id='email' name='email' placeholder="Email" value="<? echo $email;?>" autofocus>
            <!-- <input type="text" class="form-control" id='key' name='key' placeholder="Beta Key" value="<? echo $key;?>" autofocus> -->
            
			<div id='account_selection'>
            <b>Account Plan:</b> Free <br/>(1 Modeller, 0 Contributors, 1GB Cloudlet)
			<br/><br/>
			<b>Server Location:</b>
			<select class="form-control m-bot15"  id='location' name='location' style='font-size:11px;'>
<?
$regions = digital_ocean_return_all_regions();
for($i=0;$i<count($regions->regions);$i++) {
	$region = $regions->regions[$i];
	
	$regionStr = $region->name;
	if( strpos($regionStr,"1") !== false ) {
		$regionStr = substr($regionStr,0, strlen($regionStr)-2);
		echo "<option value='".$region->slug."'>".$regionStr."</option>";
	}
}

?>
			</select>
			</div>
            <label class="checkbox">
                <input type="checkbox" name="account" value="selfselect" onclick="btnSelfSelect(this);"> Select a server size and location later
            </label>
            
            <p> Enter your account details below</p>
            <label class="checkbox">
                <input type="checkbox" name="subscribed" value="newsletter"> Keep me informed about news and updates
            </label>
            <label class="checkbox">
                <input type="checkbox" name="agreed" value="agree this condition"> I agree to the Terms of Service and Privacy Policy
            </label>
            <button class="btn btn-lg btn-login btn-block" type="submit">Submit</button>

            <div class="registration">
                Already Registered.
                <a class="" href="/">
                    Login
                </a>
            </div>

        </div>

      </form>

    </div>


    <!-- Placed js at the end of the document so the pages load faster -->
	
    <!--Core js-->
    <script src="/js/jquery.js"></script>
    <script src="/bs3/js/bootstrap.min.js"></script>
	
	<script type="text/javascript">
		function btnSelfSelect(checkbox) {
			
			if( checkbox.checked ) {
				$('#account_selection').css('display','none');
			} else {
				$('#account_selection').css('display','block');
			}
		
		}
	</script>
	
	
  </body>
</html>