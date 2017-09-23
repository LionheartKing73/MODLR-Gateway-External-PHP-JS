<?php

require 'includes.php';

if(!Users_IsUserLoggedIn() OR !Users_IsUserAdmin(Users_CurrentUserId())){
	Leave(FORUM_URL);
}

$layout = GetPage('approve', '{{ST:approve_posts}}');

$layout->AddContentById('breadcrumbs', ' <li><a href="'.FORUM_URL.'">{{ST:home}}</a> <span class="divider">/</span> </li><li class="active">{{ST:approve_posts}}</li>');

if(isset($_GET['page'])){
	$page = intval($_GET['page']);
}else{
	$page = 1;
}

if(isset($_GET['delete'])){
	$db->query("DELETE FROM " . TABLES_PREFIX . "posts WHERE id = " . intval($_GET['id']));
	$layout->AddContentById('alert', $layout->GetContent('alert'));
	$layout->AddContentById('alert_nature', ' alert-success');
	$layout->AddContentById('alert_heading', '{{ST:success}}!');
	$layout->AddContentById('alert_message', '{{ST:the_post_has_been_deleted}}');
}

if(isset($_GET['approve'])){
	$db->update(TABLES_PREFIX . "posts", array('approved'=>'y'), array('id'=>intval($_GET['id'])), array("%s"));
	$layout->AddContentById('alert', $layout->GetContent('alert'));
	$layout->AddContentById('alert_nature', ' alert-success');
	$layout->AddContentById('alert_heading', '{{ST:success}}!');
	$layout->AddContentById('alert_message', '{{ST:the_post_has_been_approved}}');
}



$rows = ROWS_PER_PAGE;
$offset = ($page - 1) * $rows;

$latest = $db->get_results("SELECT * FROM " . TABLES_PREFIX . "posts WHERE approved = 'n' ORDER BY date DESC LIMIT $offset, $rows");
$number_of_records = count($db->get_results("SELECT * FROM " . TABLES_PREFIX . "posts WHERE approved = 'n'" ));
$number_of_pages = ceil( $number_of_records / $rows );

$rows_html = '';
if($latest){
	foreach($latest as $post){
		$row_layout = new Layout('html/','str/');
		$row_layout->SetContentView('approve-rows');
		$row_layout->AddContentById('id', $post->id);
		
		$row_layout->AddContentById('title', TrimText($post->title, 46));
		$row_layout->AddContentById('post', $post->body);
		$row_layout->AddContentById('page', $page);
		
		if($post->photos != ''){
			$files = unserialize($post->photos);
			if(count($files) > 0 AND is_array($files)){
				$files_lists = '<br/><p>';
				$files_count = 1;
				foreach($files as $f){
					$files_lists .= '<i class="icon-file"></i>&nbsp;<a target="_blank" href="'. FORUM_URL . 'uploads/' . $f.'">{{ST:attachment}} '.$files_count.'</a>&nbsp;<br/>';
					$files_count++;
				}
				$files_lists .= '</p>';
				$row_layout->AddContentById('files', $files_lists);
			}
		}
				
		
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
		
		$rows_html .= $row_layout-> ReturnView();
	}
	
	if($number_of_records>$rows){
		$pagination = Paginate('approve.php', $page, $number_of_pages, false, 3);
		$layout->AddContentById('pagination', $pagination);
	}
	
}else{
	$rows_html = '<p>{{ST:there_are_no_posts_pending_approval}}</p>';
}



$layout->AddContentById('rows', $rows_html);


$layout->RenderViewAndExit();
