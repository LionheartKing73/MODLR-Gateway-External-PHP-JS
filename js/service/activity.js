	
var serviceName = "model.service";
var activity = null;


function edit_user_tags(userid, username) {
	document.getElementById('changeUserLabel').innerHTML = "Collaborator: " + username;
	setDataset(document.getElementById('changeUserLabel'),"userid",userid);
	var user = itemFromArray(activity['users'], "id", userid );
	$("#txtTagsChange").val(user['tags']);
	$('#txtTagsChange').select2();
}

function userFormReset() {
	$("#existing_user_form").css("display","none");
	$("#new_user_form").css("display","none");
	$("#user_form_default").css("display","block");
}

function userFormAddExisting() {
	$("#existing_user_form").css("display","block");
	$("#new_user_form").css("display","none");
	$("#user_form_default").css("display","none");
}

function userFormAddNew() {
	$("#existing_user_form").css("display","none");
	$("#new_user_form").css("display","block");
	$("#user_form_default").css("display","none");
}

function updatePage() {
	var id = getParameterByName("id");
	var activityid = getParameterByName("activityid");
	
	update("/json/activity/?id=" + id + "&activityid=" + activityid, draw_page, draw_page_failed);
}

function listboxValue(listId) {
	var list = document.getElementById(listId);
	if( list.selectedIndex == -1 )
		return "";
	return list.options[list.selectedIndex].value;
}
function setListboxValue(listId, valueToSelect) {
	var list = document.getElementById(listId);
    list.value = valueToSelect;
}

var bEditingPage = false;
var editingTitle = "";
var editingId = "";

function add_page_click() {
	bEditingPage = false;
	$("#btnPageAdd").html("Add");
	
	$("#txtPageRoute").val("unnamed-route");
	
	document.getElementById('txtPageName').value = "";
	setListboxValue('lstPageType',"single");

	setListboxValue('lstView0',"custom");
	setListboxValue('lstView1',"custom");
	setListboxValue('lstView2',"custom");
	
	changedPageType();
}

var loadingTag = false;
var tagDef = null;

function change_tag(tagId) {
	loadingTag = true;
	//
	
	var activityid = getParameterByName("activityid");
	var tasks = {"tasks": [
			{"task": "activity.tag.get", "id":model_detail.id, "activityid":activityid,  "tagid": tagId }
	]};
	bLoading = true;
	query("model.service",tasks,change_tag_Callback);
}


function tag_form_reset() {
	tagDef = null;
	document.getElementById('txtTagName').value = "";
}

function change_tag_Callback(data) {
	var results = JSON.parse(data);
	tagDef = results['results'][0];
	
	document.getElementById('txtTagName').value = tagDef['name'];
	$('#lstTagType').val(tagDef['type']);
	changedTagType();
	
	if( tagDef['dimension'] ) {
		$('#lstTagDimension').val(tagDef['dimension']);
		changedDimension();
	}
	loadingTag = false;
	if( tagDef['dimension'] ) {
		$('#lstTagHeirarchy').val(tagDef['hierarchy']);
		changedHierarchy();
	} else {
		updateTagScreensForm();
	}
	
	//update the panel settings based on the tag type.
	
	
}

function add_tag() {
	
	var name = document.getElementById('txtTagName').value.trim();
	if( name.indexOf(' ') > -1 ) {
		alert("Tags cannot use spaces.");
		return;
	}
	if( name.length == 0 ) {
		alert("Tag name missing.");
		return;
	}
	
	
	//get tag type.
	//get planning screen or element list from here.
	var optionValue = listboxValue('lstTagType');
	
	var tagJson = {"name" : name, "type" : optionValue, "access" : {}};
	
	if( tagDef != null ) {
		tagJson['id'] = tagDef['id'];
	}
	
	if( optionValue == "screen" ) {
		
		var screens = $("div#screens_tag_settings div.checked");
		for(var i=0;i<screens.length;i++) {
			var screenRadio = screens[i];
			if( $(screenRadio).hasClass('checked') ) {
				var itemId = screenRadio.childNodes[0].name;
				var access = getDataset(screenRadio.childNodes[0], "access");
				if( access.trim() != "none" ) {
					tagJson["access"][itemId] = access.trim();
				}
			}
		}
		
		var activityid = getParameterByName("activityid");
		var tasks = {"tasks": [
				{"task": "activity.tag.create.update", "id":model_detail.id, "activityid":activityid,  "definition": tagJson }
		]};
		query("model.service",tasks,add_tag_Callback);
		
	} else {
		var optionDim = listboxValue('lstTagDimension');
		var optionHeir = listboxValue('lstTagHeirarchy');
		tagJson['dimension'] = optionDim;
		tagJson['hierarchy'] = optionHeir;
		
		var screens = $("div#element_tag_settings div.checked");
		for(var i=0;i<screens.length;i++) {
			var screenRadio = screens[i];
			if( $(screenRadio).hasClass('checked') ) {
				var itemId = screenRadio.childNodes[0].name;
				var access = getDataset(screenRadio.childNodes[0], "access");
				if( access.trim() != "none" ) {
					tagJson["access"][itemId] = access.trim();
				}
			}
		}
		
		var activityid = getParameterByName("activityid");
		var tasks = {"tasks": [
				{"task": "activity.tag.create.update", "id":model_detail.id, "activityid":activityid,  "definition": tagJson }
		]};
		query("model.service",tasks,add_tag_Callback);
		
	}
	
}

