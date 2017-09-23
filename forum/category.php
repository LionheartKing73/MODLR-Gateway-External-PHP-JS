<?php

require 'includes.php';

if(!Users_IsUserLoggedIn() OR !Users_IsUserAdmin(Users_CurrentUserId())){
	Leave('index.php');
}

$layout = GetPage('category', '{{ST:forums}}');

$layout->AddContentById('breadcrumbs', ' <li><a href="'.FORUM_URL.'">{{ST:home}}</a> <span class="divider">/</span> </li> <li><a href="'.FORUM_URL.'categories.php">{{ST:forums}}</a> <span class="divider">/</span> </li><li class="active">{{ST:edit_forum}}</li>');

if(isset($_GET['id'])){
	$id = intval($_GET['id']);
}else{
	Leave(FORUM_URL.'categories.php');
}

$category = $db->get_row("SELECT * FROM " . TABLES_PREFIX . "categories WHERE id = $id ORDER BY id DESC LIMIT 0,1");

if(isset($_GET['delete'])){
	$db->query("DELETE FROM " . TABLES_PREFIX . "categories WHERE id = " . $id);
	if($_GET['option'] == 'everything'){
		$children_ids = get_category_children_id($id);
		$children_ids[] = $id;
		
		$db->query("DELETE FROM " . TABLES_PREFIX . "categories WHERE id IN (".implode(",", $children_ids).")");
		
		
		$db->query("DELETE FROM " . TABLES_PREFIX . "posts WHERE category_id IN (".implode(",", $children_ids).")");
	}elseif($_GET['option'] == 'assign_to'){
		$db->update(TABLES_PREFIX . "categories", array('parent'=>intval($_GET['new_parent'])), array('parent'=>$id), array("%d"));
		$db->update(TABLES_PREFIX . "posts", array('category_id'=>intval($_GET['new_parent'])), array('category_id'=>$id), array("%d"));
	}
	Leave(FORUM_URL.'categories.php?message=deleted');
}

$layout->AddContentById('id', $category->id);

$categoryQuery = get_nested_categories();
$categories = '';
if($categoryQuery){
	foreach($categoryQuery as $u){
		if($u->id != $id AND $u->parent != $id){
			$categories .= '<option {{ID:selected_category_' . $u->id . '}} value="' . $u->id . '">' . $u->name . '</option>';
		}
	}
	$layout->AddContentById('categories', $categories);
}

if(isset($_POST['submit'])){
	
	$errors = false;
	$values = array();
	$format = array();
	$error_msg = '';
	
	if(isset($_POST['name']) AND $_POST['name'] != ''){
		$layout->AddContentById('name', $_POST['name']);
		$check_name = $db->get_row("SELECT * FROM " . TABLES_PREFIX . "categories WHERE name = '" . $_POST['name'] . "' ORDER BY id DESC LIMIT 0,1");
		if($check_name AND intval($check_name->id) != $id){
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
	
	if(isset($_POST['parent']) AND $_POST['parent'] != ''){
		$layout->AddContentById('selected_category_' . $_POST['parent'], 'selected="selected"');
		$values['parent'] = $_POST['parent'];
		$format[] = "%s";
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
	
	if(!$errors){
		$db->update(TABLES_PREFIX . "categories", $values, array('id'=>$id), $format);
		$layout->AddContentById('alert', $layout->GetContent('alert'));
		$layout->AddContentById('alert_nature', ' alert-success');
		$layout->AddContentById('alert_heading', '{{ST:success}}!');
		$layout->AddContentById('alert_message', '{{ST:the_item_has_been_saved}}');
	}else{
		$layout->AddContentById('alert', $layout->GetContent('alert'));
		$layout->AddContentById('alert_nature', ' alert-error');
		$layout->AddContentById('alert_heading', '{{ST:error}}!');
		$layout->AddContentById('alert_message', $error_msg);
	}
	
}else{
	
	$layout->AddContentById('name', $category->name);
	$layout->AddContentById('description', $category->description);
	if(intval($category->order_by) != 9999){
		$layout->AddContentById('order_by', intval($category->order_by));
	}
	
	if($category->locked == 'y'){
		$layout->AddContentById('locked_state', 'checked="checked"');
	}
	
	$layout->AddContentById('selected_category_' . $category->parent, 'selected="selected"');
}

$layout->RenderViewAndExit();
