<?php

require 'includes.php';

if(!Users_IsUserLoggedIn() OR !Users_IsUserAdmin(Users_CurrentUserId())){
	Leave(FORUM_URL);
}

$layout = GetPage('settings', '{{ST:settings}}');

$layout->AddContentById('breadcrumbs', ' <li><a href="'.FORUM_URL.'"><i class="fa fa-home"></i> {{ST:home}}</a></li> <a class="active-trail active" href="#">{{ST:settings}}</a>');


if(isset($_POST['submit'])){
	$errors = false;
	$error_msg = '';
	
	if(isset($_POST['site_name']) AND $_POST['site_name'] != ''){
		set_option('site_name', $_POST['site_name']);
		$layout->AddContentById('site_name', $_POST['site_name']);
	}else{
		$errors = true;
		$error_msg .= '{{ST:site_name_required}} ';
	}
	
	if(isset($_POST['site_meta_description']) AND $_POST['site_meta_description'] != ''){
		set_option('site_meta_desc', $_POST['site_meta_description']);
		$layout->AddContentById('site_meta_description', $_POST['site_meta_description']);
	}else{
		set_option('site_meta_desc');
	}
	
	if(isset($_POST['use_sef_urls']) AND $_POST['use_sef_urls'] != ''){
		set_option('use_sef_urls', 'y');
		$layout->AddContentById('use_sef_urls_state', 'checked="checked"');
	}else{
		set_option('use_sef_urls','n');
	}
	
	if(isset($_POST['classic_home_page']) AND $_POST['classic_home_page'] != ''){
		set_option('classic_home_page', 'y');
		$layout->AddContentById('classic_home_page_state', 'checked="checked"');
	}else{
		set_option('classic_home_page','n');
	}
	
	if(isset($_POST['time_zone']) AND $_POST['time_zone'] != ''){
		set_option('time_zone', $_POST['time_zone']);
		$layout->AddContentById('time_zone', $_POST['time_zone']);
	}else{
		$errors = true;
		$error_msg .= '{{ST:time_zone_required}} ';
	}
	
	if(isset($_POST['rows_per_page']) AND $_POST['rows_per_page'] != ''){
		set_option('rows_per_page', $_POST['rows_per_page']);
		$layout->AddContentById('rows_per_page', $_POST['rows_per_page']);
	}else{
		$errors = true;
		$error_msg .= '{{ST:rows_per_page_required}} ';
	}
	
	if(isset($_POST['base_path']) AND $_POST['base_path'] != ''){
		set_option('base_path', $_POST['base_path']);
		$layout->AddContentById('base_path', $_POST['base_path']);
	}else{
		$errors = true;
		$error_msg .= '{{ST:base_path_required}} ';
	}
	
	if(isset($_POST['forum_url']) AND $_POST['forum_url'] != ''){
		set_option('forum_url', $_POST['forum_url']);
		$layout->AddContentById('forum_url', $_POST['forum_url']);
	}else{
		$errors = true;
		$error_msg .= '{{ST:forum_url_required}} ';
	}
	
	if(isset($_POST['site_url']) AND $_POST['site_url'] != ''){
		set_option('site_url', $_POST['site_url']);
		$layout->AddContentById('site_url_option', $_POST['site_url']);
	}else{
		set_option('site_url');
	}
	
	if(isset($_POST['recaptcha_public_key']) AND $_POST['recaptcha_public_key'] != ''){
		set_option('recaptcha_public_key', $_POST['recaptcha_public_key']);
		$layout->AddContentById('recaptcha_public_key', $_POST['recaptcha_public_key']);
	}else{
		set_option('recaptcha_public_key');
	}
	
	if(isset($_POST['recaptcha_private_key']) AND $_POST['recaptcha_private_key'] != ''){
		set_option('recaptcha_private_key', $_POST['recaptcha_private_key']);
		$layout->AddContentById('recaptcha_private_key', $_POST['recaptcha_private_key']);
	}else{
		set_option('recaptcha_private_key');
	}
	
	if(isset($_POST['facebook_app_id']) AND $_POST['facebook_app_id'] != ''){
		set_option('facebook_app_id', $_POST['facebook_app_id']);
		$layout->AddContentById('facebook_app_id', $_POST['facebook_app_id']);
	}else{
		set_option('facebook_app_id');
	}
	
	if(isset($_POST['facebook_app_secret']) AND $_POST['facebook_app_secret'] != ''){
		set_option('facebook_app_secret', $_POST['facebook_app_secret']);
		$layout->AddContentById('facebook_app_secret', $_POST['facebook_app_secret']);
	}else{
		set_option('facebook_app_secret');
	}
	
	if(isset($_POST['theme']) AND $_POST['theme'] != ''){
		set_option('theme', $_POST['theme']);
		$layout->AddContentById('theme_selected_' .$_POST['theme'], 'selected');
	}else{
		set_option('theme','default');
	}
	
	if(isset($_POST['admin_approve_posts']) AND $_POST['admin_approve_posts'] != ''){
		set_option('admin_approve_posts', 'y');
		$layout->AddContentById('admin_approve_posts_state', 'checked="checked"');
	}else{
		set_option('admin_approve_posts','n');
	}
	
	if(isset($_POST['email_admin_on_flag']) AND $_POST['email_admin_on_flag'] != ''){
		set_option('email_admin_on_flag', 'y');
		$layout->AddContentById('email_admin_on_flag_state', 'checked="checked"');
	}else{
		set_option('email_admin_on_flag','n');
	}
	
	if(isset($_POST['email_admin_on_signup']) AND $_POST['email_admin_on_signup'] != ''){
		set_option('email_admin_on_signup', 'y');
		$layout->AddContentById('email_admin_on_signup_state', 'checked="checked"');
	}else{
		set_option('email_admin_on_signup','n');
	}

	if(isset($_POST['make_forum_private']) AND $_POST['make_forum_private'] != ''){
		set_option('make_forum_private', 'y');
		$layout->AddContentById('make_forum_private_state', 'checked="checked"');
	}else{
		set_option('make_forum_private','n');
	}
	
	if(isset($_POST['email_admin_on_start_thread']) AND $_POST['email_admin_on_start_thread'] != ''){
		set_option('email_admin_on_start_thread', 'y');
		$layout->AddContentById('email_admin_on_start_thread_state', 'checked="checked"');
	}else{
		set_option('email_admin_on_start_thread','n');
	}
	
	
	if(isset($_POST['approve_new_user']) AND $_POST['approve_new_user'] != ''){
		set_option('approve_new_user', 'y');
		$layout->AddContentById('approve_new_user_state', 'checked="checked"');
	}else{
		set_option('approve_new_user','n');
	}
	
	if(isset($_POST['bad_words']) AND $_POST['bad_words'] != ''){
		set_option('bad_words', $_POST['bad_words']);
		$layout->AddContentById('bad_words', $_POST['bad_words']);
	}else{
		set_option('bad_words','');
	}
	
	if(isset($_POST['multi_lingual']) AND $_POST['multi_lingual'] != ''){
		set_option('multi_lingual', 'y');
		$layout->AddContentById('multi_lingual_state', 'checked="checked"');
		
		
		if(isset($_POST['primary_language']) AND $_POST['primary_language'] != ''){
			set_option('primary_language', $_POST['primary_language']);
			$layout->AddContentById('primary_language', $_POST['primary_language']);
		}else{
			$errors = true;
			$error_msg .= '{{ST:primary_language_required}} ';
		}
		
		if(isset($_POST['other_languages']) AND $_POST['other_languages'] != ''){
			set_option('other_languages', $_POST['other_languages']);
			$layout->AddContentById('other_languages', $_POST['other_languages']);
		}else{
			$errors = true;
			$error_msg .= '{{ST:other_languages_required}} ';
		}

	
		
		
	}else{
		set_option('multi_lingual','n');
		set_option('primary_language');
		set_option('other_languages');
	}
	
	if(!$errors){
		$layout->AddContentById('alert', $layout->GetContent('alert'));
		$layout->AddContentById('alert_nature', ' alert-success');
		$layout->AddContentById('alert_heading', '{{ST:success}}!');
		$layout->AddContentById('alert_message', '{{ST:settings_updated}}');
	}else{
		$layout->AddContentById('alert', $layout->GetContent('alert'));
		$layout->AddContentById('alert_nature', ' alert-error');
		$layout->AddContentById('alert_heading', '{{ST:error}}!');
		$layout->AddContentById('alert_message', $error_msg);
	}
}else{
	$layout->AddContentById('site_name', get_option('site_name'));
	$layout->AddContentById('site_meta_description', get_option('site_meta_desc'));
	$layout->AddContentById('time_zone', get_option('time_zone'));
	$layout->AddContentById('rows_per_page', get_option('rows_per_page'));
	$layout->AddContentById('base_path', get_option('base_path'));
	$layout->AddContentById('forum_url', get_option('forum_url'));
	$layout->AddContentById('site_url_option', get_option('site_url'));
	
	
	if($use_sef_urls = get_option('use_sef_urls')){
		if($use_sef_urls == 'y'){
			$layout->AddContentById('use_sef_urls_state', 'checked="checked"');
		}
	}
	
	if($classic_home_page = get_option('classic_home_page')){
		if($classic_home_page == 'y'){
			$layout->AddContentById('classic_home_page_state', 'checked="checked"');
		}
	}
	
	if($theme = get_option('theme')){
		$layout->AddContentById('theme_selected_' .$theme, 'selected');
	}
	
	$layout->AddContentById('recaptcha_public_key', get_option('recaptcha_public_key'));
	$layout->AddContentById('recaptcha_private_key', get_option('recaptcha_private_key'));
	$layout->AddContentById('facebook_app_id', get_option('facebook_app_id'));
	$layout->AddContentById('facebook_app_secret', get_option('facebook_app_secret'));
	
	if($admin_approve_posts = get_option('admin_approve_posts')){
		if($admin_approve_posts == 'y'){
			$layout->AddContentById('admin_approve_posts_state', 'checked="checked"');
		}
	}
	
	if($email_admin_on_flag = get_option('email_admin_on_flag')){
		if($email_admin_on_flag == 'y'){
			$layout->AddContentById('email_admin_on_flag_state', 'checked="checked"');
		}
	}
	
	if($email_admin_on_signup = get_option('email_admin_on_signup')){
		if($email_admin_on_signup == 'y'){
			$layout->AddContentById('email_admin_on_signup_state', 'checked="checked"');
		}
	}
	
	if($email_admin_on_start_thread = get_option('email_admin_on_start_thread')){
		if($email_admin_on_start_thread == 'y'){
			$layout->AddContentById('email_admin_on_start_thread_state', 'checked="checked"');
		}
	}

	if($make_forum_private = get_option('make_forum_private')){
		if($make_forum_private == 'y'){
			$layout->AddContentById('make_forum_private_state', 'checked="checked"');
		}
	}
	
	if($multi_lingual= get_option('multi_lingual')){
		if($multi_lingual == 'y'){
			$layout->AddContentById('multi_lingual_state', 'checked="checked"');
		}
	}
	
	$layout->AddContentById('primary_language', get_option('primary_language'));
	$layout->AddContentById('other_languages', get_option('other_languages'));
	
	if($approve_new_user = get_option('approve_new_user')){
		if($approve_new_user == 'y'){
			$layout->AddContentById('approve_new_user_state', 'checked="checked"');
		}
	}
	
	if($bad_words = get_option('bad_words')){
		$layout->AddContentById('bad_words', $bad_words);
	}
}

$layout->RenderViewAndExit();
