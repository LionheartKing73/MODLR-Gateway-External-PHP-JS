<?php

$meta_category = json_decode('{"description" : "Search"}');

require 'includes.php';


if(defined('PRIVATE_FORUM') AND PRIVATE_FORUM == true AND !Users_IsUserLoggedIn()){
	Leave(Users_SignInPageUrl());
}

$layout = GetPage('search', '{{ST:search_results}}');

if(defined('SITE_NAME') AND SITE_NAME != ''){
	$layout->AddContentById('meta_title', SITE_NAME);
}
if(defined('SITE_META_DESCRIPTION') AND SITE_META_DESCRIPTION != ''){
	$layout->AddContentById('meta_desc', SITE_META_DESCRIPTION);
}

$layout->AddContentById('meta_desc', $meta_category->description);

if(isset($_POST['search'])){
	if($_POST['keyword'] != ''){
		$keyword_post = str_replace(' ', '+', $_POST['keyword']);
		Leave(FORUM_URL.'search.php?q='.$keyword_post);
	}
}

$q = '';
if(isset($_GET['q']) AND $_GET['q']  != ''){
	$q = str_replace('+', ' ', $_GET['q']);
}elseif(isset($_POST['q']) AND $_POST['q']  != ''){
	$q = $_POST['q'];
}else{
	Leave(FORUM_URL);
}

$layout->AddContentById('breadcrumbs', ' <li><a href="'.FORUM_URL.'">{{ST:home}}</a> <span class="divider">/</span> </li><li class="active">{{ST:search_results}}: '.$q.'</li>');


if(isset($_GET['page'])){
	$page = intval($_GET['page']);
}else{
	$page = 1;
}
$rows = ROWS_PER_PAGE;
$offset = ($page -1) * $rows;

$search_results = $db->get_results("SELECT * FROM " . TABLES_PREFIX . "posts WHERE " . CreateSearchQuery($q, array('title','body')));
$search_filtered = array();
$added_questions = array();
foreach($search_results as $s){
	if($s->is_question == 'y' AND $s->flagged == 'n' AND $s->approved == 'y'){
		$search_filtered[] = $s;
		$added_questions[] = intval($s->id);
	}elseif(intval($s->parent_id) != 0 AND !in_array(intval($s->parent_id), $added_questions)){
		$parent = $db->get_row("SELECT * FROM " . TABLES_PREFIX . "posts WHERE id = ".intval($s->parent_id)."  AND flagged = 'n' ORDER BY pinned DESC, id DESC LIMIT 0,1");
		if($parent){
			$search_filtered[] = $parent;
			$added_questions[] = intval($s->parent_id);
		}
	}
}
$number_of_records = count($search_filtered);

$latest = array_slice($search_filtered, $offset, $rows);

$number_of_pages = ceil( $number_of_records / $rows );

$rows_html = '';
if($latest){
	foreach($latest as $post){
		$row_layout = new Layout('html/','str/');
		$row_layout->SetContentView('home-rows');
		$row_layout->AddContentById('id', $post->id);
		
		if($post->pinned == 1){
			$row_layout->AddContentById('pinned', '<img style="float: right;" src="{{ID:base_url}}img/pin.png" width="16px"  height="16px" title="{{ST:pinned}}">');
		}
		
		if($post->locked == 'y'){
			$row_layout->AddContentById('title', TrimText($post->title, 38));
			$row_layout->AddContentById('locked', '({{ST:locked}})');
		}else{
			$row_layout->AddContentById('title', TrimText($post->title, 46));
		}
		$row_layout->AddContentById('question', TrimText($post->body, 200));
		
		$row_layout->AddContentById('likes', NiceNumber($post->likes));
		$row_layout->AddContentById('views', NiceNumber($post->views));
		
		$row_layout->AddContentById('date', getRelativeTime($post->date));
		
		$row_layout->AddContentById('replies', count($db->get_results("SELECT * FROM " . TABLES_PREFIX . "posts WHERE parent_id = " . $post->id )));
		
		$row_layout->AddContentById('category_id', $post->category_id);
		$row_layout->AddContentById('category_name', $db->get_var("SELECT name FROM " . TABLES_PREFIX . "categories WHERE id = " . $post->category_id));
		
		if(defined('SEO_HUMAN_FRIENDLY_URLS') AND SEO_HUMAN_FRIENDLY_URLS == true){
			$row_layout->AddContentById('thread_url', '{{ID:base_url}}thread/'.UrlText($post->title).'/'.$post->id.'/');
			$row_layout->AddContentById('category_url', '{{ID:base_url}}forum/'.UrlText($db->get_var("SELECT name FROM " . TABLES_PREFIX . "categories WHERE id = " . $post->category_id)).'/'.$post->category_id.'/');
		}else{
			$row_layout->AddContentById('thread_url', '{{ID:base_url}}thread.php?id'.$post->id);
			$row_layout->AddContentById('category_url', '{{ID:base_url}}index.php?id='.$post->category_id);
		}
		
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
		}
		
		$row_layout->AddContentById('user_badges', Users_GetUserBadges($post->user_id));
		
		$rows_html .= $row_layout-> ReturnView();
	}
	
	if($number_of_records>$rows){
		$pagination = Paginate(FORUM_URL.'search.php?q='.str_replace(' ', '+', $q), $page, $number_of_pages, true, 3);
		$layout->AddContentById('pagination', $pagination);
	}
	
}else{
	$rows_html = '<p>{{ST:there_are_no_posts}}</p>';
}



$layout->AddContentById('rows', $rows_html);


$layout->RenderViewAndExit();
