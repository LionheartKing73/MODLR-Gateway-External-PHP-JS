<?php

require 'includes.php';

if(defined('PRIVATE_FORUM') AND PRIVATE_FORUM == true AND !Users_IsUserLoggedIn()){
	Leave(Users_SignInPageUrl());
}

if(isset($_GET['id'])){
	$id = intval($_GET['id']);
}else{
	Leave(FORUM_URL);
}

$question = $db->get_row("SELECT * FROM " . TABLES_PREFIX . "posts WHERE id = $id ORDER BY id DESC LIMIT 0,1");

if(defined('SITE_NAME') AND SITE_NAME != ''){
	$layout = GetPage('thread', $question->title . ' | ' . SITE_NAME);
	$layout->AddContentById('meta_title', $question->title . ' | ' . SITE_NAME);
}else{
	$layout = GetPage('thread', $question->title);
	$layout->AddContentById('meta_title', $question->title);
}
$layout->AddContentById('meta_desc', TrimText($question->body, 160));




$bread_crumb_html = '';
	
	$bread_search = true;
	$current_level = intval($question->category_id);
	while($bread_search == true){
		$bc_category = $db->get_row("SELECT * FROM " . TABLES_PREFIX . "categories WHERE id = $current_level ORDER BY id DESC LIMIT 0,1");
		if(defined('SEO_HUMAN_FRIENDLY_URLS') AND SEO_HUMAN_FRIENDLY_URLS == true){
			$bread_crumb_html = ' <li><a href="{{ID:base_url}}forum/'.UrlText($bc_category->name).'/'.$bc_category->id.'/">'.$bc_category->name.'</a>  <span class="divider">/</span> </li>' . $bread_crumb_html;
		}else{
			$bread_crumb_html = ' <li><a href="{{ID:base_url}}index.php?id='.$bc_category->id.'">'.$bc_category->name.'</a>  <span class="divider">/</span> </li>' . $bread_crumb_html;
		}
		if(intval($bc_category->parent) == 0){
			$bread_search = false;
		}else{
			$current_level = intval($bc_category->parent);
		}
	}
	
	//$layout->AddContentById('breadcrumbs', ' <li><a href="'.FORUM_URL.'">{{ST:home}}</a> <span class="divider">/</span> </li>'. $bread_crumb_html . '<li class="active">'.$question->title.'</li>');
	$layout->AddContentById('breadcrumbs', ' <li><a href="'.FORUM_URL.'"><i class="fa fa-home"></i> {{ST:home}}</a></li> '. $bread_crumb_html . ' <li><a class="active-trail active" href="#">'.$question->title.'</a></li>');
	


if(isset($_GET['lock']) AND intval($_GET['lock']) == 1){
	if(Users_IsUserAdminOrModerator(Users_CurrentUserId())){
		$db->update(TABLES_PREFIX . "posts", array('locked'=>'y'), array('id'=>$id), array("%s"));
		$layout->AddContentById('alert', $layout->GetContent('alert'));
		$layout->AddContentById('alert_nature', ' alert-success');
		$layout->AddContentById('alert_heading', '{{ST:success}}!');
		$layout->AddContentById('alert_message', '{{ST:the_thread_has_been_locked}}');
		$question = $db->get_row("SELECT * FROM " . TABLES_PREFIX . "posts WHERE id = $id ORDER BY id DESC LIMIT 0,1");
	}
}

if(isset($_GET['unlock']) AND intval($_GET['unlock']) == 1){
	if(Users_IsUserAdminOrModerator(Users_CurrentUserId())){
		$db->update(TABLES_PREFIX . "posts", array('locked'=>'n'), array('id'=>$id), array("%s"));
		$layout->AddContentById('alert', $layout->GetContent('alert'));
		$layout->AddContentById('alert_nature', ' alert-success');
		$layout->AddContentById('alert_heading', '{{ST:success}}!');
		$layout->AddContentById('alert_message', '{{ST:the_thread_has_been_unlocked}}');
		$question = $db->get_row("SELECT * FROM " . TABLES_PREFIX . "posts WHERE id = $id ORDER BY id DESC LIMIT 0,1");
	}
}

