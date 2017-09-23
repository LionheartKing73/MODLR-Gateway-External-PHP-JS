<?php

require 'includes.php';

if(!Users_IsUserLoggedIn() OR !Users_IsUserAdmin(Users_CurrentUserId())){
	Leave(FORUM_URL);
}

$layout = GetPage('achievement_new', '{{ST:create_achievement}}');

$layout->AddContentById('breadcrumbs', ' <li><a href="'.FORUM_URL.'">{{ST:home}}</a> <span class="divider">/</span> </li> <li><a href="'.FORUM_URL.'achievements.php">{{ST:achievements}}</a> <span class="divider">/</span> </li><li class="active">{{ST:create_achievement}}</li>');


if(isset($_POST['submit'])){
	
	$errors = false;
	$values = array();
	$format = array();
	$error_msg = '';
	
	if(isset($_POST['name']) AND $_POST['name'] != ''){
		$layout->AddContentById('name', $_POST['name']);
		$values['name'] = $_POST['name'];
		$format[] = "%s";
	}else{
		$errors = true;
		$error_msg .= '{{ST:name_required}} ';
	}
	
	if(isset($_POST['description']) AND $_POST['description'] != ''){
		$layout->AddContentById('description', $_POST['description']);
		$values['description'] = $_POST['description'];
		$format[] = "%s";
	}
	
	if(isset($_POST['type']) AND $_POST['type'] != ''){
		$layout->AddContentById($_POST['type'].'_selected', 'selected');
		$values['type'] = $_POST['type'];
		$format[] = "%s";
	}else{
		$errors = true;
		$error_msg .= '{{ST:type_required}} ';
	}
	
	if(isset($_POST['type']) AND $_POST['type'] != 'manual_assign'){
	
		if(isset($_POST['starting']) AND $_POST['starting'] != ''){
			$layout->AddContentById('starting', $_POST['starting']);
			$values['start_from'] = $_POST['starting'];
			$format[] = "%d";
		}else{
			$errors = true;
			$error_msg .= '{{ST:starting_required}} ';
		}
	
		if(isset($_POST['ending']) AND $_POST['ending'] != ''){
			$layout->AddContentById('ending', $_POST['ending']);
			$values['end_at'] = $_POST['ending'];
			$format[] = "%d";
		}else{
			$errors = true;
			$error_msg .= '{{ST:ending_required}} ';
		}
	
	}
	
	$icon = '';
	if(isset($_FILES["icon"]["name"]) AND $_FILES["icon"]["name"] != ''){
		if($_FILES["icon"]["error"] > 0){
			$errors = true;
			$error_msg .= '{{ST:icon}}: ' . UploadError($_FILES["icon"]["error"]) . '. ';
		}else{
			if(!is_image_file($_FILES["icon"]["name"])){
				$errors = true;
				$error_msg .= '{{ST:icon_needs_to_be_image}} ';
			}else{
				$icon = 'img/badges/' . set_filename('img/badges/', $_FILES["icon"]["name"]);
				$values['icon'] = $icon;
				$format[] = "%s";
			}
		}
	}
	
	if(!$errors){
			
		if($db->insert(TABLES_PREFIX . "achievements", $values, $format)){
			if(isset($_FILES["icon"]["name"]) AND $_FILES["icon"]["name"] != ''){
				move_uploaded_file($_FILES["icon"]["tmp_name"], $icon);
			}
			
			$layout->AddContentById('alert', $layout->GetContent('alert'));
			$layout->AddContentById('alert_nature', ' alert-success');
			$layout->AddContentById('alert_heading', '{{ST:success}}!');
			$layout->AddContentById('alert_message', '{{ST:the_achievement_has_been_saved}}');
		}else{
			$layout->AddContentById('alert', $layout->GetContent('alert'));
			$layout->AddContentById('alert_nature', ' alert-error');
			$layout->AddContentById('alert_heading', '{{ST:error}}!');
			$layout->AddContentById('alert_message', '{{ST:unknow_error_try_again}}');
		}
	}else{
		$layout->AddContentById('alert', $layout->GetContent('alert'));
		$layout->AddContentById('alert_nature', ' alert-error');
		$layout->AddContentById('alert_heading', '{{ST:error}}!');
		$layout->AddContentById('alert_message', $error_msg);
	}
}


$layout->RenderViewAndExit();
