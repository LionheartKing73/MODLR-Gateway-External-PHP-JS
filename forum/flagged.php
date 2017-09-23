<?php

require 'includes.php';

if(!Users_IsUserLoggedIn() OR !Users_IsUserAdmin(Users_CurrentUserId())){
	Leave(FORUM_URL);
}

$layout = GetPage('flagged', '{{ST:flagged}}');

$layout->AddContentById('breadcrumbs', ' <li><a href="'.FORUM_URL.'">{{ST:home}}</a> <span class="divider">/</span> </li><li class="active">{{ST:flagged}}</li>');

if(isset($_GET['page'])){
	$page = intval($_GET['page']);
}else{
	$page = 1;
}

if(isset($_GET['delete'])){
	$db->query("DELETE FROM " . TABLES_PREFIX . "posts WHERE id = " . intval($_GET['id']));
	$db->query("DELETE FROM " . TABLES_PREFIX . "flags WHERE post_id = " . intval($_GET['id']));
	$layout->AddContentById('alert', $layout->GetContent('alert'));
	$layout->AddContentById('alert_nature', ' alert-success');
	$layout->AddContentById('alert_heading', '{{ST:success}}!');
	$layout->AddContentById('alert_message', '{{ST:the_post_has_been_deleted}}');
}

if(isset($_GET['remove'])){
	$db->update(TABLES_PREFIX . "posts", array('flagged'=>'n'), array('id'=>intval($_GET['id'])), array("%s"));
	$db->query("DELETE FROM " . TABLES_PREFIX . "flags WHERE post_id = " . intval($_GET['id']));
	$layout->AddContentById('alert', $layout->GetContent('alert'));
	$layout->AddContentById('alert_nature', ' alert-success');
	$layout->AddContentById('alert_heading', '{{ST:success}}!');
	$layout->AddContentById('alert_message', '{{ST:the_flag_has_been_removed}}');
}



$rows = ROWS_PER_PAGE;
$offset = ($page - 1) * $rows;

$latest = $db->get_results("SELECT * FROM " . TABLES_PREFIX . "posts WHERE flagged = 'y' ORDER BY date DESC LIMIT $offset, $rows");
$number_of_records = count($db->get_results("SELECT * FROM " . TABLES_PREFIX . "posts WHERE flagged = 'y'" ));
$number_of_pages = ceil( $number_of_records / $rows );

$rows_html = '';
if($latest){
	foreach($latest as $post){
		$row_layout = new Layout('html/','str/');
		$row_layout->SetContentView('flagged-rows');
		$row_layout->AddContentById('id', $post->id);
		
		if($post->locked == 'y'){
			$row_layout->AddContentById('title', TrimText($post->title, 38));
			$row_layout->AddContentById('locked', '({{ST:locked}})');
		}else{
			$row_layout->AddContentById('title', TrimText($post->title, 46));
		}
		$row_layout->AddContentById('post', $post->body);
		$row_layout->AddContentById('page', $page);
		
		$row_layout->AddContentById('date', getRelativeTime($post->date));
		
		$row_layout->AddContentById('user_id', $post->user_id);
		
		$user_details = Users_GetUserDetails($post->user_id);
		if($user_details){
			if($user_details['username']){
				$row_layout->AddContentById('user_name', $user_details['username']);
			}
			if($user_details['path_to_photo']){
				$row_layout->AddContentById('user_photo', $user_details['path_to_photo']);
			}else{
				$row_layout->AddContentById('user_photo', FORUM_URL.'img/anon.png');
			}
		}
		
		$row_layout->AddContentById('user_badges', Users_GetUserBadges($post->user_id));
		
		/*
		$flag = $db->get_row("SELECT * FROM " . TABLES_PREFIX . "flags WHERE post_id = ".$post->id." ORDER BY id DESC LIMIT 0,1");
		if($flag){
			//$row_layout->AddContentById('reason', $flag->reason);
		
			$row_layout->AddContentById('flag_date', getRelativeTime($flag->date));
		
			$row_layout->AddContentById('reporter_user_id', $flag->user_id);
		
			$user_details = Users_GetUserDetails($flag->user_id);
			if($user_details){
				if($user_details['username']){
					$row_layout->AddContentById('reporter_user_name', $user_details['username']);
				}
				if($user_details['path_to_photo']){
					$row_layout->AddContentById('reporter_user_photo', $user_details['path_to_photo']);
				}else{
					$row_layout->AddContentById('reporter_user_photo', 'img/anon.png');
				}
			}
		}
		*/
		$rows_html .= $row_layout-> ReturnView();
	}
	
	if($number_of_records>$rows){
		$pagination = Paginate('flagged.php', $page, $number_of_pages, false, 3);
		$layout->AddContentById('pagination', $pagination);
	}
	
}else{
	$rows_html = '<p>{{ST:there_are_no_flagged_posts}}</p>';
}



$layout->AddContentById('rows', $rows_html);


$layout->RenderViewAndExit();