function add_tag_Callback(data) {
	var results = JSON.parse(data);
	
	notice("Added/Updated Tag","The tag has been added/updated.");
	
	var id = getParameterByName("id");
	var activityid = getParameterByName("activityid");
	update("/json/activity/?action=refresh&id=" + id + "&activityid=" + activityid, draw_page, draw_page_failed);
}

function add_page() {
	var id = getParameterByName("id");
	var activityid = getParameterByName("activityid");
	
	var title = encodeURIComponent(document.getElementById('txtPageName').value);
	
	var route = $("#txtPageRoute").val();
	
	var page_type = listboxValue('lstPageType');
	
	var page_0 = encodeURIComponent(listboxValue('lstView0'));
	var page_1 = encodeURIComponent(listboxValue('lstView1'));
	var page_2 = encodeURIComponent(listboxValue('lstView2'));
	
	var page_1_size = listboxValue('lstViewSize1');
	var page_2_size = listboxValue('lstViewSize2');
	
	var urlAdd = "route=" + route + "&title=" + title + "&page_type=" + page_type + "&page_0=" + page_0 + "&page_1=" + page_1 + "&page_2=" + page_2 + "&page_1_size=" + page_1_size + "&page_2_size=" + page_2_size;
	
	if( bEditingPage ) {
		urlAdd = "action=update_page&" + urlAdd + "&old=" + encodeURIComponent(editingId);
	} else { 
		urlAdd = "action=add_page&" + urlAdd;
	}
	
	update("/json/activity/?" + urlAdd + "&id=" + id + "&activityid=" + activityid, add_planning_screen_callback, draw_page_failed);
}

function add_planning_screen_callback(data) {
	notice("Added/Updated Planning Screen","The planning screen has been added/updated.");
	draw_page(data);
}

function changedDimension() {
	var optionDim = listboxValue('lstTagDimension');
	$("#lstTagHeirarchy").empty()
	for(var i=0;i<model_detail['dimensions'].length;i++) {
		var dim = model_detail['dimensions'][i];
		if( dim['id'] == optionDim ) {
			for(var k=0;k<dim['hierarchies'].length;k++) {
				var opt = new Option(dim['hierarchies'][k]['name'], dim['hierarchies'][k]['id'], false, false);
				$("#lstTagHeirarchy")[0].options.add(opt);
			}
		}
	}
	if( !loadingTag ) {
		changedHierarchy();
	}
}



function changedHierarchy() {
	var optionDim = listboxValue('lstTagDimension');
	var optionHeir = listboxValue('lstTagHeirarchy');
	
	var id = getParameterByName("id");
	update("/json/activity/?action=hierarchy&id=" + id + "&dimension=" + optionDim + "&hierarchy=" + optionHeir ,changedHierarchy_callback,changedHierarchy_callback_fail);
	
}

function changedHierarchy_callback(data) {
	var results = JSON.parse(data);
	var hierarchy = results['results'][0];
	
	var html = "";
	if( hierarchy.root.length > 0 ) {
		for(var i=0;i<hierarchy.root.length;i++) {
			html += recursiveExtractHierarchyLi(hierarchy.root[i],0);
		}
	}
	
	$("#element_tag_settings").html(html);
	
	updateRadioButtons();
	
}
function changedHierarchy_callback_fail(data) {
	//var results = JSON.parse(data);
	notice("Return Hierarchy","Failed to return the selected dimensions hierarchy. Check the selected dimension has hierarchies and try again.");
}


