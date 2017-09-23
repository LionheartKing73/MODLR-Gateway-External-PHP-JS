<?php

require 'includes.php';

if(!Users_IsUserLoggedIn() OR !Users_IsUserAdmin(Users_CurrentUserId())){
	Leave(FORUM_URL);
}

$layout = GetPage('achievement_edit', '{{ST:edit_achievement}}');

$layout->AddContentById('breadcrumbs', ' <li><a href="'.FORUM_URL.'">{{ST:home}}</a> <span class="divider">/</span> </li> <li><a href="'.FORUM_URL.'achievements.php">{{ST:achievements}}</a> <span class="divider">/</span> </li><li class="active">{{ST:edit_achievement}}</li>');

if(isset($_GET['id'])){
	$id = intval($_GET['id']);
}else{
	Leave(FORUM_URL.'achievements.php');
}

$achievement = $db->get_row("SELECT * FROM " . TABLES_PREFIX . "achievements WHERE id = $id ORDER BY id DESC LIMIT 0,1");

if(isset($_GET['delete_icon']) AND intval($_GET['delete_icon']) == 1){
	if($achievement->icon){
		$db->update(TABLES_PREFIX . "achievements", array('icon'=>''), array('id'=>$id), array("%s"));
		$file = $achievement->icon;
		$exists = file_exists($file);
		if($exists){
			unlink($file);
			Leave(FORUM_URL.'achievement_edit.php?id='.$id.'&message=image_deleted');
		}else{
			$layout->AddContentById('alert', $layout->GetContent('alert'));
			$layout->AddContentById('alert_nature', ' alert-error');
			$layout->AddContentById('alert_heading', '{{ST:error}}!');
			$layout->AddContentById('alert_message', '{{ST:image_does_not_exist}}');
		}
	}else{
		$layout->AddContentById('alert', $layout->GetContent('alert'));
		$layout->AddContentById('alert_nature', ' alert-error');
		$layout->AddContentById('alert_heading', '{{ST:error}}!');
		$layout->AddContentById('alert_message', '{{ST:image_does_not_exist}}');
	}
}

if(isset($_GET['message']) AND $_GET['message'] != ''){
	
	if($_GET['message'] == 'image_deleted'){
		$layout->AddContentById('alert', $layout->GetContent('alert'));
		$layout->AddContentById('alert_nature', ' alert-success');
		$layout->AddContentById('alert_heading', '{{ST:success}}!');
		$layout->AddContentById('alert_message', '{{ST:the_image_has_been_deleted}}');
	}
}

if(isset($_POST['delete'])){
	$db->query("DELETE FROM " . TABLES_PREFIX . "achievements WHERE id = " . $id);
	if($achievement->icon){
		$db->update(TABLES_PREFIX . "achievements", array('icon'=>''), array('id'=>$id), array("%s"));
		$file = $achievement->icon;
		$exists = file_exists($file);
		if($exists){
			unlink($file);
		}
	}
	Leave(FORUM_URL.'achievements.php');
}

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
	}else{
		$values['description'] = '';
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
		$db->update(TABLES_PREFIX . "achievements", $values, array('id'=>$id), $format);
		
		$layout->AddContentById('alert', $layout->GetContent('alert'));
		$layout->AddContentById('alert_nature', ' alert-success');
		$layout->AddContentById('alert_heading', '{{ST:success}}!');
		$layout->AddContentById('alert_message', '{{ST:the_achievement_has_been_saved}}');	
		if(isset($_FILES["icon"]["name"]) AND $_FILES["icon"]["name"] != ''){
				move_uploaded_file($_FILES["icon"]["tmp_name"], $icon);
				if($achievement->icon){
					$file = $achievement->icon;
					$exists = file_exists($file);
					if($exists){
						unlink($file);
					}
				}
			$layout->AddContentById('current_icon', '<p><a href="'.FORUM_URL . $icon.'" target="_blank">{{ST:view_img}}</a> | <a onclick="return confirm(\'{{ST:are_you_sure}}\');" title="{{ST:delete}}" href="'.FORUM_URL.'achievement_edit.php?id={{ID:id}}&delete_icon=1">{{ST:delete}}</a><p>');
		}else{
			if($achievement->icon){
				$layout->AddContentById('current_icon', '<p><a href="'.FORUM_URL . $achievement->icon.'" target="_blank">{{ST:view_img}}</a> | <a onclick="return confirm(\'{{ST:are_you_sure}}\');" title="{{ST:delete}}" href="'.FORUM_URL.'achievement_edit.php?id={{ID:id}}&delete_icon=1">{{ST:delete}}</a><p>');
			}
		}
	}else{
		$layout->AddContentById('alert', $layout->GetContent('alert'));
		$layout->AddContentById('alert_nature', ' alert-error');
		$layout->AddContentById('alert_heading', '{{ST:error}}!');
		$layout->AddContentById('alert_message', $error_msg);
	}
}else{
	
	$layout->AddContentById('name', $achievement->name);
	$layout->AddContentById('description', $achievement->description);
	$layout->AddContentById('starting', $achievement->start_from);
	$layout->AddContentById('ending', $achievement->end_at);
	$layout->AddContentById($achievement->type.'_selected', 'selected');
	if($achievement->icon){
		$layout->AddContentById('current_icon', '<p><a href="'.FORUM_URL . $achievement->icon.'" target="_blank">{{ST:view_img}}</a> | <a onclick="return confirm(\'{{ST:are_you_sure}}\');" title="{{ST:delete}}" href="'.FORUM_URL.'achievement_edit.php?id={{ID:id}}&delete_icon=1">{{ST:delete}}</a><p>');
	}
}

$layout->AddContentById('id', $achievement->id);

$layout->RenderViewAndExit();
