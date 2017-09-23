<?php

require 'includes.php';

if(!Users_IsUserLoggedIn() OR !Users_IsUserAdmin(Users_CurrentUserId())){
	Leave(FORUM_URL);
}

$layout = GetPage('users', '{{ST:user_management}}');

$layout->AddContentById('breadcrumbs', ' <li><a href="'.FORUM_URL.'">{{ST:home}}</a> <span class="divider">/</span> </li><li class="active">{{ST:user_management}}</li>');

if(isset($_GET['user_approved'])){
	$layout->AddContentById('alert', $layout->GetContent('alert'));
	$layout->AddContentById('alert_nature', ' alert-success');
	$layout->AddContentById('alert_heading', '{{ST:success}}!');
	$layout->AddContentById('alert_message', '{{ST:user_approved}}');
}

if(isset($_GET['page'])){
	$page = intval($_GET['page']);
}else{
	$page = 1;
}
$rows = ROWS_PER_PAGE;


$offset = ($page - 1) * $rows;
$admins = $db->get_results("SELECT * FROM " . TABLES_PREFIX . "users ORDER BY id DESC LIMIT $offset, $rows");
$number_of_records = count($db->get_results("SELECT * FROM " . TABLES_PREFIX . "users" ));
$number_of_pages = ceil( $number_of_records / $rows );

$rows_html = '';
if($admins){
	foreach($admins as $admin){
		$row_layout = new Layout('html/','str/');
		$row_layout->SetContentView('users-rows');
		$row_layout->AddContentById('id', $admin->id);
		$row_layout->AddContentById('username', $admin->username);
		$row_layout->AddContentById('email', $admin->email);
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
		}elseif($admin->status == 'muted'){
			$row_layout->AddContentById('status', '{{ST:muted}}');
		}
		
		$rows_html .= $row_layout-> ReturnView();
	}
	
	if($number_of_records>$rows){
		$pagination = Paginate(FORUM_URL.'users.php', $page, $number_of_pages, false, 3);
		$layout->AddContentById('pagination', $pagination);
	}
	
}else{
	$rows_html = '<tr><td colspan="4">{{ST:no_items}}</td></tr>';
}



$layout->AddContentById('rows', $rows_html);



$layout->RenderViewAndExit();