if(isset($_GET['unpin']) AND intval($_GET['unpin']) == 1){
	if(Users_IsUserAdminOrModerator(Users_CurrentUserId())){
		$db->update(TABLES_PREFIX . "posts", array('pinned'=>0), array('id'=>$id), array("%d"));
		$layout->AddContentById('alert', $layout->GetContent('alert'));
		$layout->AddContentById('alert_nature', ' alert-success');
		$layout->AddContentById('alert_heading', '{{ST:success}}!');
		$layout->AddContentById('alert_message', '{{ST:the_thread_has_been_unpin}}');
		$question = $db->get_row("SELECT * FROM " . TABLES_PREFIX . "posts WHERE id = $id ORDER BY id DESC LIMIT 0,1");
	}
}

if(isset($_GET['pin']) AND intval($_GET['pin']) == 1){
	if(Users_IsUserAdminOrModerator(Users_CurrentUserId())){
		$db->update(TABLES_PREFIX . "posts", array('pinned'=>1), array('id'=>$id), array("%d"));
		$layout->AddContentById('alert', $layout->GetContent('alert'));
		$layout->AddContentById('alert_nature', ' alert-success');
		$layout->AddContentById('alert_heading', '{{ST:success}}!');
		$layout->AddContentById('alert_message', '{{ST:the_thread_has_been_pin}}');
		$question = $db->get_row("SELECT * FROM " . TABLES_PREFIX . "posts WHERE id = $id ORDER BY id DESC LIMIT 0,1");
	}
}

if(isset($_GET['subscribe']) AND intval($_GET['subscribe']) == 1){
	if(Users_CurrentUserId()){
		$db->insert(TABLES_PREFIX . "posts_following", array('post_id'=>$id,'user_id'=>intval(Users_CurrentUserId())), array("%d","%d"));
		$layout->AddContentById('alert', $layout->GetContent('alert'));
		$layout->AddContentById('alert_nature', ' alert-success');
		$layout->AddContentById('alert_heading', '{{ST:success}}!');
		$layout->AddContentById('alert_message', '{{ST:you_have_subscribed_to_this_thread}}');
	}
}

if(isset($_GET['unsubscribe']) AND intval($_GET['unsubscribe']) == 1){
	if(Users_CurrentUserId()){
		$db->query("DELETE FROM " . TABLES_PREFIX . "posts_following WHERE post_id = " . $id . " AND user_id = " . intval(Users_CurrentUserId()));
		$layout->AddContentById('alert', $layout->GetContent('alert'));
		$layout->AddContentById('alert_nature', ' alert-success');
		$layout->AddContentById('alert_heading', '{{ST:success}}!');
		$layout->AddContentById('alert_message', '{{ST:you_have_unsubscribed_from_this_thread}}');
	}
}

if(isset($_GET['like']) AND intval($_GET['like']) == 1 AND isset($_GET['post'])){
	if(Users_CurrentUserId()){
		if(count($db->get_results("SELECT * FROM " . TABLES_PREFIX . "likes WHERE user_id = ".intval(Users_CurrentUserId())." AND post_id = ".intval($_GET['post'])."" )) > 0){
		
		}else{
			$db->insert(TABLES_PREFIX . "likes", array('post_id'=>intval($_GET['post']),'user_id'=>intval(Users_CurrentUserId())), array("%d","%d"));
			
			$the_post = $db->get_row("SELECT * FROM " . TABLES_PREFIX . "posts WHERE id = " .intval($_GET['post']) . " ORDER BY id DESC LIMIT 0,1");
			$db->update(TABLES_PREFIX . "posts", array('likes'=>(1+$the_post->likes)), array('id'=>intval($_GET['post'])), array("%d"));
			
			$layout->AddContentById('alert', $layout->GetContent('alert'));
			$layout->AddContentById('alert_nature', ' alert-success');
			$layout->AddContentById('alert_heading', '{{ST:success}}!');
			$layout->AddContentById('alert_message', '{{ST:you_have_liked_the_post}}');
		}
	}
}