function recursiveExtractHierarchyLi(node, indent) {
	
	var children = node['children'];
	//var str = nodeLi( node['name'] , indent, children.length);
	
	var checkedWrite = "";
	var checkedRead = "";
	var checkedNone = " checked";
	
	if( tagDef != null) {
		if( tagDef['access'][node['name']] ) {
			if( tagDef['access'][node['name']] == "read" ) {
				checkedRead = " checked";
			} else if( tagDef['access'][node['name']] == "write" ) {
				checkedWrite = " checked";
			}
			checkedNone = "";
		}
	}
	
	
	var html = '<div class="form-group" style="height: 36px;">';
	html += '<label class="col-sm-4 control-label" Style="padding-top: 10px;padding-left:' + (10 + (indent * 10)) + 'px;width:180px;overflow-x:hidden;">' + node['name'] + '</label>';
	html += '<div class="col-sm-8 icheck " style="padding-left: 0px;padding-right: 0px;">';
	html += '<div class="flat-green" style="width:105px;"><div class="radio ">';
	if( children.length == 0 ) {
		html += '<input tabindex="3" type="radio"  name="' + node['name'] + '" data-access="write" ' + checkedWrite + '>';
		html += '<label>Write </label>';
	}
	html += '</div></div>';
	
	html += '<div class="flat-red" style="width:105px;"><div class="radio ">';
	html += '<input tabindex="3" type="radio"  name="' + node['name'] + '" data-access="read"  ' + checkedRead + '>';
	html += '<label>Read </label></div></div>';
	html += '<div class="flat-grey" style="width:105px;"><div class="radio ">';
	html += '<input tabindex="3" type="radio"  name="' + node['name'] + '" data-access="none"  ' + checkedNone + '>';
	html += '<label>Hidden </label></div></div>';
	html += '</div></div>';

	//html += '<ul class="tree" style="">';
	for(var i=0;i<children.length;i++) {
		html += recursiveExtractHierarchyLi(children[i],indent+1);
	}	
	//html += '</ul>';
	html += '</li>';
	return html;
}

function changedTagType() {
	var optionValue = listboxValue('lstTagType');
	
	$("#screens_tag_settings").css("display","none");
	$("#element_tag_settings").css("display","none");
	
	$("#element_tag_heirarchy").css("display","none");
	$("#element_tag_dimension").css("display","none");
	
	
	if( optionValue == "screen" ) {
		$("#screens_tag_settings").css("display","block");
	} else {
		$("#element_tag_settings").css("display","block");
		$("#element_tag_heirarchy").css("display","block");
		$("#element_tag_dimension").css("display","block");
	}
	
}

function changedPageType() {
	var optionValue = listboxValue('lstPageType');
	
	if( optionValue.substring(0,5) == "three" ) {
		$("#page_0").css("display","block");
		$("#page_1").css("display","block");
		$("#page_2").css("display","block");
	} else if( optionValue.substring(0,3) == "two" ) {
		$("#page_0").css("display","block");
		$("#page_1").css("display","block");
		$("#page_2").css("display","none");
	} else {
		$("#page_0").css("display","block");
		$("#page_1").css("display","none");
		$("#page_2").css("display","none");
	}
}

function removeFromArray(array, field, match ) {
	for(var i=0;i<array.length;i++){
		if( array[i][field] == match ) {
			array.splice(i, 1);
			return array;
		}
	}
	return array;
}
function itemFromArray(array, field, match ) {
	for(var i=0;i<array.length;i++){
		if( array[i][field] == match ) {
			return array[i];
		}
	}
	return null;
}

