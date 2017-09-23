
var server_id = 0;
var cache = null;

function addTask() {
	$( "#dialog-item" ).dialog({
		autoOpen: true,
		modal: true,
		buttons: {},
		width: 500,
		height: 210,
		zindex: 100001
	});
	taskId = null;
}

function closeTask() {
	$( "#dialog-item" ).dialog( "close" );
}

function btnDelete(taskIdentifier) {
	var tasks = {"tasks": [{"task": "workflow.item.delete", "id":modelid,  "workflowid": workflowid ,  "itemid": taskIdentifier }]};
	query("model.service",tasks,saveTaskCallback);
}
function btnMove(taskIdentifier, dir) {
	var tasks = {"tasks": [{"task": "workflow.item.move", "id":modelid,  "workflowid": workflowid ,  "itemid": taskIdentifier,"direction" : dir }]};
	query("model.service",tasks,saveTaskCallback);
}

function btnEdit(taskIdentifier) {
	addTask();
	taskId = taskIdentifier;
	
	var task = null;
	for(var i=0;i<cache.tasks.length;i++) {
		task = cache.tasks[i];
		if( task.id == taskIdentifier ) {
			break;
		}
	}
	
	
	$("#item_title").val(task.title);
	$("#item_link").val(task.link);
	$("#item_workview").val(task.workview);
	$("#item_process").val(task.process);

	
}


var taskId = null;
function saveTask() {
	var task = null;
	
	var item_title = $("#item_title").val();
	var item_link = $("#item_link").val();
	var item_workview = $("#item_workview").val();
	var item_process = $("#item_process").val();
	
	if( taskId == null ) {
		task = {"task": "workflow.item.create", "id":modelid,  "workflowid": workflowid  };
	} else {
		task = {"task": "workflow.item.update", "id":modelid,  "workflowid": workflowid ,  "itemid": taskId };
	}
	task['title'] = item_title;
	task['link'] = item_link;
	task['workview'] = item_workview;
	task['process'] = item_process;
	
	var tasks = {"tasks": [task]};
		
	query("model.service",tasks,saveTaskCallback);
	
	$( "#dialog-item" ).dialog( "close" );
}
function saveTaskCallback() {
	refreshPage();
}

function renameWorkflowPrompt() {
	$( "#dialog-rename-workflow" ).dialog({
		autoOpen: true,
		modal: true,
		buttons: {},
		width: 537,
		height: 88,
		zindex: 100001
	});
}

function setVariablePrompt() {
	$( "#dialog-variable-workflow" ).dialog({
		autoOpen: true,
		modal: true,
		buttons: {},
		width: 537,
		height: 88,
		zindex: 100001
	});
}

function workflowSetVariable(modelid, workflowid) {
	
	var name = $("#workflowVariable").val();
	window.location = "/workflow/?action=setVariable&id=" + modelid + "&workflowid=" + workflowid + "&variable=" + name;
	
}

function workflowRename(modelid, workflowid) {
	
	var name = $("#workflowNewName").val();
	window.location = "/workflow/?action=rename&id=" + modelid + "&workflowid=" + workflowid + "&name=" + name;
	
}


function deleteWorkflow(id,name) {
	var removeButton = "Delete Workflow: " + name;
	
	document.getElementById('confirmParagraph').innerHTML = "Are you sure you would like to permanently delete the '" + name + "' workflow?";
	
	$( "#dlgDeleteItem" ).dialog({
		resizable: false,
		height:140,
		width:400,
		modal: true,
		title: removeButton,
		buttons: {
			"Delete Object" : function() {
				deleteWorkflowConfirmed(id,name);
				$( this ).dialog( "close" );
			},
			Cancel: function() {
				$( this ).dialog( "close" );
			}
		}
	});
}

function deleteWorkflowConfirmed(id,name) {
	var tasks = null;
	tasks = {"tasks": [
		{"task": "workflow.delete", "id":modelid,  "workflowid": id }
	]};
	query("model.service",tasks,deleteItemConfirmedCallback);
}

function deleteItemConfirmedCallback(data) {
	var results = JSON.parse(data);
	var result = results['results'][0]['result'];
	if( parseInt(result) == 0 ) {
		//failed to remove item.
		
		document.getElementById('confirmParagraph').innerHTML = results['results'][0]['error'];
	
		$( "#dlgDeleteItem" ).dialog({
			resizable: false,
			height:140,
			width:400,
			modal: true,
			title: "Oops! There was a problem with that action",
			buttons: {
				Close: function() {
					$( this ).dialog( "close" );
				}
			}
		});
		
	} else {
		
		window.location = "/model/?id=" + modelid;
	}
}


function update(query, postObject, callback_func) {
	showLoading();
	
	var jsonFinal = postObject;
	
	$.ajax({
		type: "POST",
		dataType: "text",
        contentType: "application/text; charset=utf-8",
		processData: false,
		url: query,
		data: JSON.stringify( jsonFinal )
	}).done(function() {
	
	}).fail(function() {
		
	}).always(function(result) {
		hideLoading();
		callback_func(result);
	});
}

var serviceName = "";
$(document).ready(function(){
	refreshPage();
});


function refreshPage() {
	var task = null;
	tasks = {"tasks": [
			{"task": "workflow.get", "id":modelid,  "workflowid": workflowid }
		]};
	query("model.service",tasks,refreshPageCallback);
}
function refreshPageCallback(data) {
	var result = JSON.parse(data).results[0];
	
	var html = "";
	
	cache = result;
	if( result.tasks.length == 0 ) {
		html += "<tr><td colspan='3'>There are not workflow items yet within this process.</td></tr>";
	} else {
		for(var i=0;i<result.tasks.length;i++) {
			var task = result.tasks[i];
			
			html += "<tr  class='workview-entry'>";
			html += "<td class='workview-name object-row'><img src='/img/icons/16/task--pencil.png'/>&nbsp;<span class='object-name'><b>" + (i+1) + ".</b> " + task.title + "</span></td>";
			html += "<td class='workview-name object-row'><button class='btn btn-xs btn-success' onclick=\"btnMove('" + task.id + "','up');\"><i class='fa fa-arrow-circle-up'></i></button>&nbsp;<button class='btn btn-xs btn-success' onclick=\"btnMove('" + task.id + "','down');\"><i class='fa fa-arrow-circle-down'></i></button>&nbsp;<button class='btn btn-xs btn-success' onclick=\"btnEdit('" + task.id + "')\">Edit</button>&nbsp;<button class='btn btn-xs btn-danger' onclick=\"btnDelete('" + task.id + "');\">Delete</button></td>";
			html += "</tr>";
			
		}
	}
	
	$("#workflow-items").html(html);
	
}