if(isset($_GET['unlike']) AND intval($_GET['unlike']) == 1 AND isset($_GET['post'])){
	if(Users_CurrentUserId()){
		if(count($db->get_results("SELECT * FROM " . TABLES_PREFIX . "likes WHERE user_id = ".intval(Users_CurrentUserId())." AND post_id = ".intval($_GET['post'])."" )) > 0){
			$db->query("DELETE FROM " . TABLES_PREFIX . "likes WHERE post_id = " . intval($_GET['post']) . " AND user_id = " . intval(Users_CurrentUserId()));
			
			$the_post = $db->get_row("SELECT * FROM " . TABLES_PREFIX . "posts WHERE id = " .intval($_GET['post']) . " ORDER BY id DESC LIMIT 0,1");
			$db->update(TABLES_PREFIX . "posts", array('likes'=>($the_post->likes - 1)), array('id'=>intval($_GET['post'])), array("%d"));
			
			$layout->AddContentById('alert', $layout->GetContent('alert'));
			$layout->AddContentById('alert_nature', ' alert-success');
			$layout->AddContentById('alert_heading', '{{ST:success}}!');
			$layout->AddContentById('alert_message', '{{ST:you_have_unliked_the_post}}');
		}
	}
}

if(isset($_GET['delete']) AND intval($_GET['delete']) == 1 AND isset($_GET['post'])){
	if(Users_IsUserAdminOrModerator(Users_CurrentUserId())){
		$the_post = $db->get_row("SELECT * FROM " . TABLES_PREFIX . "posts WHERE id = " .intval($_GET['post']) . " ORDER BY id DESC LIMIT 0,1");
		
		if($the_post->photos != ''){
			$files = unserialize($the_post->photos);
			if(count($files) > 0 AND is_array($files)){
				foreach($files as $f){
					$file = 'uploads/' . $f;
					$exists = is_file($file);
					if($exists){
						unlink($file);
					}
				}
			}
		}
		
		if($the_post->is_question == 'y'){
			$all_children = $db->get_results("SELECT * FROM " . TABLES_PREFIX . "posts WHERE id = ".intval($_GET['post'])." OR parent_id = ".intval($_GET['post'])."" );
			if($all_children){
				foreach($all_children as $child){
					$db->query("DELETE FROM " . TABLES_PREFIX . "posts_following WHERE post_id = " . intval($child->id) );
					$db->query("DELETE FROM " . TABLES_PREFIX . "likes WHERE post_id = " . intval($child->id));
					$db->query("DELETE FROM " . TABLES_PREFIX . "flags WHERE post_id = " . intval($child->id));
					$db->query("DELETE FROM " . TABLES_PREFIX . "posts WHERE id = " . intval($child->id));	
				}
			}
			Leave(FORUM_URL.'?message=deleted');
		}else{
			$db->query("DELETE FROM " . TABLES_PREFIX . "posts_following WHERE post_id = " . intval($_GET['post']) );
			$db->query("DELETE FROM " . TABLES_PREFIX . "likes WHERE post_id = " . intval($_GET['post']));
			$db->query("DELETE FROM " . TABLES_PREFIX . "flags WHERE post_id = " . intval($_GET['post']));
			$db->query("DELETE FROM " . TABLES_PREFIX . "posts WHERE id = " . intval($_GET['post']));
			
			$layout->AddContentById('alert', $layout->GetContent('alert'));
			$layout->AddContentById('alert_nature', ' alert-success');
			$layout->AddContentById('alert_heading', '{{ST:success}}!');
			$layout->AddContentById('alert_message', '{{ST:the_post_has_been_deleted}}');
		}
	}
}

if(isset($_GET['flag']) AND intval($_GET['flag']) == 1 AND isset($_GET['post'])){
	if(Users_CurrentUserId()){
		$db->insert(TABLES_PREFIX . "flags", array('post_id'=>intval($_GET['post']),'user_id'=>intval(Users_CurrentUserId()),'date'=>date('Y-m-d H:i:s')), array("%d","%d","%s"));
			
		$db->update(TABLES_PREFIX . "posts", array('flagged'=>'y'), array('id'=>intval($_GET['post'])), array("%s"));
		
		if(defined('EMAIL_ADMIN_ON_FLAG') AND EMAIL_ADMIN_ON_FLAG == true){
			$to = WEBMASTER_EMAIL;
			$subject = "[" . $_SERVER['SERVER_NAME'] . "]Forum: " . $strings->Get('flagged_post_subject');
			$message = $strings->Get('flagged_post_body') . '

' . FORUM_URL;
			$from = WEBMASTER_EMAIL;
			$headers = "From: $from";
			ini_set("sendmail_from", $from);	
			mail($to,$subject,$message,$headers);
		}
			
		$layout->AddContentById('alert', $layout->GetContent('alert'));
		$layout->AddContentById('alert_nature', ' alert-success');
		$layout->AddContentById('alert_heading', '{{ST:success}}!');
		$layout->AddContentById('alert_message', '{{ST:the_post_has_been_flagged}}');
	}
}