function draw_page(data) {
	var results = JSON.parse(data);
	activity = results['results'][1]['activity'];
	
	if( !activity ) 
		return;
	
	//detect if we are on the manage page
	if( $("#lstUsers").length == 0 ) {
		return;
	}
	
	//should only show 'btnLaunch' if the active user is in the collaborators list.
	var existing_users_not_in_activity = full_user_list;
	
	var modelId = getParameterByName("id");
	var activityId = getParameterByName("activityid");
	
	var html = "";
	var users = activity['users'];
	if( users ) {
		
		html += '<table class="table table-striped"><thead><tr><th>Collaborator Name</th><th>Access Tags</th><th class="text-right">Actions</th></tr></thead><tbody>';
	
		for(var i=0;i<users.length;i++){
			var user = users[i];
			
			var userDetails = itemFromArray(full_user_list, "id", user['id'] );
			if( userDetails != null ) {
				var tags = activity['tags'];
				var tagsStr = "";
				if( tags ) {
					for(var k=0;k<user['tags'].length;k++) {
						tag = itemFromArray(tags, "id", user['tags'][k] );
						if( tag != null )
							tagsStr += tag['name'] + ", ";
					}
					if( user['tags'].length > 0 ) {
						tagsStr = tagsStr.substr(0,tagsStr.length-2);
					}
				}
				
				html += "<tr style='font-size:14px;'><td style='width:300px;'>" + userDetails['name'] + " (" + userDetails['email'] + ")</td><td>" + tagsStr + "</td>";
				html += "<td class='text-right'><button class='btn btn-xs btn-success'><a style='color:white;' data-toggle='modal'  href='#changeUsersForm' onclick=\"edit_user_tags('" + userDetails['id'] + "','" + userDetails['name'] + "');\">Change</a></button>&nbsp;<button class='btn btn-xs btn-danger'><a style='color:white;' onclick=\"remove_user('" + userDetails['id'] + "','" + userDetails['name'] + "');\" href='#'>Remove</a></button></td></tr>";
				
				//remove user from existing_users is they are in the activity
				existing_users_not_in_activity = removeFromArray(existing_users_not_in_activity, "name", user['name']);
			} else {
				var id = getParameterByName("id");
				var activityid = getParameterByName("activityid");
	
				update("/json/activity/?action=remove_user&user_id=" + user['id'] + "&id=" + id + "&activityid=" + activityid, remove_user_callback, draw_page_failed);
			}
		}
		html += '</tbody></table>';
	
		$("#contributors_table").html(html);
		$("#contributors_table").css("display","block");
		$("#contributors_none").css("display","none");
	} else {
		$("#contributors_table").css("display","none");
		$("#contributors_none").css("display","block");
	}
	
	//update the list of existing users not in this activity.
	
	
	$("#lstUsers").empty()
	for(var i=0;i<existing_users_not_in_activity.length;i++) {
		var user = existing_users_not_in_activity[i];
		var opt = new Option(user['email'] + " (" + user['name'] + ")", user['id'], false, false);
		$("#lstUsers")[0].options.add(opt);
	}
	
	
	html = "";
	var screens = activity['screens'];
	if( screens ) {
		
		html += '<table class="table table-striped"><thead><tr><th>Screen Name</th><th>Type</th><th class="text-right">Actions</th></tr></thead><tbody>';
	
		for(var i=0;i<screens.length;i++){
			var screen = screens[i];
			
			var type = "Single Screen";
			if( screen['page_type'] == "two_vertical" ) {
				type = "Two pages split vertically";
			} else if( screen['page_type'] == "two_horizontal" ) {
				type = "Two pages split horizontally";
			} else if( screen['page_type'] == "three_vertical" ) {
				type = "Three pages split vertically";
			} else if( screen['page_type'] == "three_horizontal" ) {
				type = "Three pages split horizontally";
			} else if( screen['page_type'] == "hidden" ) {
				type = "Hidden (No Menu Item)";
			}
			
			
			html += "<tr style='font-size:14px;'><td>" + screen['title'] + "</td><td>" + type + "</td>";
			html += "<td class='text-right'><button class='btn btn-xs btn-success' onclick=\"move_screen('" + screen['title'] + "','up');\"><i class='fa fa-arrow-circle-up'></i></button>&nbsp;<button class='btn btn-xs btn-success' onclick=\"move_screen('" + screen['title'] + "','down');\"><i class='fa fa-arrow-circle-down'></i></button>&nbsp;<button class='btn btn-xs btn-success'><a style='color:white;' onclick=\"edit_screen('" + screen['title'] + "');\" data-toggle='modal' href='#addPlanningScreenForm'>Change</a></button>&nbsp;<button class='btn btn-xs btn-danger'><a style='color:white;' onclick=\"remove_screen('" + screen['title'] + "');\" href='#'>Remove</a></button></td></tr>";
		}
		html += '</tbody></table>';
	
		$("#screens_table").html(html);
		$("#screens_table").css("display","block");
		$("#screens_none").css("display","none");
		
		updateTagScreensForm();
		
		
		
	} else {
		$("#screens_table").css("display","none");
		$("#screens_none").css("display","block");
	}
	
	
	$("#txtTags").empty();
	$("#txtTagsAdd").empty();
	$("#txtTagsChange").empty();
	

	html = "";
	var tags = activity['tags'];
	if( tags ) {
		
		html += '<table class="table table-striped"><thead><tr><th>Tag</th><th>Description</th><th class="text-right">Actions</th></tr></thead><tbody>';
	
		for(var i=0;i<tags.length;i++){
			var tag = tags[i];
			
			var type = "Tag";
			
			var opt = new Option(tag['name'] , tag['id'], false, false);
			$("#txtTags")[0].options.add(opt);
			opt = new Option(tag['name'] , tag['id'], false, false);
			$("#txtTagsAdd")[0].options.add(opt);
			opt = new Option(tag['name'] , tag['id'], false, false);
			$("#txtTagsChange")[0].options.add(opt);
			
			
			
			html += "<tr style='font-size:14px;'><td>" + tag['name'] + "</td><td>" + tag['type'] + "</td>";
			html += "<td class='text-right'><button class='btn btn-xs btn-success'><a style='color:white;' onclick=\"change_tag('" + tag['id'] + "');\" data-toggle='modal' href='#addTagForm'>Change</a></button>&nbsp;<button class='btn btn-xs btn-danger'><a style='color:white;' onclick=\"remove_tag('" + tag['id'] + "','" + tag['name'] + "');\" href='#'>Remove</a></button></td></tr>";
		}
		html += '</tbody></table>';
	
		$("#tags_table").html(html);
		$("#tags_table").css("display","block");
		$("#tags_none").css("display","none");
	} else {
		$("#tags_table").css("display","none");
		$("#tags_none").css("display","block");
	}
	
	
	
	
	
	var pages = activity['pages'];
	var bDisplay = false;
	if( pages ) {
		if( pages.length > 0 ) {
			bDisplay = true;
		}
	}	
	
	if( bDisplay ) {
		html = "";
		html += '<table class="table table-striped"><thead><tr><th>Page</th><th>Type</th><th class="text-right">Actions</th></tr></thead><tbody>';
	
		for(var i=0;i<pages.length;i++){
			var page = pages[i];
			
			var page_type = page.type;
			var page_name = "page";
			if( page_type == "form" ) {
				page_name = "form";
			}
			if( page_type == "table" ) {
				page_name = "table";
			}
			
			var url = "/activity/"+page_name+"/?action=open&id=" + modelId + "&activityid=" + activityId + "&page=" + page['pageid'];
			html += "<tr style='font-size:14px;'><td>" + page['name'] + "</td><td>" + page['type'] + "</td>";
			html += "<td class='text-right'><button class='btn btn-xs btn-success' onclick=\"window.location='"+url+"';\"><a style='color:white;'>Change</a></button>&nbsp;<button class='btn btn-xs btn-danger'><a style='color:white;' href='/activity/page/?action=delete&id=" + modelId + "&activityid=" + activityId + "&page=" + page['pageid'] + "'>Remove</a></button></td></tr>";
		}
		html += '</tbody></table>';
	
		$("#pages_table").html(html);
		$("#pages_table").css("display","block");
		$("#pages_none").css("display","none");
	} else {
		$("#pages_table").css("display","none");
		$("#pages_none").css("display","block");
	}
	
	
	if( users == null ) 
		users = [];
	if( screens == null ) 
		screens = [];
	if( tags == null ) 
		tags = [];
	if( pages == null ) 
		pages = [];
	
	$("#collaboratorCount").html(users.length + " Collaborators");
	$("#screenCount").html(screens.length + " Screens");
	$("#tagsCount").html(tags.length + " Access Tags");
	$("#pagesCount").html(pages.length + " Custom Pages");
	
	
	
	$("#txtTags").select2();
	$("#txtTagsAdd").select2();
	$("#txtTagsChange").select2();
	//txtTags
}


