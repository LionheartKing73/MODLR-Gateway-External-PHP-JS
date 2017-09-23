<?php
/**
 * Sign Up
 *
 * @package Simple Forum
 * @author  robertnduati
 */
 
require 'includes.php';

$layout = GetPage('signup', '{{ST:sign_up}}');

if(defined('SITE_NAME') AND SITE_NAME != ''){
	$layout->AddContentById('meta_title', SITE_NAME);
}
if(defined('SITE_META_DESCRIPTION') AND SITE_META_DESCRIPTION != ''){
	$layout->AddContentById('meta_desc', SITE_META_DESCRIPTION);
}

$layout->AddContentById('breadcrumbs', ' <li><a href="'.FORUM_URL.'signin.php">{{ST:signin}}</a> <span class="divider">/</span> </li><li class="active">{{ST:sign_up}}</li>');

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
	
	if(!$errors){
		$values['status'] = 'pending';
		$format[] = "%s";
		$values['added_on'] = date('Y-m-d H:i:s');
		$format[] = "%s";
		$values['role'] = 'user';
		$format[] = "%s";
		$values['likes'] = 0;
		$format[] = "%d";
		
		$key = $values['username'] . $values['email'] . date('mY');  
		$values['hash'] = md5($key);
		$format[] = "%s";
		
		if($db->insert(TABLES_PREFIX . "users", $values, $format)){
			$new_record = $db->insert_id;
			
			if(get_option('approve_new_user') == 'y'){
				$to = WEBMASTER_EMAIL;
					$subject = "[" . $_SERVER['SERVER_NAME'] . "]".SITE_NAME.": " . $strings->Get('approve_user_subject');
					$message = $strings->Get('approve_user_body') . '

' . FORUM_URL . 'user.php?id=' . $new_record;
					$from = WEBMASTER_EMAIL;
					$headers = "From: $from";
					ini_set("sendmail_from", $from);	
					mail($to,$subject,$message,$headers);
					
					$layout->AddContentById('alert', $layout->GetContent('alert'));
			$layout->AddContentById('alert_nature', ' alert-success');
			$layout->AddContentById('alert_heading', '{{ST:success}}!');
			$layout->AddContentById('alert_message', '{{ST:signup_success_wait_for_approval}}');
			}else{
			
			$to      = $values['email']; 
			$subject = "[" . $_SERVER['SERVER_NAME'] . "]Just a Forum: " . $strings->Get('signup_subject');
			$message = ' 
 
'.$strings->Get('thank_you_for_signing_up').'
'.$strings->Get('signup_email_msg').'

 
'.$strings->Get('click_here_to_activate').': 
 
'.FORUM_URL.'verify.php?email='.$values['email'].'&hash='.$values['hash'].' 
 
';
                      
			$from = WEBMASTER_EMAIL;
			$headers = "From: $from";
			mail($to, $subject, $message, $headers);
			
			$layout->AddContentById('alert', $layout->GetContent('alert'));
			$layout->AddContentById('alert_nature', ' alert-success');
			$layout->AddContentById('alert_heading', '{{ST:success}}!');
			$layout->AddContentById('alert_message', '{{ST:signup_success}}');
			
			}
			
			
			
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