if($question->locked == 'y'){
	$layout->AddContentById('post_title',  '({{ST:locked}})' . $question->title);
}else{
	$layout->AddContentById('post_title', $question->title);
}

if(!Users_IsUserLoggedIn()){
	$layout->AddContentById('post_alert', 'onclick="return SignInAlert();"');
}elseif($question->locked == 'y'){
	$layout->AddContentById('post_alert', 'onclick="return LockedAlert();"');
}

if(Users_IsUserAdminOrModerator(Users_CurrentUserId())){
	if($question->locked == 'y'){
		$layout->AddContentById('lock_state', 'style="display:none"');
	}else{
		$layout->AddContentById('unlock_state', 'style="display:none"');
	}
}else{
	$layout->AddContentById('unlock_state', 'style="display:none"');
	$layout->AddContentById('lock_state', 'style="display:none"');
}

if(Users_IsUserAdminOrModerator(Users_CurrentUserId())){
	if(intval($question->pinned) == 1){
		$layout->AddContentById('pin_state', 'style="display:none"');
	}else{
		$layout->AddContentById('unpin_state', 'style="display:none"');
	}
}else{
	$layout->AddContentById('pin_state', 'style="display:none"');
	$layout->AddContentById('unpin_state', 'style="display:none"');
}

if(!Users_IsUserLoggedIn()){
	$layout->AddContentById('subscribe_state', 'style="display:none"');
	$layout->AddContentById('unsubscribe_state', 'style="display:none"');
}else{
	if(count($db->get_results("SELECT * FROM " . TABLES_PREFIX . "posts_following WHERE user_id = ".intval(Users_CurrentUserId())." AND post_id = ".intval($_GET['id'])."" )) > 0){
		$layout->AddContentById('subscribe_state', 'style="display:none"');
	}else{
		$layout->AddContentById('unsubscribe_state', 'style="display:none"');
	}
}

if(!Users_IsUserAdminOrModerator(Users_CurrentUserId())){
	$layout->AddContentById('delete_state', 'style="display:none"');
}


$rows = ROWS_PER_PAGE;
$number_of_records = count($db->get_results("SELECT * FROM " . TABLES_PREFIX . "posts WHERE id = ".intval($_GET['id'])." OR parent_id = ".intval($_GET['id'])."" ));
$number_of_pages = ceil( $number_of_records / $rows );
if(isset($_GET['lastpage'])){
	$page = $number_of_pages;
}else{
	if(isset($_GET['page'])){
		$page = intval($_GET['page']);
	}else{
		$page = 1;
	}
}
$offset = ($page - 1) * $rows;
$layout->AddContentById('id', $question->id);
$layout->AddContentById('page', $page);

$latest = $db->get_results("SELECT * FROM " . TABLES_PREFIX . "posts WHERE id = ".intval($_GET['id'])." OR parent_id = ".intval($_GET['id'])." ORDER BY date ASC LIMIT $offset, $rows");