function updateTagScreensForm() {
	//screens_tag_settings
	
	
	var screens = activity['screens'];
	if( screens ) {
		
		var html = '';
	
		for(var i=0;i<screens.length;i++){
			var screen = screens[i];
			
			
			var checkedWrite = "";
			var checkedNone = " checked";

			if( tagDef != null) {
				if(  tagDef['access'] ) {
					if( tagDef['access'][screen['id']] ) {
						if( tagDef['access'][screen['id']] == "write" ) {
							checkedWrite = " checked";
						}
						checkedNone = "";
					}
				}
			}

			
			
			html += '<div class="form-group" style="height: 36px;">';
			html += '<label class="col-sm-6 control-label" Style="padding-top: 10px;">' + screen['title'] + '</label>';
			html += '<div class="col-sm-6 icheck ">';
			html += '<div class="flat-green"><div class="radio ">';
			html += '<input tabindex="3" type="radio"  name="' + screen['id'] + '" data-access="write" ' + checkedWrite + '>';
			html += '<label>Visible </label></div></div>';
			html += '<div class="flat-red"><div class="radio ">';
			html += '<input tabindex="3" type="radio"  name="' + screen['id'] + '" data-access="none" ' + checkedNone + '>';
			html += '<label>Hidden </label></div></div>';
			html += '</div></div>';
			
		}
		
		$("#screens_tag_settings").html(html);
		
		updateRadioButtons();
		
		
	} else {
		$("#screens_tag_settings").html("This activity has no screens yet.");
	}
	
}

