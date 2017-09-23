<?php

if(isset($_GET['forum_page']) AND $_GET['forum_page'] != ''){
	$requestURI = explode('/', $_GET['forum_page']);
	$function = $requestURI[0];
	switch($function){
		case 'thread':
			 if($requestURI[2]){
			 	$_GET['id'] = intval($requestURI[2]);
			 	require 'thread.php';
			 }else{
			 	header('Location: index.php');
				exit();
			 }
			 break;
		case 'forum':
			 if($requestURI[2]){
			 	$_GET['id'] = intval($requestURI[2]);
			 	require 'index.php';
			 }else{
			 	header('Location: index.php');
				exit();
			 }
			 break;
		default:
			require '404.php';
	}
}else{
	header('Location: index.php');
	exit();
}
