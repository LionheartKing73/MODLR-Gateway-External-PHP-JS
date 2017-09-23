<?php

require 'includes.php';

if(!Users_IsUserLoggedIn()){
	Leave(FORUM_URL);
}

$layout = GetPage('profile', '{{ST:profile}}');

$layout->AddContentById('breadcrumbs', ' <li><a href="'.FORUM_URL.'">{{ST:home}}</a> <span class="divider">/</span> </li><li class="active">{{ST:profile}}</li>');

$id = intval(Users_CurrentUserId());

$admin = $db->get_row("SELECT * FROM " . TABLES_PREFIX . "users WHERE id = $id ORDER BY id DESC LIMIT 0,1");

if(isset($_GET['delete_photo']) AND intval($_GET['delete_photo']) == 1){
	if($admin->photo){
		$db->update(TABLES_PREFIX . "users", array('photo'=>''), array('id'=>$id), array("%s"));
		$file = $admin->photo;
		$exists = file_exists($file);
		if($exists){
			unlink($file);
			Leave(FORUM_URL.'profile.php?message=image_deleted');
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

$layout->AddContentById('id', $admin->id);

if($admin->facebook_id AND $admin->facebook_id != '' AND $admin->facebook_id != null){
	$layout->AddContentById('facbook_profile_hide', ' style="display: none;"');
}

if(isset($_POST['submit'])){
	
	$errors = false;
	$values = array();
	$format = array();
	$error_msg = '';
	
	if(!$admin->facebook_id){
		if(isset($_POST['opassword']) AND $_POST['opassword'] != ''){
			$check_pass = $db->get_row("SELECT * FROM " . TABLES_PREFIX . "users WHERE id = " . $id . " ORDER BY id DESC LIMIT 0,1");
			if(!$check_pass OR $check_pass->password != encode_password($_POST['opassword'])){
				$errors = true;
				$error_msg .=  '{{ST:current_password_not_correct}} ';
			}
		}else{
			$errors = true;
			$error_msg .= '{{ST:current_password_required}} ';
		}
	
		if(isset($_POST['password']) AND $_POST['password'] != ''){
			if($_POST['password'] != $_POST['cpassword']){
				$errors = true;
				$error_msg .= '{{ST:password_not_confirmed}} ';
			}else{
				$values['password'] = encode_password($_POST['password']);
				$format[] = "%s";
			}
		}
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
	
	if(isset($_POST['bio']) AND $_POST['bio'] != ''){
		$layout->AddContentById('bio', stripslashes($_POST['bio']));
		$values['bio'] = filterbadwords($_POST['bio']);
		$format[] = "%s";
	}else{
		$values['bio'] = '';
		$format[] = "%s";
	}
	
	if(isset($_POST['signature']) AND $_POST['signature'] != ''){
		$layout->AddContentById('signature', stripslashes($_POST['signature']));
		$values['signature'] = $_POST['signature'];
		$format[] = "%s";
	}else{
		$values['signature'] = '';
		$format[] = "%s";
	}
	
	if(isset($_POST['website_url']) AND $_POST['website_url'] != ''){
		$layout->AddContentById('website_url', $_POST['website_url']);
		
		if(!filter_var($_POST['website_url'], FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED)){
			$errors = true;
			$error_msg .=  '{{ST:invalid_website_url}} ';
		}else{
			$values['website_url'] = $_POST['website_url'];
			$format[] = "%s";
		}
	}else{
		$values['website_url'] = '';
		$format[] = "%s";
	}
	
	if(isset($_POST['facebook_url']) AND $_POST['facebook_url'] != ''){
		$layout->AddContentById('facebook_url', $_POST['facebook_url']);
		
		if(!filter_var($_POST['facebook_url'], FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED)){
			$errors = true;
			$error_msg .=  '{{ST:invalid_facebook_url}} ';
		}else{
			$values['facebook_url'] = $_POST['facebook_url'];
			$format[] = "%s";
		}
	}else{
		$values['facebook_url'] = '';
		$format[] = "%s";
	}
	
	if(isset($_POST['twitter_url']) AND $_POST['twitter_url'] != ''){
		$layout->AddContentById('twitter_url', $_POST['twitter_url']);
		
		if(!filter_var($_POST['twitter_url'], FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED)){
			$errors = true;
			$error_msg .=  '{{ST:invalid_twitter_url}} ';
		}else{
			$values['twitter_url'] = $_POST['twitter_url'];
			$format[] = "%s";
		}
	}else{
		$values['twitter_url'] = '';
		$format[] = "%s";
	}
	
	$profile_photo = '';
	if(isset($_FILES["profile_photo"]["name"]) AND $_FILES["profile_photo"]["name"] != ''){
		if($_FILES["profile_photo"]["error"] > 0){
			$errors = true;
			$error_msg .= '{{ST:profile_photo}}: ' . UploadError($_FILES["profile_photo"]["error"]) . '. ';
		}else{
			if(!is_image_file($_FILES["profile_photo"]["name"])){
				$errors = true;
				$error_msg .= '{{ST:profile_photo_needs_to_be_image}} ';
			}else{
				$profile_photo = 'uploads/' . set_filename('uploads/', $_FILES["profile_photo"]["name"]);
				$values['photo'] = $profile_photo;
				$format[] = "%s";
			}
		}
	}
	
	if(!$errors){
		$db->update(TABLES_PREFIX . "users", $values, array('id'=>$id), $format);
		
		if(isset($_FILES["profile_photo"]["name"]) AND $_FILES["profile_photo"]["name"] != ''){
			move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $profile_photo);
			if($admin->photo){
				$file = $admin->photo;
				$exists = file_exists($file);
				if($exists){
					unlink($file);
				}
			}
			
			
			
			$layout->AddContentById('current_profile', '<p><a href="'.FORUM_URL . $profile_photo.'" target="_blank">{{ST:view_img}}</a> | <a onclick="return confirm(\'{{ST:are_you_sure}}\');" title="{{ST:delete}}" href="'.FORUM_URL.'profile.php?delete_photo=1">{{ST:delete}}</a><p>');
		}else{
			if($admin->photo){
				$layout->AddContentById('current_profile', '<p><a href="'.FORUM_URL . $admin->photo.'" target="_blank">{{ST:view_img}}</a> | <a onclick="return confirm(\'{{ST:are_you_sure}}\');" title="{{ST:delete}}" href="'.FORUM_URL.'profile.php?delete_photo=1">{{ST:delete}}</a><p>');
			}
		}
		
		$layout->AddContentById('alert', $layout->GetContent('alert'));
		$layout->AddContentById('alert_nature', ' alert-success');
		$layout->AddContentById('alert_heading', '{{ST:success}}!');
		$layout->AddContentById('alert_message', '{{ST:the_item_has_been_saved}}');
	}else{
		$layout->AddContentById('alert', $layout->GetContent('alert'));
		$layout->AddContentById('alert_nature', ' alert-error');
		$layout->AddContentById('alert_heading', '{{ST:error}}!');
		$layout->AddContentById('alert_message', $error_msg);
		
		if($admin->photo){
			$layout->AddContentById('current_profile', '<p><a href="'.FORUM_URL . $admin->photo.'" target="_blank">{{ST:view_img}}</a> | <a onclick="return confirm(\'{{ST:are_you_sure}}\');" title="{{ST:delete}}" href="'.FORUM_URL.'profile.php?delete_photo=1">{{ST:delete}}</a><p>');
		}
	}
}else{
	
	$layout->AddContentById('username', $admin->username);
	$layout->AddContentById('email', $admin->email);
	$layout->AddContentById('bio', $admin->bio);
	$layout->AddContentById('signature', $admin->signature);
	if($admin->photo){
		$layout->AddContentById('current_profile', '<p><a href="'.FORUM_URL . $admin->photo.'" target="_blank">{{ST:view_img}}</a> | <a onclick="return confirm(\'{{ST:are_you_sure}}\');" title="{{ST:delete}}" href="'.FORUM_URL.'profile.php?delete_photo=1">{{ST:delete}}</a><p>');
	}
	$layout->AddContentById('facebook_url', $admin->facebook_url);
	$layout->AddContentById('twitter_url', $admin->twitter_url);
	$layout->AddContentById('website_url', $admin->website_url);
}

$layout->RenderViewAndExit();
