<?php

require 'includes.php';
require_once('php/recaptchalib.php');

if(defined('PRIVATE_FORUM') AND PRIVATE_FORUM == true AND !Users_IsUserLoggedIn()){
	Leave(Users_SignInPageUrl());
}

$type = 'question';
if(isset($_GET['type']) AND $_GET['type'] != ''){
	$type = $_GET['type'];
}else{
	Leave(FORUM_URL);
}

if($type == 'question'){
	$title = '{{ST:start_a_thread}}';
}elseif($type == 'reply'){
	$title = '{{ST:reply_to_post}}';
	if(isset($_GET['id'])){
		$id = intval($_GET['id']);
	}else{
		Leave(FORUM_URL);
	}
}elseif($type == 'edit'){
	$title = '{{ST:edit_a_post}}';
	if(isset($_GET['page'])){
		$page = '&page='.intval($_GET['page']);
	}else{
		$page = '&lastpage=1';
	}
	if(isset($_GET['id'])){
		$id = intval($_GET['id']);
	}else{
		Leave(FORUM_URL);
	}
	
	$post = $db->get_row("SELECT * FROM " . TABLES_PREFIX . "posts WHERE id = $id ORDER BY id DESC LIMIT 0,1");
	if(!$post){
		Leave("index.php");
	}elseif(!Users_IsUserAdminOrModerator(Users_CurrentUserId())){
		if(Users_CurrentUserId() != intval($post->user_id)){
			Leave(FORUM_URL);
		}
	}
}

$layout = GetPage('post', $title);

$layout->AddContentById('type', $type);

if($type == 'question'){
	$categoryQuery = get_nested_categories_post();
	$categories = '';
	if($categoryQuery){
		foreach($categoryQuery as $u){
			$categories .= '<option {{ID:selected_category_' . $u->id . '}} value="' . $u->id . '">' . $u->name . '</option>';
		}
		$layout->AddContentById('categories', $categories);
	}
	//$layout->AddContentById('breadcrumbs', ' <li><a href="'.FORUM_URL.'">{{ST:home}}</a> <span class="divider">/</span> </li><li class="active">{{ST:start_a_thread}}</li>');
	$layout->AddContentById('breadcrumbs', ' <li><a href="'.FORUM_URL.'"><i class="fa fa-home"></i> {{ST:home}}</a></li> <li><a class="active-trail active" href="#">{{ST:start_a_thread}}</a></li>');
}elseif($type == 'reply'){
	$layout->AddContentById('show_title', 'style="display: none;"');
	$layout->AddContentById('show_categories', 'style="display: none;"');
	$layout->AddContentById('id_query', '&id='.$id);
	
	//$layout->AddContentById('breadcrumbs', ' <li><a href="'.FORUM_URL.'">{{ST:home}}</a> <span class="divider">/</span> </li> <li><a href="'.FORUM_URL.'thread.php?id='.$id.'">{{ST:thread}}</a> <span class="divider">/</span> </li><li class="active">{{ST:reply_to_post}}</li>');
	$layout->AddContentById('breadcrumbs', ' <li><a href="'.FORUM_URL.'"><i class="fa fa-home"></i> {{ST:home}}</a></li> <li><a href="'.FORUM_URL.'thread.php?id='.$id.'">{{ST:thread}}</a></li> <li><a class="active-trail active" href="#">{{ST:reply_to_post}}</a></li>');

}elseif($type == 'edit'){
	if($post->is_question != 'y'){
		$layout->AddContentById('show_title', 'style="display: none;"');
		$layout->AddContentById('show_categories', 'style="display: none;"');
	}else{
		$categoryQuery = get_nested_categories_post();
		$categories = '';
		if($categoryQuery){
			foreach($categoryQuery as $u){
				$categories .= '<option {{ID:selected_category_' . $u->id . '}} value="' . $u->id . '">' . $u->name . '</option>';
			}
			$layout->AddContentById('categories', $categories);
		}
	}
	$layout->AddContentById('show_subscribe', 'style="display: none;"');
	$layout->AddContentById('id_query', '&id='.$id.''.$page);
	
	$layout->AddContentById('breadcrumbs', ' <li><a href="'.FORUM_URL.'"><i class="fa fa-home"></i> {{ST:home}}</a></li> <li><a class="active-trail active" href="#">{{ST:edit_a_post}}</a></li>');
	//$layout->AddContentById('breadcrumbs', ' <li><a href="'.FORUM_URL.'">{{ST:home}}</a> <span class="divider">/</span> </li><li class="active">{{ST:edit_a_post}}</li>');
}

