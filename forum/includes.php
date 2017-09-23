<?php

include_once("../lib/lib.php");
//session_start();

require_once 'php/config.php';

if(defined('DEVELOPMENT') AND DEVELOPMENT == false){
	error_reporting(E_ALL ^ E_NOTICE);
}

require_once 'php/db.php';

$db = new Db;

require_once 'php/string.php';
require_once 'php/layout.php';
require_once 'php/class.phpmailer.php';
require_once 'php/cookie.php';
require_once 'php/users_api.php';
require_once 'php/functions.php';
require_once 'php/Upload.php';

LoadSettings();

date_default_timezone_set(TIME_ZONE);

global $lang;
$lang = null;
if(defined('MULTI_LANG') AND MULTI_LANG == true){
	if(isset($_POST['lang_submit'])){
		if(isset($_POST['lang']) AND $_POST['lang'] != ''){
			$lang = strtolower($_POST['lang']);
			Cookie::Set(TABLES_PREFIX.'sforum_lang', $lang);
		}
	}else{
		if(Cookie::Exists(TABLES_PREFIX.'sforum_lang')){
			$lang = strtolower(Cookie::Get(TABLES_PREFIX.'sforum_lang'));
		}else{
			if(defined('PRIMARY_LANG') AND PRIMARY_LANG != ''){
				$lang = strtolower(PRIMARY_LANG);
			}
		}
	}
}


$strings = new StringResource('str/');

if(isset($_POST))
	$_POST = CleanXSS($_POST);
if(isset($_GET))
	$_GET = CleanXSS($_GET);
