<?php

require 'includes.php';

if(defined('PRIVATE_FORUM') AND PRIVATE_FORUM == true AND !Users_IsUserLoggedIn()){
	Leave(Users_SignInPageUrl());
}

$classic = false;
if(defined('CLASSIC_HOME_PAGE') AND CLASSIC_HOME_PAGE == true)
	$classic = true;

if(isset($_GET['id']) OR isset($_GET['mine']) OR isset($_GET['following']) OR $classic == false){
	if(isset($_GET['id'])){
		$meta_category = $db->get_row("SELECT * FROM " . TABLES_PREFIX . "categories WHERE id = " . intval($_GET['id']) . " ORDER BY id DESC LIMIT 0,1");
		if(defined('SITE_NAME') AND SITE_NAME != ''){
			$layout = GetPage('home', $meta_category->name . ' | ' . SITE_NAME);
			$layout->AddContentById('meta_title', $meta_category->name . ' | ' . SITE_NAME);
		}else{
			$layout = GetPage('home', $meta_category->name);
			$layout->AddContentById('meta_title', $meta_category->name);
		}
		$layout->AddContentById('meta_desc', $meta_category->description);
	}else{
		$layout = GetPage('home', '{{ST:home}}');
		if(defined('SITE_NAME') AND SITE_NAME != ''){
			$layout->AddContentById('meta_title', SITE_NAME);
		}
		if(defined('SITE_META_DESCRIPTION') AND SITE_META_DESCRIPTION != ''){
			$layout->AddContentById('meta_desc', SITE_META_DESCRIPTION);
		}
	}
}else{
	$layout = GetPage('home-forums', '{{ST:home}}');
	if(defined('SITE_NAME') AND SITE_NAME != ''){
		$layout->AddContentById('meta_title', SITE_NAME);
	}
	if(defined('SITE_META_DESCRIPTION') AND SITE_META_DESCRIPTION != ''){
		$layout->AddContentById('meta_desc', SITE_META_DESCRIPTION);
	}
}

if(isset($_GET['id'])){
	$layout->AddContentById('home_title', $db->get_var("SELECT name FROM " . TABLES_PREFIX . "categories WHERE id = " . intval($_GET['id'])));
	
	$bread_crumb_html = '';
	
	$bread_search = true;
	$current_level = intval($_GET['id']);
	if(intval($db->get_var("SELECT parent FROM " . TABLES_PREFIX . "categories WHERE id = " . intval($_GET['id']))) == 0){
		$bread_search = false;
	}else{
		$current_level = intval($db->get_var("SELECT parent FROM " . TABLES_PREFIX . "categories WHERE id = " . intval($_GET['id'])));
	}
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
	
	$layout->AddContentById('breadcrumbs', ' <li><a href="'.FORUM_URL.'"><i class="fa fa-home"></i> {{ST:home}}</a></li> <a class="active-trail active" href="#">'.$db->get_var("SELECT name FROM " . TABLES_PREFIX . "categories WHERE id = " . intval($_GET['id'])).'</a>');
	//$layout->AddContentById('breadcrumbs', ' <li><a href="'.FORUM_URL.'">{{ST:home}}</a> <span class="divider">/</span> </li>'. $bread_crumb_html . '<li class="active">'.$db->get_var("SELECT name FROM " . TABLES_PREFIX . "categories WHERE id = " . intval($_GET['id'])).'</li>');

	$subforums = $db->get_results("SELECT * FROM " . TABLES_PREFIX . "categories WHERE parent = ".intval($_GET['id'])." ORDER BY order_by ASC");
	if(count($subforums) > 0){
		$subforums_layout = new Layout('html/','str/');
		$subforums_layout->SetContentView('home-subs');
		$sf_html = '';
		foreach($subforums as $sf){
			if(defined('SEO_HUMAN_FRIENDLY_URLS') AND SEO_HUMAN_FRIENDLY_URLS == true){
				$sf_html .= '<li><a href="{{ID:base_url}}forum/'.UrlText($sf->name).'/'.$sf->id.'/">'.$sf->name.'</a></li>';
			}else{
				$sf_html .= '<li><a href="{{ID:base_url}}index.php?id='.$sf->id.'">'.$sf->name.'</a></li>';
			}
		}
		$subforums_layout->AddContentById('rows', $sf_html);
		$layout->AddContentById('subforums', $subforums_layout->ReturnView());
	}
	
	
}elseif(isset($_GET['mine'])){
	$layout->AddContentById('home_title', '{{ST:your_threads}}');
	
	$layout->AddContentById('breadcrumbs', ' <li><a href="'.FORUM_URL.'"><i class="fa fa-home"></i> {{ST:home}}</a></li> <a class="active-trail active" href="#">{{ST:your_threads}}</a>');
	
	//$layout->AddContentById('breadcrumbs', ' <li><a href="'.FORUM_URL.'">{{ST:home}}</a> <span class="divider">/</span> </li><li class="active">{{ST:your_threads}}</li>');
}elseif(isset($_GET['following'])){
	$layout->AddContentById('home_title', '{{ST:your_subscriptions}}');
	//$layout->AddContentById('breadcrumbs', ' <li><a href="'.FORUM_URL.'">{{ST:home}}</a> <span class="divider">/</span> </li><li class="active">{{ST:your_subscriptions}}</li>');
	$layout->AddContentById('breadcrumbs', ' <li><a href="'.FORUM_URL.'"><i class="fa fa-home"></i> {{ST:home}}</a></li> <a class="active-trail active" href="#">{{ST:your_subscriptions}}</a>');
	
}else{
	$layout->AddContentById('home_title', '{{ST:all_forums}}');
	//$layout->AddContentById('breadcrumbs', ' <li class="active">{{ST:home}}</li>');
	$layout->AddContentById('breadcrumbs', ' <li><a href="'.FORUM_URL.'"><i class="fa fa-home"></i> {{ST:home}}</a></li>');
}

