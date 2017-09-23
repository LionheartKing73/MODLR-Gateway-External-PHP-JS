<?php

require 'includes.php';

if(!Users_IsUserLoggedIn() OR !Users_IsUserAdminOrModerator(Users_CurrentUserId())){
	Leave(FORUM_URL);
}

$layout = GetPage('users-profiles', '{{ST:user_management}}');

$layout->AddContentById('breadcrumbs', ' <li><a href="'.FORUM_URL.'">{{ST:home}}</a> <span class="divider">/</span> </li><li class="active">{{ST:user_management}}</li>');


if(isset($_GET['page'])){
	$page = intval($_GET['page']);
}else{
	$page = 1;
}
$rows = ROWS_PER_PAGE;


$offset = ($page - 1) * $rows;
$admins = $db->get_results("SELECT * FROM " . TABLES_PREFIX . "users WHERE status != 'pending' AND role = 'user' ORDER BY id DESC LIMIT $offset, $rows");
$number_of_records = count($db->get_results("SELECT * FROM " . TABLES_PREFIX . "users" ));
$number_of_pages = ceil( $number_of_records / $rows );

$rows_html = '';
if($admins){
	foreach($admins as $admin){
		$row_layout = new Layout('html/','str/');
		$row_layout->SetContentView('users-profiles-rows');
		$row_layout->AddContentById('id', $admin->id);
		$row_layout->AddContentById('username', $admin->username);
		if($admin->role == 'admin'){
			$row_layout->AddContentById('role', '{{ST:admin}}');
		}elseif($admin->role == 'moderator'){
			$row_layout->AddContentById('role', '{{ST:moderator}}');
		}else{
			$row_layout->AddContentById('role', '{{ST:user}}');
		}
		
		if($admin->status == 'active'){
			$row_layout->AddContentById('status', '{{ST:active}}');
		}elseif($admin->status == 'banned'){
			$row_layout->AddContentById('status', '{{ST:banned}}');
		}elseif($admin->status == 'pending'){
			$row_layout->AddContentById('status', '{{ST:pending_activation}}');
		}
		
		$rows_html .= $row_layout-> ReturnView();
	}
	
	if($number_of_records>$rows){
		$pagination = Paginate(FORUM_URL.'users_profiles.php', $page, $number_of_pages, false, 3);
		$layout->AddContentById('pagination', $pagination);
	}
	
}else{
	$rows_html = '<tr><td colspan="4">{{ST:no_items}}</td></tr>';
}



$layout->AddContentById('rows', $rows_html);



$layout->RenderViewAndExit();