function updateRadioButtons() {

	$(function(){
		"use strict";
		$('.minimal input').iCheck({
			checkboxClass: 'icheckbox_minimal',
			radioClass: 'iradio_minimal',
			increaseArea: '20%' // optional
		});
	});

	$(function(){
		$('.flat-red input').iCheck({
			checkboxClass: 'icheckbox_flat-red',
			radioClass: 'iradio_flat-red'
		});
	});

	$(function(){
		$('.flat-green input').iCheck({
			checkboxClass: 'icheckbox_flat-green',
			radioClass: 'iradio_flat-green'
		});
	});

	$(function(){
		$('.flat-grey input').iCheck({
			checkboxClass: 'icheckbox_flat-grey',
			radioClass: 'iradio_flat-grey'
		});
	});


}

function draw_page_failed(data) {
	notice("Page Update Failed","Please refresh the page as your session may have expired.");
}


function edit_screen(title) {
	var screens = activity['screens'];
	bEditingPage = true;
	
	for(var i=0;i<screens.length;i++) {
		var screen = screens[i];
		if( screen['title'] == title ) {
			
			document.getElementById('txtPageName').value = screen['title'];
			editingTitle = screen['title'];
			editingId = screen['id'];
			
			$("#txtPageRoute").val(screen['route']);
			
			setListboxValue('lstPageType',screen['page_type']);
			
			setListboxValue('lstView0',screen['page_0']);
			setListboxValue('lstView1',screen['page_1']);
			setListboxValue('lstView2',screen['page_2']);
			
			setListboxValue('lstViewSize1',screen['page_1_size']);
			setListboxValue('lstViewSize2',screen['page_2_size']);
			
			changedPageType();
			
			$("#btnPageAdd").html("Update");
			
			return;
		}
	}
	
	//activity
	
}

function remove_screen(title) {
	$('<div></div>').appendTo('body')
    .html('<div><b>Are you sure you want to remove the "' + title + '" screen from the activity?</b><br/>Note: This action is irreversible.</div>')
    .dialog({
        modal: true,
        title: 'Confirm Action?',
        zIndex: 10000,
        autoOpen: true,
        width: '450',
        resizable: false,
        buttons: {
            Yes: function () {
                
                var id = getParameterByName("id");
				var activityid = getParameterByName("activityid");
	
				update("/json/activity/?action=remove_page&title=" + encodeURIComponent(title) + "&id=" + id + "&activityid=" + activityid, remove_page_callback, draw_page_failed);
	
                $(this).dialog("close");
            },
            No: function () {
                $(this).dialog("close");
            }
        },
        close: function (event, ui) {
            $(this).remove();
        }
    });
}

function move_screen(title, direction) {
	
	var id = getParameterByName("id");
	var activityid = getParameterByName("activityid");

	update("/json/activity/?action=move_page&direction="+direction+"&title=" + encodeURIComponent(title) + "&id=" + id + "&activityid=" + activityid, draw_page, draw_page_failed);

}

function remove_user(userid, username) {
	$('<div></div>').appendTo('body')
    .html('<div><b>Are you sure you want to remove "' + username + '" from the activity?</b><br/>Note: This action does not remove the user from your account with Modlr. To do this you need to access the "Manage Account" page and remove the user there.</div>')
    .dialog({
        modal: true,
        title: 'Confirm Action?',
        zIndex: 10000,
        autoOpen: true,
        width: '450',
        resizable: false,
        buttons: {
            Yes: function () {
                
                var id = getParameterByName("id");
				var activityid = getParameterByName("activityid");
	
				update("/json/activity/?action=remove_user&user_id=" + userid + "&id=" + id + "&activityid=" + activityid, remove_user_callback, draw_page_failed);
	
                $(this).dialog("close");
            },
            No: function () {
                $(this).dialog("close");
            }
        },
        close: function (event, ui) {
            $(this).remove();
        }
    });
}