if(isset($_GET['delete_file']) AND intval($_GET['delete_file']) != 0){
	$file_to_delete = '';
	$files = unserialize($post->photos);
	$new_files = array();
	if(count($files) > 0){
		$files_count = 1;
		foreach($files as $f){
			if(intval($_GET['delete_file']) == $files_count){
				$file_to_delete = $f;
			}else{
				$new_files[] = $f;
			}
			$files_count++;
		}
		
		if($file_to_delete){
			$file = 'uploads/' . $file_to_delete;
			$exists = is_file($file);
			if($exists){
				unlink($file);
				$db->update(TABLES_PREFIX . "posts", array('photos'=>serialize($new_files)), array('id'=>$id), array("%s"));
				
				$post = $db->get_row("SELECT * FROM " . TABLES_PREFIX . "posts WHERE id = $id ORDER BY id DESC LIMIT 0,1");
			
				$layout->AddContentById('alert', $layout->GetContent('alert'));
				$layout->AddContentById('alert_nature', ' alert-success');
				$layout->AddContentById('alert_heading', '{{ST:success}}!');
				$layout->AddContentById('alert_message', '{{ST:file_has_been_deleted}}');
			}else{
				$layout->AddContentById('alert', $layout->GetContent('alert'));
				$layout->AddContentById('alert_nature', ' alert-error');
				$layout->AddContentById('alert_heading', '{{ST:error}}!');
				$layout->AddContentById('alert_message', '{{ST:file_does_not_exist}}');
			}
		}else{
			$layout->AddContentById('alert', $layout->GetContent('alert'));
			$layout->AddContentById('alert_nature', ' alert-error');
			$layout->AddContentById('alert_heading', '{{ST:error}}!');
			$layout->AddContentById('alert_message', '{{ST:file_does_not_exist}}');
		}
	}
}

