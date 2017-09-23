<?php

require 'includes.php';

$layout = GetPage('online', '{{ST:who_is_online}}');

$layout->AddContentById('breadcrumbs', ' <li><a href="'.FORUM_URL.'">{{ST:home}}</a> <span class="divider">/</span> </li><li class="active">{{ST:who_is_online}}</li>');

$timecheck = date('Y-m-d H:i:s',time()-(60*5));

$online = $db->get_results("SELECT * FROM " . TABLES_PREFIX . "users WHERE id != ".Users_CurrentUserId()." AND last_seen > '$timecheck'");

$rows_html = '';
if($online){
	foreach($online as $o){
		$row_layout = new Layout('html/','str/');
		$row_layout->SetContentView('online-rows');
		$user_details = Users_GetUserDetails($o->id);
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
			
		$row_layout->AddContentById('user_badges', Users_GetUserBadges($o->id));
		
		$rows_html .= $row_layout-> ReturnView();
	}
}else{
	$rows_html = '<center>{{ST:no_users_online}}</center>';
}

$layout->AddContentById('rows', $rows_html);

$layout->RenderViewAndExit();
