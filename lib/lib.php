<?php
ini_set('session.cookie_httponly',1);
ini_set('session.use_only_cookies',1);

session_start();

if( strpos(strtolower($_SERVER['HTTP_HOST']),"modlr.co") > -1 ) {
	if( isset($_SERVER['HTTPS']) ) {
		if($_SERVER['HTTPS']!="on" ){
			$redirect= "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
			header("Location:".$redirect,true,301);
			exit();
		}
	} else {
		$redirect= "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		header("Location:".$redirect,true,301);
		exit();
	}
}

//Detect special conditions devices
if( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
	$iPod    = stripos($_SERVER['HTTP_USER_AGENT'],"iPod");
	$iPhone  = stripos($_SERVER['HTTP_USER_AGENT'],"iPhone");
	$iPad    = stripos($_SERVER['HTTP_USER_AGENT'],"iPad");
	$Android = stripos($_SERVER['HTTP_USER_AGENT'],"Android");
	$webOS   = stripos($_SERVER['HTTP_USER_AGENT'],"webOS");
}

include_once("config.php");
include_once("dbfunctions.php");
include_once("miscfunctions.php");
include_once("includes/exception.php");
include_once("includes/audit.php");
include_once("includes/email.php");
include_once("includes/sms.php");
include_once("includes/notice.php");
include_once("includes/validation.php");

include_once("class/user.php");
include_once("class/db.php");

DBopen();
$smsg = "";

if (!isset($_SESSION['uid'])) {
  $uid = "NULL";
} else {
  $uid = $_SESSION['uid'];
}

$page = $_SERVER["REQUEST_URI"];

//are we in a non restricted area.
if( substr($page,0,2) == "/?" || $page == "/" || substr($page,0,6) == "/auth/" || substr($page,0,9) == "/password" ||  $page == "/register/" ||  $page == "/register"  ||  $page == "/built/" || substr($page,0,8) == "/built/?" || substr($page,0,9) == "/json/ask"   ) {
	//urestricted area
} else {
	//has the user logged in.
	if( !is_numeric($uid)  ) {
		//the user id is not valid
        $_SESSION['redirect'] = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		header( 'Location: /?msg=Session Expired' ) ;
		exit;
	} else {
		//restrict user to their areas.
		if( session("role") == "MODELLER" ) {
			//can go anywhere
		} else {
			//collaborators can only stay in activities.
			if( substr($page,0,12) != "/activities/" && substr($page,0,5) != "/api/" ) {
				header( 'Location: /activities/home/' ) ;
				exit;
			}
		}
	}
	
}

?>