function remove_tag(tagid, tagname) {
	$('<div></div>').appendTo('body')
    .html('<div><b>Are you sure you want to remove the tag "' + tagname + '" from the activity?</b></div>')
    .dialog({
        modal: true,
        title: 'Confirm Action?',
        zIndex: 10000,
        autoOpen: true,
        width: '450',
        resizable: false,
        buttons: {
            Yes: function () {
                
                var id = getParameterByName("id");
				var activityid = getParameterByName("activityid");
	
				update("/json/activity/?action=remove_tag&tagid=" + tagid + "&id=" + id + "&activityid=" + activityid, remove_tag_callback, draw_page_failed);
	
                $(this).dialog("close");
            },
            No: function () {
                $(this).dialog("close");
            }
        },
        close: function (event, ui) {
            $(this).remove();
        }
    });
}

function update_existing_user() {

	var userId = getDataset(document.getElementById('changeUserLabel'),"userid");
	
	var tags = $("#txtTagsChange").val() || [];
	var tagsList = tags.join( "," )
	
	var id = getParameterByName("id");
	var activityid = getParameterByName("activityid");
	
	update("/json/activity/?action=add_existing&id=" + id + "&activityid=" + activityid + "&userid=" + userId + "&tags=" + tagsList,add_existing_user_callback,add_user_callback_fail);

}

function add_existing_user() {
	var userId = $("#lstUsers").val();
	
	var tags = $("#txtTags").val() || [];
	var tagsList = tags.join( "," )
	
	var id = getParameterByName("id");
	var activityid = getParameterByName("activityid");
	
	update("/json/activity/?action=add_existing&id=" + id + "&activityid=" + activityid + "&userid=" + userId + "&tags=" + tagsList,add_existing_user_callback,add_user_callback_fail);
}

function add_user() {
	var txtName = document.getElementById('txtName').value;
	var txtEmail = document.getElementById('txtEmail').value;
	var txtPhone = document.getElementById('txtPhone').value;
	
	if( txtEmail.indexOf("@") < 0 ) {
		alert("The email address provided does not appear to be valid.");
		return;
	} 
	
	var tags = $("#txtTagsAdd").val() || [];
	var tagsList = tags.join( "|" )
	
	var id = getParameterByName("id");
	var activityid = getParameterByName("activityid");
	
	update("/json/activity/?action=add&id=" + id + "&activityid=" + activityid + "&name=" + txtName + "&email=" + txtEmail + "&phone=" + txtPhone + "&tags=" + tagsList,add_user_callback,add_user_callback_fail);
	
}
function remove_page_callback(data) {
	//var results = JSON.parse(data);
	notice("Removed Screen","The Screen has been removed from the activity");
	draw_page(data);
}

function remove_user_callback(data) {
	//var results = JSON.parse(data);
	notice("Removed Collaborator","The Collaborator has been removed from the activity");
	draw_page(data);
}
function add_existing_user_callback(data) {
	//var results = JSON.parse(data);
	notice("Add/Update Collaborator","The Collaborator has been updated in the activity");
	draw_page(data);
}
function remove_tag_callback(data) {
	//var results = JSON.parse(data);
	notice("Removed Tag","The Tag has been removed from the activity");
	draw_page(data);
}

function add_user_callback(data) {
	//var results = JSON.parse(data);
	notice("Add Collaborator","The Collaborator has been added to the activity");
	//draw_page(data);
	location.reload(true);
}
function add_user_callback_fail(data) {
	//var results = JSON.parse(data);
	notice("Add Collaborator Failed","Failed to add the collaborator. Check your settings and try again.");
}


function update(query, callback_success, callback_error) {
	//table-data
	showLoading();

	$.ajax({
		type: "GET",
		dataType: "text",
        contentType: "application/text; charset=utf-8",
		processData: false,
		url: query
	}).done(function(result) {
		hideLoading();
		callback_success(result);
	}).fail(function(result) {
		hideLoading();
		callback_error(result);
	}).always(function(result) {
	
	});
}

