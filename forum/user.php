<?php

require 'includes.php';

if(!Users_IsUserLoggedIn() OR !Users_IsUserAdmin(Users_CurrentUserId())){
	Leave(FORUM_URL);
}

$layout = GetPage('user', '{{ST:user_management}}');
$layout->AddContentById('breadcrumbs', ' <li><a href="'.FORUM_URL.'">{{ST:home}}</a> <span class="divider">/</span> </li> <li><a href="'.FORUM_URL.'users.php">{{ST:user_management}}</a> <span class="divider">/</span> </li><li class="active">{{ST:edit_user}}</li>');


if(isset($_GET['id'])){
	$id = intval($_GET['id']);
}else{
	Leave(FORUM_URL.'users.php');
}

$admin = $db->get_row("SELECT * FROM " . TABLES_PREFIX . "users WHERE id = $id ORDER BY id DESC LIMIT 0,1");

if(isset($_POST['delete'])){
	$db->query("DELETE FROM " . TABLES_PREFIX . "users WHERE id = " . $id);
	$db->query("DELETE FROM " . TABLES_PREFIX . "posts_following WHERE user_id = " . $id);
	Leave(FORUM_URL.'users.php');
}

if(isset($_GET['approve'])){
	$db->update(TABLES_PREFIX . "users", array('status'=>'active'), array('id'=>intval($id)), array("%s"));
	
	$to      = $admin->email; 
			$subject = get_option('site_name').": " . $strings->Get('you_have_been_approved_subject');
			$message = ' 
 
'.$strings->Get('you_have_been_approved_body').'
 
'.FORUM_URL.' 
 
';
                      
			$from = WEBMASTER_EMAIL;
			$headers = "From: $from";
			mail($to, $subject, $message, $headers);
	
	Leave(FORUM_URL.'users.php?user_approved=1');
}

$layout->AddContentById('id', $admin->id);

$achievements = $db->get_results("SELECT * FROM " . TABLES_PREFIX . "achievements WHERE type = 'manual_assign'" );
$achievements_html = '';
if($achievements){
	foreach($achievements as $a){
		$achievements_html .= '<lable><input type="checkbox" value="'.$a->id.'" name="achievements[]" {{ID:achievement_state_'.$a->id.'}}> '.$a->name.'</lable><br/>';
	}
}
$layout->AddContentById('achievements', $achievements_html);

if(isset($_POST['submit'])){
	
	$errors = false;
	$values = array();
	$format = array();
	$error_msg = '';
	
	if($admin->status != 'muted'){
		$layout->AddContentById('mute_state', 'style="display: none;"');
	}
	
	if(isset($_POST['email']) AND $_POST['email'] != ''){
		$layout->AddContentById('email', $_POST['email']);
		$check_email = $db->get_row("SELECT * FROM " . TABLES_PREFIX . "users WHERE email = '" . $_POST['email'] . "' ORDER BY id DESC LIMIT 0,1");
		if($check_email AND intval($check_email->id) != $id){
			$errors = true;
			$error_msg .=  '{{ST:email_already_in_use}} ';
		}elseif(!ValidateEmail($_POST['email'])){
			$errors = true;
			$error_msg .=  '{{ST:email_is_invalid}} ';
		}else{
			$values['email'] = $_POST['email'];
			$format[] = "%s";
		}
	}else{
		$errors = true;
		$error_msg .= '{{ST:email_required}} ';
	}
	
	
	if(isset($_POST['username']) AND $_POST['username'] != ''){
		$layout->AddContentById('username', $_POST['username']);
		$check_username = $db->get_row("SELECT * FROM " . TABLES_PREFIX . "users WHERE username = '" . $_POST['username'] . "' ORDER BY id DESC LIMIT 0,1");
		if($check_username AND intval($check_username->id) != $id){
			$errors = true;
			$error_msg .=  '{{ST:username_already_in_use}} ';
		}else{
			$values['username'] = $_POST['username'];
			$format[] = "%s";
		}
	}else{
		$errors = true;
		$error_msg .= '{{ST:username_required}} ';
	}
	
	if(isset($_POST['role']) AND $_POST['role'] != ''){
		$layout->AddContentById($_POST['role'].'_selected', 'selected');
		$values['role'] = $_POST['role'];
		$format[] = "%s";
	}else{
		$errors = true;
		$error_msg .= '{{ST:role_required}} ';
	}
	
	
	if(isset($_POST['deny_access'])){
		$values['status'] = 'banned';
		$format[] = "%s";
		$layout->AddContentById('deny_access_state', 'checked="checked"');
	}elseif($admin->status != 'muted'){
		$values['status'] = 'active';
		$format[] = "%s";
	}
	
	if(isset($_POST['unmute'])){
		$values['status'] = 'active';
		$format[] = "%s";
		$layout->AddContentById('unmute_state', 'checked="checked"');
	}
	
	if(isset($_POST['achievements']) AND count($_POST['achievements']) > 0){
		$values['achievements'] = serialize($_POST['achievements']);
		$format[] = "%s";
		foreach($_POST['achievements'] as $a){
			$layout->AddContentById('achievement_state_'.$a, 'checked="checked"');
		}
	}else{
		$values['achievements'] = "";
		$format[] = "%s";
	}
	
	if(!$errors){
		$db->update(TABLES_PREFIX . "users", $values, array('id'=>$id), $format);
		$layout->AddContentById('alert', $layout->GetContent('alert'));
		$layout->AddContentById('alert_nature', ' alert-success');
		$layout->AddContentById('alert_heading', '{{ST:success}}!');
		$layout->AddContentById('alert_message', '{{ST:the_item_has_been_saved}}');
	}else{
		$layout->AddContentById('alert', $layout->GetContent('alert'));
		$layout->AddContentById('alert_nature', ' alert-error');
		$layout->AddContentById('alert_heading', '{{ST:error}}!');
		$layout->AddContentById('alert_message', $error_msg);
	}
}else{
	
	$layout->AddContentById('username', $admin->username);
	$layout->AddContentById('email', $admin->email);
	
	if(get_option('approve_new_user') == 'y' AND $admin->status == 'pending'){
		$layout->AddContentById('alert', $layout->GetContent('alert'));
		$layout->AddContentById('alert_nature', ' alert-info');
		$layout->AddContentById('alert_heading', '{{ST:new_user}}!');
		$layout->AddContentById('alert_message', '{{ST:this_user_is_waiting_to_be_approved}} <a href="' . FORUM_URL . 'user.php?approve=1&id=' . $id . '">{{ST:approve}}</a>');
	}
	
	if($admin->achievements){
		$achievements_array =  unserialize($admin->achievements);
		foreach($achievements_array as $a){
			$layout->AddContentById('achievement_state_'.$a, 'checked="checked"');
		}
	}
	
	if($admin->status == 'banned'){
		$layout->AddContentById('deny_access_state', 'checked="checked"');
	}
	
	if($admin->status != 'muted'){
		$layout->AddContentById('mute_state', 'style="display: none;"');
	}
	
	$layout->AddContentById($admin->role.'_selected', 'selected');
	
}

$layout->RenderViewAndExit();
