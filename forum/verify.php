<?php
/**
 * Sign Up
 *
 * @package Simple Forum
 * @author  robertnduati
 */
 
require 'includes.php';

$layout = GetPage('verify', '{{ST:account_activation}}');

$layout->AddContentById('breadcrumbs', ' <li><a href="'.FORUM_URL.'signin.php">{{ST:signin}}</a> <span class="divider">/</span> </li><li class="active">{{ST:account_activation}}</li>');


if(isset($_GET['email']) && !empty($_GET['email']) AND isset($_GET['hash']) && !empty($_GET['hash'])){  
	$email = $_GET['email'];
	$hash = $_GET['hash']; 
	
	$user = $db->get_row("SELECT * FROM " . TABLES_PREFIX . "users WHERE email = '" . $email . "'  AND hash = '" . $hash . "' ORDER BY id DESC LIMIT 0,1");
	if($user){
		$db->update(TABLES_PREFIX . "users", array('status'=>'active'), array('id'=>intval($user->id)), array("%s"));
		
		if(defined('EMAIL_ADMIN_ON_SIGNUP') AND EMAIL_ADMIN_ON_SIGNUP == true){
			$to = WEBMASTER_EMAIL;
			$subject = "[" . $_SERVER['SERVER_NAME'] . "]".SITE_NAME.": " . $strings->Get('new_user_subject');
			$message = $strings->Get('new_user_body') . '

' . FORUM_URL;
			$from = WEBMASTER_EMAIL;
			$headers = "From: $from";
			ini_set("sendmail_from", $from);	
			mail($to,$subject,$message,$headers);
		}
		
		$layout->AddContentById('alert', $layout->GetContent('alert'));
		$layout->AddContentById('alert_nature', ' alert-success');
		$layout->AddContentById('alert_heading', '{{ST:success}}!');
		$layout->AddContentById('alert_message', '{{ST:activation_success}}');
	}else{
		$layout->AddContentById('alert', $layout->GetContent('alert'));
		$layout->AddContentById('alert_nature', ' alert-error');
		$layout->AddContentById('alert_heading', '{{ST:error}}!');
		$layout->AddContentById('alert_message', '{{ST:failed_activation}}');
	}
}else{  
	$layout->AddContentById('alert', $layout->GetContent('alert'));
	$layout->AddContentById('alert_nature', ' alert-error');
	$layout->AddContentById('alert_heading', '{{ST:error}}!');
	$layout->AddContentById('alert_message', '{{ST:failed_activation}}');
}

$layout->RenderViewAndExit();
