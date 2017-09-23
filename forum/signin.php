<?php
/**
 * Sign In
 *
 * @package Simple Forum
 * @author  robertnduati
 */
 
require 'includes.php';

$layout = GetPage('signin', '{{ST:signin}}');

if(defined('SITE_NAME') AND SITE_NAME != ''){
	$layout->AddContentById('meta_title', SITE_NAME);
}
if(defined('SITE_META_DESCRIPTION') AND SITE_META_DESCRIPTION != ''){
	$layout->AddContentById('meta_desc', SITE_META_DESCRIPTION);
}

$layout->AddContentById('breadcrumbs', ' <li class="active">{{ST:signin}}</li>');

if(isset($_POST['submit'])){
	$errors = false;
	
	
	
	if(!isset($_POST['username_or_email']) OR $_POST['username_or_email'] == ''){
		$errors = true;
		$layout->AddContentById('alert', $layout->GetContent('alert'));
		$layout->AddContentById('alert_nature', ' alert-error');
		$layout->AddContentById('alert_heading', '{{ST:error}}!');
		$layout->AddContentById('alert_message', '{{ST:all_fields_are_required}}');
	}
	
	if(!isset($_POST['password']) OR $_POST['password'] == ''){
		$errors = true;
		$layout->AddContentById('alert', $layout->GetContent('alert'));
		$layout->AddContentById('alert_nature', ' alert-error');
		$layout->AddContentById('alert_heading', '{{ST:error}}!');
		$layout->AddContentById('alert_message', '{{ST:all_fields_are_required}}');
	}
	
	
	if($errors == false){
		
		$user = $db->get_row("SELECT * FROM " . TABLES_PREFIX . "users WHERE email = '" . $_POST['username_or_email'] . "'  OR username = '" . $_POST['username_or_email'] . "' ORDER BY id DESC LIMIT 0,1");
		if($user AND ($user->password == encode_password($_POST['password']))){
			if($user->status == 'pending'){
				$layout->AddContentById('alert', $layout->GetContent('alert'));
				$layout->AddContentById('alert_nature', ' alert-error');
				$layout->AddContentById('alert_heading', '{{ST:error}}!');
				$layout->AddContentById('alert_message', '{{ST:your_account_not_activated}}');
			}elseif($user->status == 'banned'){
				$layout->AddContentById('alert', $layout->GetContent('alert'));
				$layout->AddContentById('alert_nature', ' alert-error');
				$layout->AddContentById('alert_heading', '{{ST:error}}!');
				$layout->AddContentById('alert_message', '{{ST:your_account_is_banned}}');
			}else{
			
				$_SESSION[TABLES_PREFIX.'sforum_logged_in'] = true;
				$_SESSION[TABLES_PREFIX.'sforum_user_id'] = $user->id;
				$_SESSION[TABLES_PREFIX.'sforum_user_role'] = $user->role;
				$_SESSION[TABLES_PREFIX.'sforum_user_username'] = $user->username;
				if(isset($_POST['remember_me'])){
					setcookie(TABLES_PREFIX . COOKIE_NAME, 'email='.$_POST['username_or_email'].'&hash='.encode_password($_POST['password']), time() + COOKIE_TIME);
				}
				header('Location: '.FORUM_URL);
				exit;
			}
		}else{
			$layout->AddContentById('alert', $layout->GetContent('alert'));
			$layout->AddContentById('alert_nature', ' alert-error');
			$layout->AddContentById('alert_heading', '{{ST:error}}!');
			$layout->AddContentById('alert_message', '{{ST:the_info_is_not_correct}}');
		}
	}
	
}

$layout->RenderViewAndExit();