$rows_html = '';
if($latest){
	foreach($latest as $post){
		if($post->approved == 'y'){
			if($post->flagged == 'y'){
				$row_layout = new Layout('html/','str/');
				$row_layout->SetContentView('thread-flagged');
			}else{
				$row_layout = new Layout('html/','str/');
				$row_layout->SetContentView('thread-rows');
				$row_layout->AddContentById('id', $post->id);
				$row_layout->AddContentById('thread_id', $question->id);
				$row_layout->AddContentById('page', $page);
		
				
				if($post->photos != ''){
					$files = unserialize($post->photos);
					if(count($files) > 0 AND is_array($files)){
						$files_lists = '<div class="attachment-mail">' ;
						$files_count = 1;
						foreach($files as $f){
							$files_lists .= '<i class="icon-file"></i>&nbsp;<a target="_blank" href="'. FORUM_URL . 'uploads/' . $f.'">{{ST:attachment}} '.$files_count.'</a>&nbsp;<br/>';
							$files_count++;
						}
						$files_lists .= '</div>';
						$row_layout->AddContentById('files',  $files_lists );
					}
				}
				
				
				$signature = "";
		
				$row_layout->AddContentById('likes', NiceNumber($post->likes));
		
				$row_layout->AddContentById('date', getRelativeTime($post->date));
		
				$row_layout->AddContentById('user_id', $post->user_id);
		
				$user_details = Users_GetUserDetails($post->user_id);
				if($user_details){
					if($user_details['is_admin'] == true){
						$row_layout->AddContentById('is_admin', '<p><span class="label label-warning">{{ST:is_admin}}</span></p>');
					}elseif($user_details['is_moderator'] == true){
						$row_layout->AddContentById('is_admin', '<p><span class="label label-info">{{ST:moderator}}</span></p>');
					}
					if($user_details['username']){
						$row_layout->AddContentById('user_name', $user_details['username']);
					}
					if($user_details['path_to_profile']){
						$row_layout->AddContentById('path_to_profile', $user_details['path_to_profile']);
					}
					if($user_details['path_to_photo']){
						$row_layout->AddContentById('user_photo', $user_details['path_to_photo']);
					}else{
						$row_layout->AddContentById('user_photo', FORUM_URL.'img/anon.png');
					}
					
					if($user_details['signature']){
						$signature = "<hr/><div class='muted'>" . $user_details['signature'] . "</div>";
						
						$row_layout->AddContentById('signature', $signature);
					}	
				}
				
				if($post->quote AND $post->quote != ''){
					$row_layout->AddContentById('question', $post->quote . $post->body);
				}else{
					$row_layout->AddContentById('question', $post->body);
				}
				
				$row_layout->AddContentById('user_badges', Users_GetUserBadges($post->user_id));
		
				if(!Users_IsUserAdminOrModerator(Users_CurrentUserId())){
					if(Users_CurrentUserId() != intval($post->user_id)){
						$row_layout->AddContentById('edit_state', 'style="display:none"');
					}
				}
		
				if(!Users_IsUserAdminOrModerator(Users_CurrentUserId())){
					$row_layout->AddContentById('delete_state', 'style="display:none"');
				}
		
				if(!Users_IsUserLoggedIn()){
					$row_layout->AddContentById('like_or_un_get', 'like');
					$row_layout->AddContentById('like_or_un', '{{ST:like}}');
					$row_layout->AddContentById('like_alert', 'onclick="return SignInAlert();"');
					$row_layout->AddContentById('flag_alert', 'onclick="return SignInAlert();"');
				}elseif(count($db->get_results("SELECT * FROM " . TABLES_PREFIX . "likes WHERE user_id = ".intval(Users_CurrentUserId())." AND post_id = ".intval($post->id)."" )) > 0){
					
					$row_layout->AddContentById('like_or_un_get', 'unlike');
					$row_layout->AddContentById('like_or_un', '{{ST:unlike}}');
					
					$row_layout->AddContentById('flag_alert', 'onclick="return confirm(\'{{ST:are_you_sure}}\');"');
				}else{
					$row_layout->AddContentById('like_or_un_get', 'like');
					$row_layout->AddContentById('like_or_un', '{{ST:like}}');
					$row_layout->AddContentById('flag_alert', 'onclick="return confirm(\'{{ST:are_you_sure}}\');"');
				}
				
				if($question->locked == 'y'){
					$row_layout->AddContentById('post_alert', 'onclick="return LockedAlert();"');
				}
		
			}
		
			$rows_html .= $row_layout-> ReturnView();
		}
	}
	
	if($number_of_records>$rows){
		if(defined('SEO_HUMAN_FRIENDLY_URLS') AND SEO_HUMAN_FRIENDLY_URLS == true){
			$pagination = Paginate(FORUM_URL.'thread/'.UrlText($question->title).'/'.intval($_GET['id']).'/', $page, $number_of_pages, false, 3);
		}else{
			$pagination = Paginate(FORUM_URL.'thread.php?id='.intval($_GET['id']), $page, $number_of_pages, true, 3);
		}
		$layout->AddContentById('pagination', $pagination);
	}
	
}else{
	$rows_html = '<p>{{ST:there_are_no_posts}}</p>';
}



$layout->AddContentById('rows', $rows_html);
$db->update(TABLES_PREFIX . "posts", array('views'=>(1+$question->views)), array('id'=>$id), array("%d"));
$layout->RenderViewAndExit();
