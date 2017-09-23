<?php

require 'includes.php';

if(!Users_IsUserLoggedIn() OR !Users_IsUserAdmin(Users_CurrentUserId())){
	Leave(FORUM_URL);
}

$layout = GetPage('categories', '{{ST:forums}}');

$layout->AddContentById('breadcrumbs', ' <li><a href="'.FORUM_URL.'">{{ST:home}}</a> <span class="divider">/</span> </li><li class="active">{{ST:forums}}</li>');

if(isset($_GET['page'])){
	$page = intval($_GET['page']);
}else{
	$page = 1;
}
$rows = ROWS_PER_PAGE;

$categoryQuery = get_nested_categories();
$categories = '';
if($categoryQuery){
	foreach($categoryQuery as $u){
		$categories .= '<option {{ID:selected_category_' . $u->id . '}} value="' . $u->id . '">' . $u->name . '</option>';
	}
	$layout->AddContentById('categories', $categories);
}

if(isset($_GET['message']) AND $_GET['message'] != ''){
	
	if($_GET['message'] == 'deleted'){
		$layout->AddContentById('alert', $layout->GetContent('alert'));
		$layout->AddContentById('alert_nature', ' alert-success');
		$layout->AddContentById('alert_heading', '{{ST:success}}!');
		$layout->AddContentById('alert_message', '{{ST:the_item_has_been_deleted}}');
	}
}

if(isset($_POST['submit'])){
	$errors = false;
	$values = array();
	$format = array();
	$error_msg = '';
	
	if(isset($_POST['name']) AND $_POST['name'] != ''){
		$layout->AddContentById('name', $_POST['name']);
		$check_name = $db->get_row("SELECT * FROM " . TABLES_PREFIX . "categories WHERE name = '" . $_POST['name'] . "' ORDER BY id DESC LIMIT 0,1");
		if($check_name){
			$errors = true;
			$error_msg .=  '{{ST:name_already_in_use}} ';
		}else{
			$values['name'] = $_POST['name'];
			$format[] = "%s";
		}
	}else{
		$errors = true;
		$error_msg .= '{{ST:name_required}} ';
	}
	
	if(isset($_POST['order_by']) AND intval($_POST['order_by']) != 0){
		$layout->AddContentById('order_by', $_POST['order_by']);
		$values['order_by'] = intval($_POST['order_by']);
		$format[] = "%d";
	}else{
		$values['order_by'] = 9999;
		$format[] = "%d";
	}
	
	if(isset($_POST['description']) AND $_POST['description'] != ''){
		$layout->AddContentById('description', $_POST['description']);
		$values['description'] = $_POST['description'];
		$format[] = "%s";
	}else{
		$values['description'] = '';
		$format[] = "%s";
	}
	
	if(isset($_POST['locked'])){
		$layout->AddContentById('locked_state', 'checked="checked"');
		$values['locked'] = 'y';
		$format[] = "%s";
	}else{
		$values['locked'] = 'n';
		$format[] = "%s";
	}
	
	if(isset($_POST['parent']) AND $_POST['parent'] != ''){
		$layout->AddContentById('selected_category_' . $_POST['parent'], 'selected="selected"');
		$values['parent'] = $_POST['parent'];
		$format[] = "%s";
	}
	
	if(!$errors){
		
		if($db->insert(TABLES_PREFIX . "categories", $values, $format)){
			$layout->AddContentById('alert', $layout->GetContent('alert'));
			$layout->AddContentById('alert_nature', ' alert-success');
			$layout->AddContentById('alert_heading', '{{ST:success}}!');
			$layout->AddContentById('alert_message', '{{ST:the_item_has_been_saved}}');
		}else{
			$layout->AddContentById('alert', $layout->GetContent('alert'));
			$layout->AddContentById('alert_nature', ' alert-error');
			$layout->AddContentById('alert_heading', '{{ST:error}}!');
			$layout->AddContentById('alert_message', '{{ST:unknow_error_try_again}}');
		}
	}else{
		$layout->AddContentById('alert', $layout->GetContent('alert'));
		$layout->AddContentById('alert_nature', ' alert-error');
		$layout->AddContentById('alert_heading', '{{ST:error}}!');
		$layout->AddContentById('alert_message', $error_msg);
	}
}


$offset = ($page - 1) * $rows;
$categories = get_nested_categories($offset, $rows);
$number_of_records = count($db->get_results("SELECT * FROM " . TABLES_PREFIX . "categories" ));
$number_of_pages = ceil( $number_of_records / $rows );

$rows_html = '';
if($categories){
	foreach($categories as $category){
		$row_layout = new Layout('html/','str/');
		$row_layout->SetContentView('categories-rows');
		$row_layout->AddContentById('id', $category->id);
		$row_layout->AddContentById('name', $category->name);
		
		if(intval($category->order_by) != 9999){
			$row_layout->AddContentById('order_by', intval($category->order_by));
		}
		
		$quizzes = count($db->get_results("SELECT * FROM " . TABLES_PREFIX . "posts WHERE category_id = " . intval($category->id) ));
		$row_layout->AddContentById('posts', $quizzes);
		
		$rows_html .= $row_layout-> ReturnView();
	}
	
	if($number_of_records>$rows){
		$pagination = Paginate('categories.php', $page, $number_of_pages, false, 3);
		$layout->AddContentById('pagination', $pagination);
	}
	
}else{
	$rows_html = '<tr><td colspan="3">{{ST:no_items}}</td></tr>';
}



$layout->AddContentById('rows', $rows_html);



$layout->RenderViewAndExit();
