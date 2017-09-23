<?php

require 'db_install.php';

$db = new Db;

function LoadSettings(){
	if(!($site_name = get_option('site_name'))){
		set_option('site_name', 'Just A Forum');
	}
	
	if(!($base_path = get_option('base_path'))){
		if(preg_match("/(.*)\/upgrade\.php/",$_SERVER['SCRIPT_FILENAME'],$matches)){
			$server_path=$matches[1];
			set_option('base_path', $matches[1]);
		}
	}
	
	if(!($forum_url = get_option('forum_url'))){
		$pageURL = 'http';
		if(isset($_SERVER["HTTPS"]) AND $_SERVER["HTTPS"] == "on"){
			$pageURL .= "s";
		}
		$pageURL .= "://";
		if($_SERVER["SERVER_PORT"] != "80"){
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		}else{
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}
		if(preg_match("/(.*)\/upgrade\.php/",$pageURL,$matches)){
			set_option('forum_url', $matches[1]);
			set_option('site_url', $matches[1]);
		}
	}
}

function get_option($key){
	global $db;
	$value = $db->get_var("SELECT option_value FROM " . TABLES_PREFIX . "options WHERE option_key = '" . $key."'");
	if($value){
		return $value;
	}else{
		return "";
	}
}

function set_option($key, $value = null){
	global $db;
	
	if($value == null){
		$db->query("DELETE FROM " . TABLES_PREFIX . "options WHERE option_key = '" . $key ."'");
		return true;
	}
	
	$values = array('option_value'=>$value);
	if($db->get_row("SELECT * FROM " . TABLES_PREFIX . "options WHERE option_key = '" . $key ."' ORDER BY id DESC LIMIT 0,1")){
		$db->update(TABLES_PREFIX . "options", $values, array('option_key'=>$key), array("%s"));
	}else{
		$values['option_key'] = $key;
		$db->insert(TABLES_PREFIX . "options", $values, array("%s"));
	}
	return true;
}

LoadSettings();

echo 'Done. Signin as an admin and then click on the settings menu under the admin tab in the sidebar. There options you need to set.';