if(isset($_GET['message']) AND $_GET['message'] != ''){
	
	if($_GET['message'] == 'deleted'){
		$layout->AddContentById('alert', $layout->GetContent('alert'));
		$layout->AddContentById('alert_nature', ' alert-success');
		$layout->AddContentById('alert_heading', '{{ST:success}}!');
		$layout->AddContentById('alert_message', '{{ST:the_post_and_its_children_have_been_deleted}}');
	}
}

$rows_html = '';
if(isset($_GET['id']) OR isset($_GET['mine']) OR isset($_GET['following']) OR $classic == false){
	if(isset($_GET['page'])){
		$page = intval($_GET['page']);
	}else{
		$page = 1;
	}
	$rows = ROWS_PER_PAGE;
	$offset = ($page - 1) * $rows;

	if(isset($_GET['id'])){
		$latest = $db->get_results("SELECT * FROM " . TABLES_PREFIX . "posts WHERE approved = 'y' AND is_question = 'y' AND category_id = ".intval($_GET['id'])." AND flagged = 'n' ORDER BY pinned DESC, date DESC LIMIT $offset, $rows");
		$number_of_records = count($db->get_results("SELECT * FROM " . TABLES_PREFIX . "posts WHERE approved = 'y' AND is_question = 'y' AND category_id = ".intval($_GET['id'])." AND flagged = 'n'" ));
	}elseif(isset($_GET['mine'])){
		$latest = $db->get_results("SELECT * FROM " . TABLES_PREFIX . "posts WHERE approved = 'y' AND is_question = 'y' AND user_id = ".intval(Users_CurrentUserId())." AND flagged = 'n' ORDER BY pinned DESC, date DESC LIMIT $offset, $rows");
		$number_of_records = count($db->get_results("SELECT * FROM " . TABLES_PREFIX . "posts WHERE approved = 'y' AND is_question = 'y' AND user_id = ".intval(Users_CurrentUserId())." AND flagged = 'n'" ));
	}elseif(isset($_GET['following'])){
		$tables = TABLES_PREFIX . 'posts, ' . TABLES_PREFIX .'posts_following';
		$select = TABLES_PREFIX . 'posts.*';
		$where = TABLES_PREFIX . 'posts.id = ' . TABLES_PREFIX .'posts_following.post_id AND '. TABLES_PREFIX .'posts_following.user_id = ' . intval(Users_CurrentUserId());
		$latest = $db->get_results("SELECT $select FROM $tables WHERE $where AND approved = 'y' AND is_question = 'y' AND flagged = 'n' ORDER BY pinned DESC, date DESC LIMIT $offset, $rows");
		$number_of_records = count($db->get_results("SELECT $select FROM $tables WHERE $where AND approved = 'y' AND is_question = 'y' AND flagged = 'n'" ));
	}else{
		$latest = $db->get_results("SELECT * FROM " . TABLES_PREFIX . "posts WHERE approved = 'y' AND is_question = 'y' AND flagged = 'n' ORDER BY pinned DESC, date DESC LIMIT $offset, $rows");
		$number_of_records = count($db->get_results("SELECT * FROM " . TABLES_PREFIX . "posts WHERE approved = 'y' AND is_question = 'y' AND flagged = 'n'" ));
	}


	$number_of_pages = ceil( $number_of_records / $rows );


	if($latest){
		foreach($latest as $post){
			$row_layout = new Layout('html/','str/');
			$row_layout->SetContentView('home-rows');
			$row_layout->AddContentById('id', $post->id);
			
			if($post->pinned == 1){
				$row_layout->AddContentById('pinned', '<img style="float: right;" src="{{ID:base_url}}img/pin.png" width="16px"  height="16px" title="{{ST:pinned}}">');
			}
		
			if($post->locked == 'y'){
				$row_layout->AddContentById('title', TrimText($post->title, 34));
				$row_layout->AddContentById('locked', '({{ST:locked}})');
			}else{
				$row_layout->AddContentById('title', TrimText($post->title, 42));
			}
			$row_layout->AddContentById('question', TrimText($post->body, 150));
		
			$row_layout->AddContentById('likes', NiceNumber($post->likes));
			$row_layout->AddContentById('views', NiceNumber($post->views));
		
			$row_layout->AddContentById('date', getRelativeTime($post->date));
		
			$row_layout->AddContentById('replies', count($db->get_results("SELECT * FROM " . TABLES_PREFIX . "posts WHERE approved = 'y' AND flagged = 'n' AND parent_id = " . $post->id )));
		
			$row_layout->AddContentById('category_id', $post->category_id);
			$row_layout->AddContentById('category_name', $db->get_var("SELECT name FROM " . TABLES_PREFIX . "categories WHERE id = " . $post->category_id));
		
			if(defined('SEO_HUMAN_FRIENDLY_URLS') AND SEO_HUMAN_FRIENDLY_URLS == true){
				$row_layout->AddContentById('thread_url', '{{ID:base_url}}thread/'.UrlText($post->title).'/'.$post->id.'/');
				$row_layout->AddContentById('category_url', '{{ID:base_url}}forum/'.UrlText($db->get_var("SELECT name FROM " . TABLES_PREFIX . "categories WHERE id = " . $post->category_id)).'/'.$post->category_id.'/');
			}else{
				$row_layout->AddContentById('thread_url', '{{ID:base_url}}thread.php?id='.$post->id);
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
		
			$rows_html .= $row_layout->ReturnView();
		}
	
		if($number_of_records>$rows){
			if(isset($_GET['id'])){
				if(defined('SEO_HUMAN_FRIENDLY_URLS') AND SEO_HUMAN_FRIENDLY_URLS == true){
					$pagination = Paginate(FORUM_URL.'forum/'.UrlText($db->get_var("SELECT name FROM " . TABLES_PREFIX . "categories WHERE id = " . intval($_GET['id']))).'/'.intval($_GET['id']).'/', $page, $number_of_pages, false, 3);
				}else{
					$pagination = Paginate(FORUM_URL.'?id='.intval($_GET['id']), $page, $number_of_pages, true, 3);
				}
				
				
			}elseif(isset($_GET['mine'])){
				$pagination = Paginate(FORUM_URL.'?mine=1', $page, $number_of_pages, true, 3);
			}elseif(isset($_GET['following'])){
				$pagination = Paginate(FORUM_URL.'?following=1', $page, $number_of_pages, true, 3);
			}else{
				$pagination = Paginate(FORUM_URL, $page, $number_of_pages, false, 3);
			}
			$layout->AddContentById('pagination', $pagination);
		}
	}else{
		$rows_html = '<p>{{ST:there_are_no_posts}}</p>';
	}
}else{
	$categories = get_nested_categories();
	if($categories){
		foreach($categories as $category){
			$row_layout = new Layout('html/','str/');
			$row_layout->SetContentView('home-forums-rows');
			$row_layout->AddContentById('id', $category->id);
			$row_layout->AddContentById('name', $category->name . '<br/><br/>');
			
			if(defined('SEO_HUMAN_FRIENDLY_URLS') AND SEO_HUMAN_FRIENDLY_URLS == true){
				$row_layout->AddContentById('category_url', '{{ID:base_url}}forum/'.UrlText($category->name).'/'.$category->id.'/');
			}else{
				$row_layout->AddContentById('category_url', '{{ID:base_url}}index.php?id='.$category->id);
			}
		
			$posts_count = count($db->get_results("SELECT * FROM " . TABLES_PREFIX . "posts WHERE approved = 'y' AND flagged = 'n' AND is_question = 'y' AND category_id = " . intval($category->id) ));
			$row_layout->AddContentById('posts', $posts_count);
			
			$latest_post = $db->get_row("SELECT * FROM " . TABLES_PREFIX . "posts WHERE approved = 'y' AND category_id = " . intval($category->id) ." AND flagged = 'n' ORDER BY date DESC LIMIT 0,1");
			if($latest_post){
				if($latest_post->is_question == 'y'){
					if(defined('SEO_HUMAN_FRIENDLY_URLS') AND SEO_HUMAN_FRIENDLY_URLS == true){
						$row_layout->AddContentById('latest', '<a href="{{ID:base_url}}thread/'.UrlText($latest_post->title).'/'.$latest_post->id.'/">'.TrimText($latest_post->title, 45) . '</a><br/>' . getRelativeTime($latest_post->date));
					}else{
						$row_layout->AddContentById('latest', '<a href="{{ID:base_url}}thread.php?id='.$latest_post->id.'">'.TrimText($latest_post->title, 45) . '</a><br/>' . getRelativeTime($latest_post->date));
					}
				}else{
					if(defined('SEO_HUMAN_FRIENDLY_URLS') AND SEO_HUMAN_FRIENDLY_URLS == true){
						$row_layout->AddContentById('latest', '<a href="{{ID:base_url}}thread/'.UrlText($db->get_var("SELECT title FROM " . TABLES_PREFIX . "posts WHERE id = " . intval($latest_post->parent_id))).'/'.$latest_post->parent_id.'/">'.TrimText($db->get_var("SELECT title FROM " . TABLES_PREFIX . "posts WHERE id = " . intval($latest_post->parent_id)), 45) . '</a><br/>' . getRelativeTime($latest_post->date));
					}else{
						$row_layout->AddContentById('latest', '<a href="{{ID:base_url}}thread.php?id='.$latest_post->parent_id.'">'.TrimText($db->get_var("SELECT title FROM " . TABLES_PREFIX . "posts WHERE id = " . intval($latest_post->parent_id)), 45) . '</a><br/>' . getRelativeTime($latest_post->date));
					}
				}
			}
		
			$rows_html .= $row_layout-> ReturnView();
		}
	}else{
		$rows_html = '<tr><td colspan="3">{{ST:no_items}}</td></tr>';
	}
}
$layout->AddContentById('rows', $rows_html);


$layout->RenderViewAndExit();