function func_delete() {
	var r=confirm("Are you sure you want to delete this activity? This action is unreversble.");
	if (r==true)
	{
		document.getElementById('formDelete').submit();
	}
}


function save() {

	document.getElementById('testBox').style.display = 'block';	
	document.getElementById('testResult').innerHTML = 'Saving...';

	var name =  document.getElementById('name').value;
	task = "activity.create.update";
	var modelId = getParameterByName("id");
	var activityId = getParameterByName("activityid");
	
	activity.navigation = $("#navigation-show").val();
	activity.route = $("#route").val();
	
	activity['name'] = name;
	
	if( !activity.screens )
		activity.screens = [];

	var tasks = {"tasks": [
		{"task": task,  "id": modelId, "activityid" : activityId , "definition" : activity }
	]};

	if( name.length > 2 ) {
		query(serviceName,tasks,save_callback);
	} else {
		document.getElementById('testResult').innerHTML = '<b>Save failed, the specified name was too short.</b><br/>' + str;
	}
}

function save_callback(data) {
	var results = JSON.parse(data);

	if( parseInt(results['results'][0]['result']) == 1 ) {
		document.getElementById('testResult').innerHTML = '<b>Activity Updated.</b>';
	} else {
		if( results['results'][0]['error'] ) { 
			document.getElementById('testResult').innerHTML = '<b>' + results['results'][0]['error'] + '</b>';
		} else {
			document.getElementById('testResult').innerHTML = '<b>' + results['results'][0]['message'] + '</b>';
		}
	}
}


function upload_file() {
	var formData = new FormData();
	var wipeAllCheck = document.getElementById('wipeAllCheck').checked;
	
	formData.append("file", fileUpload.files[0]);
	formData.append("action", "upload");
	formData.append("wipe", wipeAllCheck );
	
	showLoading();
	var request = new XMLHttpRequest();
	request.open("POST", defaultRequest() + "&action=upload");
	
	request.onload = function(oEvent) {
		if (request.status == 200) {
		  	window.location = window.location + "&abc=123";
		} else {
		  	notice("Upload Failed","Unfortunately there were errors in processing your upload.");
		  	
		  	document.getElementById('uploadForm').style.display = 'block';
		}
	};
	
	request.send(formData);
	
}

function notice(title, text) {
	var unique_id = $.gritter.add({
		title: title,
		text: text,
		image: '/images/logo_square-300x300.png',
		sticky: false,
		time: '1000',
		class_name: 'my-sticky-class'
	});
}


var activeTabOnLoad = localStorage.getItem("Activity.Tab.Active");
if( !activeTabOnLoad ) 
	activeTabOnLoad = 0;
else
	activeTabOnLoad = parseInt(activeTabOnLoad);

// Set active tab on page load
if( activeTabOnLoad > 0 ) {
	$(".tab-pane").removeClass('active');
	$(".tab-pane").removeClass('in');
	var tab = "";
	if( activeTabOnLoad == 0 ) {
		tab = "overview";
	} else if( activeTabOnLoad == 1 ) {
		tab = "contributors";
	} else if( activeTabOnLoad == 2 ) {
		tab = "workviews";
	} else if( activeTabOnLoad == 3 ) {
		tab = "accesstags";
	} else if( activeTabOnLoad == 4 ) {
		tab = "custompages";
	}
	$("#" + tab).addClass('active');
	$("#" + tab).addClass('in');
	
	if( tab == "workviews" )
		tab = "Planning Screens";
	if( tab == "accesstags" )
		tab = "Access Tags";
	if( tab == "custompages" )
		tab = "Custom Pages";
	if( tab == "contributors" )
		tab = "Collaborators";
	
	$("#module_heading").html(tab.toProperCase());
}

// Capture current tab index.  Remember tab index starts at 0 for tab 1.
$('a.tabHeading').click(function(e) {
	curTabIndex = getDataset(this,"index");
	localStorage.setItem("Activity.Tab.Active", curTabIndex);
});


function showLoading(message) {
	if( !message ) 
		message = "Communicating with the data server.";
	
	if( !bLoading ) {
		$( "#dialog-loading" ).dialog({
			open: function(event, ui) { 
				document.getElementById('dialog-loading').parentNode.childNodes[0].childNodes[1].style.display = 'none';
			},
			modal: true,
			buttons: {}
		});
		bLoading = true;
	}
}

function hideLoading() {
	
	$( "#dialog-loading" ).dialog( "close" );
	bLoading = false;
	
}
