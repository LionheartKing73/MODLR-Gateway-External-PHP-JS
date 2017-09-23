<?php

require 'includes.php';

if(!Users_IsUserLoggedIn()){
	Leave(FORUM_URL);
}

if(isset($_GET['contact'])){
	$id = intval($_GET['contact']);
}else{
	$id = null;
}

$layout = GetPage('messages', '{{ST:messages}}');

//$layout->AddContentById('breadcrumbs', ' <li><a href="'.FORUM_URL.'">{{ST:home}}</a> <span class="divider">/</span> </li><li class="active">{{ST:messages}}</li>');
$layout->AddContentById('breadcrumbs', ' <li><a href="'.FORUM_URL.'"><i class="fa fa-home"></i> {{ST:home}}</a></li> <a class="active-trail active" href="#">{{ST:messages}}</a>');
	
$contacts = $db->get_results("SELECT * FROM " . TABLES_PREFIX . "contacts WHERE user_id = ".Users_CurrentUserId());

if($contacts){
	$contacts_html = '{{ST:contacts}}: ';
	foreach($contacts as $c){
		if($id != null AND $c->contact_id == $id){
			$layout->AddContentById('current_contact', '<h3>'.$c->contact_username.'</h3><br/>');
		}
		$contacts_html .= '&nbsp;<a href="messages.php?contact='.$c->contact_id.'"<span class="label label-info">' . $c->contact_username . '</a></span>';	
	}
	$layout->AddContentById('contacts', $contacts_html);
}

if(isset($_GET['mark_as_read']) AND $_GET['mark_as_read'] != ''){
	$to_mark = $db->get_row("SELECT * FROM " . TABLES_PREFIX . "messages WHERE id = ".intval($_GET['mark_as_read'])." ORDER BY id DESC LIMIT 0,1");
	if($to_mark->receiver == Users_CurrentUserId()){
		$db->update(TABLES_PREFIX . "messages", array('seen'=>'y'), array('id'=>intval($_GET['mark_as_read'])), array("%s"));
	}
}

if($id != null){
	$form_layout = new Layout('html/','str/');
	$form_layout->SetContentView('messages-form');
	$form_layout->AddContentById('id', $id);
	$layout->AddContentById('form', $form_layout->ReturnView());
	
if(isset($_POST['send'])){
	
	$errors = false;
	$values = array();
	$format = array();
	$error_msg = '';
	
	if(isset($_POST['message']) AND $_POST['message'] != ''){
		$layout->AddContentById('message', $_POST['message']);
		$values['message'] = $_POST['message'];
		$format[] = "%s";
	}else{
		$errors = true;
		$error_msg .= '{{ST:message_required}} ';
	}
	
	if(Users_CurrentUserId() == $id){
		$errors = true;
		$error_msg .= '{{ST:cant_send_yourself_a_message}} ';
	}
	
	if(!$errors){
		$values['sender'] = Users_CurrentUserId();
		$format[] = "%d";
		$values['receiver'] = $id;
		$format[] = "%d";
		$values['date_sent'] = date('Y-m-d H:i:s');
		$format[] = "%s";
		
		if($db->insert(TABLES_PREFIX . "messages", $values, $format)){
			$user_details = Users_GetUserDetails($id);
			
			
			if(!$db->get_results("SELECT * FROM " . TABLES_PREFIX . "contacts WHERE user_id = ".Users_CurrentUserId()." AND contact_id = $id")){
				$db->insert(TABLES_PREFIX . "contacts", array('user_id'=>Users_CurrentUserId(), 'contact_id'=>$id, 'contact_username'=>$user_details['username']), array("%d","%d","%s"));
			}
			
			if(!$db->get_results("SELECT * FROM " . TABLES_PREFIX . "contacts WHERE user_id = $id AND contact_id = ".Users_CurrentUserId())){
				$db->insert(TABLES_PREFIX . "contacts", array('user_id'=>$id, 'contact_id'=>Users_CurrentUserId(), 'contact_username'=>Users_CurrentUserUsername()), array("%d","%d","%s"));
			}
			
			/*
			
			if($user_details['email']){
				$to = $user_details['email'];
				$subject = "[" . $_SERVER['SERVER_NAME'] . "]Forum: " . $strings->Get('message_subject') . Users_CurrentUserUsername();
				$message = $strings->Get('message_body') . '

' . FORUM_URL;
				$from = WEBMASTER_EMAIL;
				$headers = "From: $from";
				ini_set("sendmail_from", $from);	
				//mail($to,$subject,$message,$headers);
			}*/
			
			$layout->AddContentById('alert2', $layout->GetContent('alert'));
			$layout->AddContentById('alert_nature', ' alert-success');
			$layout->AddContentById('alert_heading', '{{ST:success}}!');
			$layout->AddContentById('alert_message', '{{ST:your_message_has_been_sent}}');
		}else{
			$layout->AddContentById('alert2', $layout->GetContent('alert'));
			$layout->AddContentById('alert_nature', ' alert-error');
			$layout->AddContentById('alert_heading', '{{ST:error}}!');
			$layout->AddContentById('alert_message', '{{ST:unknow_error_try_again}}');
		}
	}else{
		$layout->AddContentById('alert2', $layout->GetContent('alert'));
		$layout->AddContentById('alert_nature', ' alert-error');
		$layout->AddContentById('alert_heading', '{{ST:error}}!');
		$layout->AddContentById('alert_message', $error_msg);
	}
}

}

