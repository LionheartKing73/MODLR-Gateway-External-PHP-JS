<?php

require 'includes.php';

if(!Users_IsUserLoggedIn() OR !Users_IsUserAdmin(Users_CurrentUserId())){
	Leave(FORUM_URL);
}

$layout = GetPage('achievements', '{{ST:achievements}}');

$layout->AddContentById('breadcrumbs', ' <li><a href="'.FORUM_URL.'">{{ST:home}}</a> <span class="divider">/</span> </li><li class="active">{{ST:achievements}}</li>');


if(isset($_GET['page'])){
	$page = intval($_GET['page']);
}else{
	$page = 1;
}
$rows = ROWS_PER_PAGE;


$offset = ($page - 1) * $rows;
$achievements = $db->get_results("SELECT * FROM " . TABLES_PREFIX . "achievements ORDER BY id DESC LIMIT $offset, $rows");
$number_of_records = count($db->get_results("SELECT * FROM " . TABLES_PREFIX . "achievements" ));
$number_of_pages = ceil( $number_of_records / $rows );

$rows_html = '';
if($achievements){
	foreach($achievements as $a){
		$row_layout = new Layout('html/','str/');
		$row_layout->SetContentView('achievements-rows');
		$row_layout->AddContentById('id', $a->id);
		$row_layout->AddContentById('name', $a->name);
		
		if($a->start_from OR $a->end_at){
			$row_layout->AddContentById('range', $a->start_from . ' - ' . $a->end_at);
		}
		
		$row_layout->AddContentById('icon', '<img src="'.$a->icon.'" alt="'.$a->name.'"/>');
		
		if($a->type == 'all_posts'){
			$row_layout->AddContentById('type', '{{ST:all_posts}}');
		}elseif($a->type == 'started_threads'){
			$row_layout->AddContentById('type', '{{ST:started_threads}}');
		}elseif($a->type == 'replies'){
			$row_layout->AddContentById('type', '{{ST:replies}}');
		}elseif($a->type == 'membership_time'){
			$row_layout->AddContentById('type', '{{ST:membership_time}}');
		}elseif($a->type == 'manual_assign'){
			$row_layout->AddContentById('type', '{{ST:manual_assign}}');
		}
		
		$rows_html .= $row_layout-> ReturnView();
	}
	
	if($number_of_records>$rows){
		$pagination = Paginate(FORUM_URL.'achievements.php', $page, $number_of_pages, false, 3);
		$layout->AddContentById('pagination', $pagination);
	}
	
}else{
	$rows_html = '<tr><td colspan="4">{{ST:no_items}}</td></tr>';
}



$layout->AddContentById('rows', $rows_html);



$layout->RenderViewAndExit();
