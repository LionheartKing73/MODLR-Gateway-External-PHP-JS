<?php
/**
 * Forgot Password
 *
 * @package Simple Forum
 * @author  robertnduati
 */
 
require 'includes.php';

$layout = GetPage('forgot', '{{ST:forgot_password}}');

if(defined('SITE_NAME') AND SITE_NAME != ''){
	$layout->AddContentById('meta_title', SITE_NAME);
}
if(defined('SITE_META_DESCRIPTION') AND SITE_META_DESCRIPTION != ''){
	$layout->AddContentById('meta_desc', SITE_META_DESCRIPTION);
}

$layout->AddContentById('breadcrumbs', ' <li><a href="'.FORUM_URL.'signin.php">{{ST:signin}}</a> <span class="divider">/</span> </li><li class="active">{{ST:forgot_password}}</li>');

if(isset($_POST['submit'])){
	if(isset($_POST['username_or_email'])){
		
		$user = $db->get_row("SELECT * FROM " . TABLES_PREFIX . "users WHERE email = '" . $_POST['username_or_email'] . "'  OR username = '" . $_POST['username_or_email'] . "' ORDER BY id DESC LIMIT 0,1");
		if($user){
			$new_password = GeneratePassword();
			$db->update(TABLES_PREFIX . "users", array('password'=>encode_password($new_password)), array('id'=>intval($user->id)), array("%s"));
			
			$to = $user->email;
			$subject = "[" . $_SERVER['SERVER_NAME'] . "]".SITE_NAME.": " . $strings->Get('forgot_password_subject');
			$message = $strings->Get('your_new_password_is') . ": " . $new_password;
			$from = WEBMASTER_EMAIL;
			$headers = "From: $from";
			ini_set("sendmail_from", $from);	
			mail($to,$subject,$message,$headers);
			$layout->AddContentById('alert', $layout->GetContent('alert'));
			$layout->AddContentById('alert_nature', ' alert-success');
			$layout->AddContentById('alert_heading', '{{ST:success}}!');
			$layout->AddContentById('alert_message', '{{ST:forgot_password_success_msg}}');
		}else{
			$layout->AddContentById('alert', $layout->GetContent('alert'));
			$layout->AddContentById('alert_nature', ' alert-error');
			$layout->AddContentById('alert_heading', '{{ST:error}}!');
			$layout->AddContentById('alert_message', '{{ST:the_info_is_not_correct}}');
		}
	}else{
		$layout->AddContentById('alert', $layout->GetContent('alert'));
		$layout->AddContentById('alert_nature', ' alert-error');
		$layout->AddContentById('alert_heading', '{{ST:error}}!');
		$layout->AddContentById('alert_message', '{{ST:the_info_is_not_correct}}');
	}
}else{
	$layout->AddContentById('alert', $layout->GetContent('alert'));
	$layout->AddContentById('alert_nature', ' alert-info');
	$layout->AddContentById('alert_heading', '{{ST:info}}:');
	$layout->AddContentById('alert_message', '{{ST:forgot_password_info}}');
}

$layout->RenderViewAndExit();
