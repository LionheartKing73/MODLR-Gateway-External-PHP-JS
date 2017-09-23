<?php

require 'includes.php';

if(defined('PRIVATE_FORUM') AND PRIVATE_FORUM == true AND !Users_IsUserLoggedIn()){
	Leave(Users_SignInPageUrl());
}


$layout = GetPage('profiles', '{{ST:profile}}');

if(defined('SITE_NAME') AND SITE_NAME != ''){
	$layout->AddContentById('meta_title', SITE_NAME);
}
if(defined('SITE_META_DESCRIPTION') AND SITE_META_DESCRIPTION != ''){
	$layout->AddContentById('meta_desc', SITE_META_DESCRIPTION);
}

$layout->AddContentById('breadcrumbs', ' <li><a href="'.FORUM_URL.'">{{ST:home}}</a> <span class="divider">/</span> </li><li class="active">{{ST:profiles}}</li>');

if(isset($_GET['id'])){
	$id = intval($_GET['id']);
}else{
	Leave(FORUM_URL);
}

$admin = $db->get_row("SELECT * FROM " . TABLES_PREFIX . "users WHERE id = $id ORDER BY id DESC LIMIT 0,1");

if(isset($_GET['delete_photo']) AND intval($_GET['delete_photo']) == 1){
	if($admin->photo){
		$db->update(TABLES_PREFIX . "users", array('photo'=>''), array('id'=>$id), array("%s"));
		$file = $admin->photo;
		$exists = file_exists($file);
		if($exists){
			unlink($file);
			Leave('profiles.php?id='.$id.'&message=image_deleted');
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

if(isset($_GET['mute']) AND intval($_GET['mute']) == 1){
	if(Users_IsUserAdminOrModerator(Users_CurrentUserId())){
		$db->update(TABLES_PREFIX . "users", array('status'=>'muted'), array('id'=>$id), array("%s"));
		$layout->AddContentById('alert', $layout->GetContent('alert'));
		$layout->AddContentById('alert_nature', ' alert-success');
		$layout->AddContentById('alert_heading', '{{ST:success}}!');
		$layout->AddContentById('alert_message', '{{ST:the_user_has_been_muted}}');
		$admin = $db->get_row("SELECT * FROM " . TABLES_PREFIX . "users WHERE id = $id ORDER BY id DESC LIMIT 0,1");
		
		$to = WEBMASTER_EMAIL;
		$subject = "[" . $_SERVER['SERVER_NAME'] . "]Forum: " . $strings->Get('muted_user');
		$message = $strings->Get('a_user_has_been_muted') . '

'.FORUM_URL.'user.php?id='.$id;
		$from = WEBMASTER_EMAIL;
		$headers = "From: $from";
		ini_set("sendmail_from", $from);	
		mail($to,$subject,$message,$headers);
	}
}

if(isset($_GET['unmute']) AND intval($_GET['unmute']) == 1){
	if(Users_IsUserAdminOrModerator(Users_CurrentUserId())){
		$db->update(TABLES_PREFIX . "users", array('status'=>'active'), array('id'=>$id), array("%s"));
		$layout->AddContentById('alert', $layout->GetContent('alert'));
		$layout->AddContentById('alert_nature', ' alert-success');
		$layout->AddContentById('alert_heading', '{{ST:success}}!');
		$layout->AddContentById('alert_message', '{{ST:the_user_has_been_unmuted}}');
		$admin = $db->get_row("SELECT * FROM " . TABLES_PREFIX . "users WHERE id = $id ORDER BY id DESC LIMIT 0,1");
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

if(isset($_POST['send'])){
	
	$errors = false;
	$values = array();
	$format = array();
	$error_msg = '';
	
	if(isset($_POST['message']) AND $_POST['message'] != ''){
		$layout->AddContentById('message', $_POST['message']);
		$values['message'] = $_POST['message'];
		$format[] = "%s";
	}else{
		$errors = true;
		$error_msg .= '{{ST:message_required}} ';
	}
	
	if(Users_CurrentUserId() == $id){
		$errors = true;
		$error_msg .= '{{ST:cant_send_yourself_a_message}} ';
	}
	
	if(!$errors){
		$values['sender'] = Users_CurrentUserId();
		$format[] = "%d";
		$values['receiver'] = $id;
		$format[] = "%d";
		$values['date_sent'] = date('Y-m-d H:i:s');
		$format[] = "%s";
		
		if($db->insert(TABLES_PREFIX . "messages", $values, $format)){
			$user_details = Users_GetUserDetails($id);
			
			
			if(!$db->get_results("SELECT * FROM " . TABLES_PREFIX . "contacts WHERE user_id = ".Users_CurrentUserId()." AND contact_id = $id")){
				$db->insert(TABLES_PREFIX . "contacts", array('user_id'=>Users_CurrentUserId(), 'contact_id'=>$id, 'contact_username'=>$user_details['username']), array("%d","%d","%s"));
			}
			
			if(!$db->get_results("SELECT * FROM " . TABLES_PREFIX . "contacts WHERE user_id = $id AND contact_id = ".Users_CurrentUserId())){
				$db->insert(TABLES_PREFIX . "contacts", array('user_id'=>$id, 'contact_id'=>Users_CurrentUserId(), 'contact_username'=>Users_CurrentUserUsername()), array("%d","%d","%s"));
			}
			
			/*
			
			if($user_details['email']){
				$to = $user_details['email'];
				$subject = "[" . $_SERVER['SERVER_NAME'] . "]Forum: " . $strings->Get('message_subject') . Users_CurrentUserUsername();
				$message = $strings->Get('message_body') . '

' . FORUM_URL;
				$from = WEBMASTER_EMAIL;
				$headers = "From: $from";
				ini_set("sendmail_from", $from);	
				//mail($to,$subject,$message,$headers);
			}*/
			
			$layout->AddContentById('alert2', $layout->GetContent('alert'));
			$layout->AddContentById('alert_nature', ' alert-success');
			$layout->AddContentById('alert_heading', '{{ST:success}}!');
			$layout->AddContentById('alert_message', '{{ST:your_message_has_been_sent}}');
		}else{
			$layout->AddContentById('alert2', $layout->GetContent('alert'));
			$layout->AddContentById('alert_nature', ' alert-error');
			$layout->AddContentById('alert_heading', '{{ST:error}}!');
			$layout->AddContentById('alert_message', '{{ST:unknow_error_try_again}}');
		}
	}else{
		$layout->AddContentById('alert2', $layout->GetContent('alert'));
		$layout->AddContentById('alert_nature', ' alert-error');
		$layout->AddContentById('alert_heading', '{{ST:error}}!');
		$layout->AddContentById('alert_message', $error_msg);
	}
}

$layout->AddContentById('username', $admin->username);
$layout->AddContentById('bio', $admin->bio);
$layout->AddContentById('this_url', FORUM_URL.'profiles.php?id='.$id);
if($admin->photo){
	$layout->AddContentById('photo', $admin->photo);
	if(Users_IsUserAdminOrModerator(Users_CurrentUserId())){
		$layout->AddContentById('delete_photo', '<p><a onclick="return confirm(\'{{ST:are_you_sure}}\');" title="{{ST:delete}}" href="'.FORUM_URL.'profiles.php?id='.$id.'&delete_photo=1">{{ST:delete}}</a></p>');
	}
}else{
	$layout->AddContentById('photo', FORUM_URL.'img/anon.png');
}

if($admin->status != 'banned'){
	if(Users_IsUserAdmin(Users_CurrentUserId()) AND $admin->status != 'pending'){
		if($admin->status == 'active'){
			$layout->AddContentById('unmute_state', 'style="display:none"');
		}elseif($admin->status == 'muted'){
			$layout->AddContentById('mute_state', 'style="display:none"');
		}
	}elseif(Users_IsUserModerator(Users_CurrentUserId()) AND $admin->role == 'user'){
		if($admin->status == 'active'){
			$layout->AddContentById('unmute_state', 'style="display:none"');
		}elseif($admin->status == 'muted'){
			$layout->AddContentById('mute_state', 'style="display:none"');
		}
	}else{
		$layout->AddContentById('mute_unmute_state', ' display:none;');
	}
}else{
	$layout->AddContentById('mute_unmute_state', ' display:none;');
}

if($admin->facebook_url){
	$layout->AddContentById('facebook_url', '<a href="'.$admin->facebook_url.'" target="_blank">'.$admin->facebook_url.'</a>');
}

if($admin->twitter_url){
	$layout->AddContentById('twitter_url', '<a href="'.$admin->twitter_url.'" target="_blank">'.$admin->twitter_url.'</a>');
}

if($admin->website_url){
	$layout->AddContentById('website_url', '<a href="'.$admin->website_url.'" target="_blank">'.$admin->website_url.'</a>');
}

$layout->AddContentById('user_badges', Users_GetUserBadges($id));

$layout->AddContentById('threads_started', count($db->get_results("SELECT * FROM " . TABLES_PREFIX . "posts WHERE is_question = 'y' AND user_id =".intval($id) )));
$layout->AddContentById('total_posts', count($db->get_results("SELECT * FROM " . TABLES_PREFIX . "posts WHERE user_id =".intval($id) )));

$layout->RenderViewAndExit();