if(isset($_POST['submit'])){
	
	$errors = false;
	$values = array();
	$format = array();
	$error_msg = '';
	
	if(RECAPTCHA_PUBLIC_KEY AND RECAPTCHA_PRIVATE_KEY AND !Users_IsUserAdminOrModerator(Users_CurrentUserId())){
		$resp = recaptcha_check_answer(RECAPTCHA_PRIVATE_KEY, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);
		if(!$resp->is_valid){
			$errors = true;
			$error_msg .= ' {{ST:human_verification_failed}} ';
			$captcha_html = $layout->GetContent('recaptcha') . recaptcha_get_html(RECAPTCHA_PUBLIC_KEY, $resp->error);
			$layout->AddContentById('captcha_html', $captcha_html);
		}else{
			$captcha_html = $layout->GetContent('recaptcha') . recaptcha_get_html(RECAPTCHA_PUBLIC_KEY, null);
			$layout->AddContentById('captcha_html', $captcha_html);
		}
	}
	
	if($type == 'question'){
		if(isset($_POST['title']) AND $_POST['title'] != ''){
			$layout->AddContentById('title_post', $_POST['title']);
			$values['title'] = filterbadwords($_POST['title']);
			$format[] = "%s";
		}else{
			$errors = true;
			$error_msg .= '{{ST:title_required}} ';
		}
		
		if(isset($_POST['category']) AND $_POST['category'] != ''){
			$layout->AddContentById('selected_category_' .$_POST['category'], 'selected');
			$category_check = $db->get_row("SELECT * FROM " . TABLES_PREFIX . "categories WHERE id = " . intval($_POST['category']) . " ORDER BY id DESC LIMIT 0,1");
			
			if($category_check->locked != 'y'){
				$values['category_id'] = $_POST['category'];
				$format[] = "%d";
			}else{
				if(!Users_IsUserAdminOrModerator(Users_CurrentUserId())){
					$errors = true;
					$error_msg .= '{{ST:only_admin_can_post_there}} ';
				}else{
					$values['category_id'] = $_POST['category'];
					$format[] = "%d";
				}
			}
		}else{
			$errors = true;
			$error_msg .= '{{ST:category_required}} ';
		}

		
	}

	if($type == 'edit'){
		if(file_exists($_FILES['photos']['tmp_name'][0]))	{
			$upload = upload_helper("photos", true);
			
			if($upload["status"] == 0){
				$errors = true;
				$error_msg .= $upload["error"] . ' ';
			}else{
				$photos = array();
				if($post ->photos){
					$photos = unserialize($post ->photos);
				}
					
				$values["photos"] = serialize(array_merge($photos, $upload["names"]));
				$format[] = "%s";
			}
		}
	}else{
		if(file_exists($_FILES['photos']['tmp_name'][0]))	{
			$upload = upload_helper("photos", true);
			
			if($upload["status"] == 0){
				$errors = true;
				$error_msg .= $upload["error"] . ' ';
			}else{
				$values["photos"] = serialize($upload["names"]);
				$format[] = "%s";
			}
		}
	}
	
	if($type == 'edit' AND $post->is_question == 'y'){
		if(isset($_POST['title']) AND $_POST['title'] != ''){
			$layout->AddContentById('title_post', $_POST['title']);
			$values['title'] = filterbadwords($_POST['title']);
			$format[] = "%s";
		}else{
			$errors = true;
			$error_msg .= '{{ST:title_required}} ';
		}
		
		if(isset($_POST['category']) AND $_POST['category'] != ''){
			$layout->AddContentById('selected_category_' .$_POST['category'], 'selected');
			$category_check = $db->get_row("SELECT * FROM " . TABLES_PREFIX . "categories WHERE id = " . intval($_POST['category']) . " ORDER BY id DESC LIMIT 0,1");
			
			if($category_check->locked != 'y'){
				$values['category_id'] = $_POST['category'];
				$format[] = "%d";
			}else{
				if(!Users_IsUserAdminOrModerator(Users_CurrentUserId())){
					$errors = true;
					$error_msg .= '{{ST:only_admin_can_post_there}} ';
				}else{
					$values['category_id'] = $_POST['category'];
					$format[] = "%d";
				}
			}
		}else{
			$errors = true;
			$error_msg .= '{{ST:category_required}} ';
		}
	}
	
	if($type == 'reply'){
		if($db->get_var("SELECT locked FROM " . TABLES_PREFIX . "posts WHERE id = $id") == 'y'){
			$errors = true;
			$error_msg .= '{{ST:thread_is_locked}} ';
		}
	}
	
	if(isset($_POST['body']) AND $_POST['body'] != '' AND trim(strip_tags($_POST['body'])) != ''){
		$layout->AddContentById('body', $_POST['body']);
		$values['body'] = filterbadwords($_POST['body']);
		$format[] = "%s";
	}else{
		$errors = true;
		$error_msg .= '{{ST:body_required}} ';
	}
	
	if(isset($_POST['email_notification'])){
		$layout->AddContentById('email_notification_state', 'checked="checked"');
	}
	
	if(isset($_POST['quote'])){
		$layout->AddContentById('quote', $_POST['quote']);
	}
	
	if(!Users_IsUserLoggedIn()){
			$errors = true;
			$error_msg .= '{{ST:you_need_to_signin_first}} ';
	}elseif(!Users_CanCurrentUserPost()){
			$errors = true;
			$error_msg .= '{{ST:you_are_not_able_to_post_contact_admin}} ';
	}
	
	if(!$errors){
		if(defined('ADMIN_APPROVE_POSTS') AND ADMIN_APPROVE_POSTS == true AND !Users_IsUserAdminOrModerator(Users_CurrentUserId())){
			$values['approved'] = 'n';
			$format[] = "%s";
		}else{
			$values['approved'] = 'y';
			$format[] = "%s";
		}	
			
		if($type == 'question' OR $type == 'reply'){
			if($type == 'question'){
				$values['is_question'] = 'y';
				$format[] = "%s";
			}else{
				$values['is_question'] = 'n';
				$format[] = "%s";
				$values['parent_id'] = $id;
				$format[] = "%d";
			}
			$values['flagged'] = 'n';
			$format[] = "%s";
			$values['likes'] = 0;
			$format[] = "%d";
			$values['date'] = date('Y-m-d H:i:s');
			$format[] = "%s";
			$values['user_id'] = Users_CurrentUserId();
			$format[] = "%d";
			
			
			if($type == 'reply'){
				$values['category_id'] = $db->get_var("SELECT category_id FROM " . TABLES_PREFIX . "posts WHERE id = " . $id);
				$format[] = "%d";
				
				if(isset($_POST['quote']) AND $_POST['quote'] != ''){
					$post_quote = $db->get_row("SELECT * FROM " . TABLES_PREFIX . "posts WHERE id = ".intval($_POST['quote'])." ORDER BY id DESC LIMIT 0,1");
					$author = $db->get_var("SELECT username FROM " . TABLES_PREFIX . "users WHERE id = " . intval($post_quote->user_id));
		
					$values['quote'] = '<br/><blockquote><i>'.$author.' '.$strings->Get('said').':</i><br/>'.Clean($post_quote->body).'</blockquote><hr/><br/>';
					$format[] = "%s";
				}
			}
			
			if($db->insert(TABLES_PREFIX . "posts", $values, $format)){
				if(defined('ADMIN_APPROVE_POSTS') AND ADMIN_APPROVE_POSTS == true AND !Users_IsUserAdminOrModerator(Users_CurrentUserId())){
					$to = WEBMASTER_EMAIL;
					$subject = "[" . $_SERVER['SERVER_NAME'] . "]".SITE_NAME.": " . $strings->Get('approve_post_subject');
					$message = $strings->Get('approve_post_body') . '

' . FORUM_URL;
					$from = WEBMASTER_EMAIL;
					$headers = "From: $from";
					ini_set("sendmail_from", $from);	
					mail($to,$subject,$message,$headers);
				}else{
					if($type == 'question'){
						if(defined('EMAIL_ADMIN_ON_START_THREAD') AND EMAIL_ADMIN_ON_START_THREAD == true){
							$to = WEBMASTER_EMAIL;
							$subject = "[" . $_SERVER['SERVER_NAME'] . "]".SITE_NAME.": " . $strings->Get('new_thread_subject');
							$message = $strings->Get('new_thread_body') . '

' . FORUM_URL;
							$from = WEBMASTER_EMAIL;
							$headers = "From: $from";
							ini_set("sendmail_from", $from);	
							mail($to,$subject,$message,$headers);
						}
					}
				}
				
				
				$new_record = $db->insert_id;
				if(isset($_POST['email_notification'])){
					$db->insert(TABLES_PREFIX . "posts_following", array('post_id'=>$id,'user_id'=>Users_CurrentUserId()), array("%d","%d"));
				}
				
				if($type == 'reply'){
					NotifySubscribers($id, $new_record);
				}
				
				if(defined('ADMIN_APPROVE_POSTS') AND ADMIN_APPROVE_POSTS == true AND !Users_IsUserAdminOrModerator(Users_CurrentUserId())){
					$layout->AddContentById('alert', $layout->GetContent('alert'));
					$layout->AddContentById('alert_nature', ' alert-success');
					$layout->AddContentById('alert_heading', '{{ST:success}}!');
					$layout->AddContentById('alert_message', '{{ST:your_contribution_has_been_saved}}');
				}else{
					if($type == 'question'){
						Leave(FORUM_URL.'thread.php?id=' . $new_record);
					}else{
						Leave(FORUM_URL.'thread.php?id=' . $id . '&lastpage=1');
					}
				}
			}else{
				$layout->AddContentById('alert', $layout->GetContent('alert'));
				$layout->AddContentById('alert_nature', ' alert-error');
				$layout->AddContentById('alert_heading', '{{ST:error}}!');
				$layout->AddContentById('alert_message', '{{ST:unknow_error_try_again}}');
			}
		}elseif($type == 'edit'){
			if($post->body != $values['body']){
				$values['last_edited_on'] = date('Y-m-d H:i:s');
				$format[] = "%s";
				$values['last_edited_by'] = Users_CurrentUserId();
				$format[] = "%d";
			}
			
			$db->update(TABLES_PREFIX . "posts", $values, array('id'=>$id), $format);
			
			if(defined('ADMIN_APPROVE_POSTS') AND ADMIN_APPROVE_POSTS == true AND !Users_IsUserAdminOrModerator(Users_CurrentUserId())){
					$to = WEBMASTER_EMAIL;
					$subject = "[" . $_SERVER['SERVER_NAME'] . "]".SITE_NAME.": " . $strings->Get('approve_post_subject');
					$message = $strings->Get('approve_post_body') . '

' . FORUM_URL;
					$from = WEBMASTER_EMAIL;
					$headers = "From: $from";
					ini_set("sendmail_from", $from);	
					mail($to,$subject,$message,$headers);
			}
			
			if(defined('ADMIN_APPROVE_POSTS') AND ADMIN_APPROVE_POSTS == true AND !Users_IsUserAdminOrModerator(Users_CurrentUserId())){
				$layout->AddContentById('alert', $layout->GetContent('alert'));
				$layout->AddContentById('alert_nature', ' alert-success');
				$layout->AddContentById('alert_heading', '{{ST:success}}!');
				$layout->AddContentById('alert_message', '{{ST:your_contribution_has_been_saved}}');
			}else{
				if($post->is_question == 'y'){
					Leave(FORUM_URL.'thread.php?id=' . $id);
				}else{
					Leave(FORUM_URL.'thread.php?id=' . $post->parent_id . $page);
				}	
			}
		}
	}else{
		$layout->AddContentById('alert', $layout->GetContent('alert'));
		$layout->AddContentById('alert_nature', ' alert-error');
		$layout->AddContentById('alert_heading', '{{ST:error}}!');
		$layout->AddContentById('alert_message', $error_msg);
	}
}else{
	if($type == 'edit'){
		$layout->AddContentById('body', $post->body);
		if($post->is_question == 'y'){
			$layout->AddContentById('title_post', $post->title);
			$layout->AddContentById('selected_category_' .$post->category_id, 'selected');
		}

		$files = unserialize($post->photos);
			if(count($files) > 0 AND is_array($files)){
				$files_lists = '<br/><ol>';
				$files_count = 1;
				foreach($files as $f){
					if(is_image_file($f)){
						$files_lists .= '<li><a target="_blank" href="{{ID:base_url}}uploads/' . $f.'">{{ST:attachment}} '.$files_count.'</a> - <a onclick="return confirm(\'{{ST:are_you_sure}}\');" href="{{ID:base_url}}post.php?id='. $id .'&type=edit&delete_file='.$files_count.'">Delete</a></li>';
					}else{
						$files_lists .= '<li><a target="_blank" href="{{ID:base_url}}uploads/' . $f.'">{{ST:attachment}} '.$files_count.'</a> - <a onclick="return confirm(\'{{ST:are_you_sure}}\');" href="{{ID:base_url}}post.php?id='. $id .'&type=edit&&delete_file='.$files_count.'">Delete</a></li>';
					}
					$files_count++;
				}
				$files_lists .= '</ol>';
				$layout->AddContentById('files', $files_lists);
			}

	}elseif($type == 'reply' AND isset($_GET['quote']) AND intval($_GET['quote']) != 0){
		$layout->AddContentById('quote', $_GET['quote']);
	}
	if(!Users_IsUserLoggedIn()){
			$layout->AddContentById('alert', $layout->GetContent('alert'));
			$layout->AddContentById('alert_nature', ' alert-error');
			$layout->AddContentById('alert_heading', '{{ST:info}}:');
			$layout->AddContentById('alert_message', '{{ST:you_need_to_signin_first}}');
	}elseif(!Users_CanCurrentUserPost()){
			$layout->AddContentById('alert', $layout->GetContent('alert'));
			$layout->AddContentById('alert_nature', ' alert-error');
			$layout->AddContentById('alert_heading', '{{ST:info}}:');
			$layout->AddContentById('alert_message', '{{ST:you_are_not_able_to_post_contact_admin}}');
	}
}

if(RECAPTCHA_PUBLIC_KEY AND RECAPTCHA_PRIVATE_KEY AND !Users_IsUserAdminOrModerator(Users_CurrentUserId())){
	$captcha_html = $layout->GetContent('recaptcha') . recaptcha_get_html(RECAPTCHA_PUBLIC_KEY, null);
	$layout->AddContentById('captcha_html', $captcha_html);
}

if(!Users_IsUserLoggedIn()){
	$layout->AddContentById('the_form', 'style="display: none;"');
}

$layout->RenderViewAndExit();
