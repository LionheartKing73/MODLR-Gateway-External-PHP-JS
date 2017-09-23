<?php

function DBopen() {
	global $config_server,$config_database,$config_username,$config_password;
	global $link;
	
	$link = mysql_connect($config_server, $config_username, $config_password );
	if (!$link) {
		die('Could not connect: ' . mysql_error());
	}
	@mysql_select_db($config_database) or die( "Unable to select database");
}

function DBclose() {
	global $link;
	//mysql_close($link);
}

function appendCriteria($orig, $crit, $conj) {
	$conj = " ".$conj." ";
	$newcrit = "";
	if (strlen($orig) > 0) {
		$newcrit = $orig.$conj.$crit;
	}
	else {
		$newcrit = $orig.$crit;
	}
	return $newcrit;
}
?>