if(isset($_GET['page'])){
	$page = intval($_GET['page']);
}else{
	$page = 1;
}

$rows = ROWS_PER_PAGE;
$offset = ($page - 1) * $rows;
if($id != null){
	$messages = $db->get_results("SELECT * FROM " . TABLES_PREFIX . "messages WHERE sender IN (".Users_CurrentUserId().",$id) AND receiver IN (".Users_CurrentUserId().",$id) ORDER BY date_sent DESC LIMIT $offset, $rows");
	$number_of_records = count($db->get_results("SELECT * FROM " . TABLES_PREFIX . "messages WHERE sender IN (".Users_CurrentUserId().",$id) AND receiver IN (".Users_CurrentUserId().",$id)"));
}else{
	$messages = $db->get_results("SELECT * FROM " . TABLES_PREFIX . "messages WHERE seen = 'n' AND receiver = ".Users_CurrentUserId()." ORDER BY date_sent DESC LIMIT $offset, $rows");
	$number_of_records = count($db->get_results("SELECT * FROM " . TABLES_PREFIX . "messages WHERE seen = 'n' AND receiver = ".Users_CurrentUserId()));

}
$number_of_pages = ceil( $number_of_records / $rows );

$rows_html = '';
if($messages){
	foreach($messages as $m){
			$row_layout = new Layout('html/','str/');
			if($id != null){
				$row_layout->SetContentView('messages-rows2');
			}else{
				$row_layout->SetContentView('messages-rows');
			}
			$row_layout->AddContentById('id', $m->id);
		
			$row_layout->AddContentById('message', $m->message);
			
			if($id != null){
				if($m->sender == Users_CurrentUserId()){
					$row_layout->AddContentById('hide_mark_as_read', ' display: none;');
				}elseif($m->seen == 'y'){
					$row_layout->AddContentById('hide_mark_as_read', ' display: none;');
				}
			}
			
			$row_layout->AddContentById('date', getRelativeTime($m->date_sent));
		
			
			$row_layout->AddContentById('user_id', $m->sender);
		
			$user_details = Users_GetUserDetails($m->sender);
			if($user_details){
				if($user_details['username']){
					$row_layout->AddContentById('user_name', $user_details['username']);
				}
				if($user_details['is_admin'] == true){
					$row_layout->AddContentById('is_admin', '<p><span class="label label-warning">{{ST:is_admin}}</span></p>');
				}elseif($user_details['is_moderator'] == true){
					$row_layout->AddContentById('is_admin', '<p><span class="label label-info">{{ST:moderator}}</span></p>');
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
			
			$row_layout->AddContentById('user_badges', Users_GetUserBadges($m->sender));
		
			$rows_html .= $row_layout->ReturnView();
		}
	
	if($number_of_records>$rows){
		if($id != null){
			$pagination = Paginate(FORUM_URL.'messages.php?contact='.$id, $page, $number_of_pages, true, 3);
			$layout->AddContentById('pagination', $pagination);
		}else{
			$pagination = Paginate(FORUM_URL.'messages.php', $page, $number_of_pages, false, 3);
			$layout->AddContentById('pagination', $pagination);
		}
	}
	
}else{
	if($id != null){
		$rows_html = '<p>{{ST:no_messages}}</p>';
	}else{
		$rows_html = '<p>{{ST:no_unseen_messages}}</p>';
	}
}

$layout->AddContentById('rows', $rows_html);



$layout->RenderViewAndExit();
