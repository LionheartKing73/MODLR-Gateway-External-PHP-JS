<?php

require 'includes.php';

if(!Users_IsUserLoggedIn() OR !Users_IsUserAdmin(Users_CurrentUserId())){
	Leave(FORUM_URL);
}

$layout = GetPage('add_user', '{{ST:add_user}}');

$layout->AddContentById('breadcrumbs', ' <li><a href="'.FORUM_URL.'">{{ST:home}}</a> <span class="divider">/</span> </li> <li><a href="'.FORUM_URL.'users.php">{{ST:user_management}}</a> <span class="divider">/</span> </li><li class="active">{{ST:add_user}}</li>');


if(isset($_POST['submit'])){
	
	$errors = false;
	$values = array();
	$format = array();
	$error_msg = '';
	
	if(isset($_POST['email']) AND $_POST['email'] != ''){
		$layout->AddContentById('email', $_POST['email']);
		$check_email = $db->get_row("SELECT * FROM " . TABLES_PREFIX . "users WHERE email = '" . $_POST['email'] . "' ORDER BY id DESC LIMIT 0,1");
		if($check_email){
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
		if($check_username){
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
	
	
	if(isset($_POST['password']) AND $_POST['password'] != ''){
		if($_POST['password'] != $_POST['cpassword']){
			$errors = true;
			$error_msg .= '{{ST:password_not_confirmed}} ';
		}else{
			$values['password'] = encode_password($_POST['password']);
			$format[] = "%s";
		}
	}else{
		$errors = true;
		$error_msg .= '{{ST:password_required}} ';
	}
	
	if(isset($_POST['role']) AND $_POST['role'] != ''){
		$layout->AddContentById($_POST['role'].'_selected', 'selected');
		$values['role'] = $_POST['role'];
		$format[] = "%s";
	}else{
		$errors = true;
		$error_msg .= '{{ST:role_required}} ';
	}
	
	
	if(isset($_POST['email_password'])){
		$layout->AddContentById('email_password_state', 'checked="checked"');
	}
	
	if(!$errors){
		$values['status'] = 'active';
		$format[] = "%s";
		$values['likes'] = 0;
		$format[] = "%d";
		$values['added_on'] = date('Y-m-d H:i:s');
		$format[] = "%s";
		
		if($db->insert(TABLES_PREFIX . "users", $values, $format)){
			if(isset($_POST['email_password'])){
				$subject    = '[' . $_SERVER['SERVER_NAME'] . '] '.$strings->Get('new_user_email_subject');
				$message = '
'. $strings->Get('new_user_email_text') .'

' . $strings->Get('username') . ': ' . $values['username'] . '
' . $strings->Get('password') . ': ' . $_POST['password'] . '

' . $strings->Get('new_user_email_link') . ': ' . FORUM_URL . '

' . $strings->Get('thank_you');
				$headers = "From:" . WEBMASTER_EMAIL;	
				mail($values['email'],$subject,$message,$headers);
			}
			
			
			$layout->AddContentById('alert', $layout->GetContent('alert'));
			$layout->AddContentById('alert_nature', ' alert-success');
			$layout->AddContentById('alert_heading', '{{ST:success}}!');
			$layout->AddContentById('alert_message', '{{ST:the_user_has_been_saved}}');
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
