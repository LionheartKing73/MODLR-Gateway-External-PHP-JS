
$( document ).ready(function() {
	
	var tasks = {"tasks": [
		{"task": "table.get", "id":model_id, "tableid": table_id }
	]};
	
	
	query("model.service",tasks,table_definition_callback);
	
	list_type_change();
});

var current_definition = {};

function table_definition_callback(data) {
	var results = JSON.parse(data);
	table_definition_draw(results['results'][0]['table']);
}

function table_definition_draw(table_definition) {
	current_definition = table_definition;
	
	var fields = current_definition.fields;
	var html = "";
	
	
	html+="<div class='field_definition field_heading'>";
	
	html+="<center><b>Database: "+table_definition.database+"</b></center><br/>";
	
	
		html+="<table width='100%'>";
			html+="<tr>";
				html+="<td width='30%'>";
					html+="<b>Field Name</b>";
				html+="</td>";
				html+="<td width='30%'>";
					html+="<b>System Name</b>";
				html+="</td>";
				html+="<td width='20%'>";
					html+="<b>Field Type</b>";
				html+="</td>";
				html+="<td width='20%'>";
					html+="<b>Actions</b>";
				html+="</td>";
			html+="</tr>";
		html+="</table>";
	
	html+="</div>";
	
	
	for(var i=0;i<fields.length;i++) {
		var field = fields[i];
		
		html+="<div class='field_definition'>";
		
			html+="<table width='100%'>";
				html+="<tr>";
					html+="<td width='30%' class='field_name'>";
					
						html+=field.name;
					
					html+="</td>";
					html+="<td width='30%' class='field_system_name'>";
					
						html+=field.system_name;
					
					html+="</td>";
					html+="<td width='20%' class='field_type'>";
					
						html+=field.type;
					
					html+="</td>";
					html+="<td width='20%' style='text-align:right;'>";
					
						if( i > 0 ){
							html+='<button class="btn btn-xs btn-success" onclick="field_move(\'up\',\''+field.id+'\',\''+field.name+'\');"><i class="fa fa-arrow-circle-up"></i></button>&nbsp;';
							html+='<button class="btn btn-xs btn-success" onclick="field_move(\'down\',\''+field.id+'\',\''+field.name+'\');"><i class="fa fa-arrow-circle-down"></i></button>&nbsp;';
							html+='<button class="btn btn-xs btn-success" onclick="field_edit(\''+field.id+'\',\''+field.name+'\');"><i class="fa fa-pencil"></i></button>&nbsp;';
							html+='<button class="btn btn-xs btn-danger" onclick="field_remove(\''+field.id+'\',\''+field.name+'\');"><i class="fa fa-trash-o"></i></button>';
						}
					
					html+="</td>";
				html+="</tr>";
			html+="</table>";
		
		html+="</div>";
		
	}
	
	$("#table_definition").html(html);
}

function list_type_change() {
	var selected_value = $("#list_type").val();
	
	if( selected_value == "TEXT [LIST]" ) {
		$("#field_options").css("display","block");
	} else {
		$("#field_options").css("display","none");
	}
	
	if( selected_value == "CALCULATED NUMERIC" || selected_value == "CALCULATED TEXT"  ) {
		$("#field_formula").css("display","block");
	} else {
		$("#field_formula").css("display","none");
	}
	
	if( selected_value == "CALCULATED NUMERIC" || selected_value == "NUMERIC"  ) {
		$("#field_format").css("display","block");
	} else {
		$("#field_format").css("display","none");
	}
	
	if( selected_value == "REMOTE IDENTIFIER"  ) {
		$("#field_remote_table").css("display","block");
	} else {
		$("#field_remote_table").css("display","none");
	}
	
	
	if( selected_value == "FILE ATTACHMENT"  ) {
		$("#field_file_type").css("display","block");
	} else {
		$("#field_file_type").css("display","none");
	}
	
}


function field_add() {

	var options = {};
	var name = $("#txt_new_field").val();
	var type = $("#list_type").val();

	
	var selected_value = $("#list_type").val();
	
	if( selected_value == "TEXT [LIST]" ) {
		var list_str = $("#txt_options").val();
		options.list =  list_str.replace("\r","").split("\n");
	}
	
	if( selected_value == "CALCULATED NUMERIC" || selected_value == "CALCULATED TEXT"  ) {
		options.formula = $("#txt_formula").val();
	}
	
	if( selected_value == "CALCULATED NUMERIC" || selected_value == "NUMERIC"  ) {
		options.format = $("#txt_format").val();
	}
	
	if( selected_value == "REMOTE IDENTIFIER"  ) {
		options.remote_table = $("#list_table").val();
	}
	
	if( selected_value == "FILE ATTACHMENT"  ) {
		options.file_type = $("#list_file_type").val();
	}
	
	
	
	var tasks = {"tasks": [
			{"task": "table.field.add", "id":model_id , "tableid": table_id, "name" : name, "type" : type, "options" : options },
			{"task": "table.get", "id":model_id, "tableid": table_id },
	]};
	
	if( field_editing != null ) {
		tasks.tasks[0].task = "table.field.update";
		tasks.tasks[0].fieldid = field_editing;
	}
	
	query("model.service",tasks,field_add_callback);

}

function field_add_callback(data) {
	var results = JSON.parse(data);
	//report on the success of the operation.
	
	if( results['results'][0]['result'] == 0 ) {
		$("#warning_newfield").html(results['results'][0]['error']);
		$("#warning_newfield").css("display","block");
	} else {
		$("#warning_newfield").css("display","none");
		$("#warning_newfield").html("");
	}
	
	//update the main display table with the updated definition.
	table_definition_draw(results['results'][1]['table']);
}


function field_move(direction,field_id,field_name) {
	$('<div></div>').appendTo('body')
    .html('<div><b>Are you sure you want to move "' + field_name + '" field ' + direction + ' one position?</b></div>')
    .dialog({
        modal: true,
        title: 'Confirm Action?',
        zIndex: 10000,
        autoOpen: true,
        width: '450',
        resizable: false,
        buttons: {
            Yes: function () {
				
				var tasks = {"tasks": [
						{"task": "table.field.move", "id":model_id , "tableid": table_id, "fieldid" : field_id , "direction" : direction },
						{"task": "table.get", "id":model_id, "tableid": table_id },
				]};
				
				query("model.service",tasks,field_remove_callback);
				
				
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

function field_remove(field_id,field_name) {
	$('<div></div>').appendTo('body')
    .html('<div><b>Are you sure you want to remove "' + field_name + '" field this table?</b><br/>Note: This action is not reversable.</div>')
    .dialog({
        modal: true,
        title: 'Confirm Action?',
        zIndex: 10000,
        autoOpen: true,
        width: '450',
        resizable: false,
        buttons: {
            Yes: function () {
				
				var tasks = {"tasks": [
						{"task": "table.field.remove", "id":model_id , "tableid": table_id, "fieldid" : field_id },
						{"task": "table.get", "id":model_id, "tableid": table_id },
				]};
				
				query("model.service",tasks,field_remove_callback);
				
				
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


function field_remove_callback(data) {
	var results = JSON.parse(data);
	//report on the success of the operation.
	
	if( results['results'][0]['result'] == 0 ) {
		$("#warning_newfield").html(results['results'][0]['error']);
		$("#warning_newfield").css("display","block");
	} else {
		$("#warning_newfield").css("display","none");
		$("#warning_newfield").html("");
	}
	
	//update the main display table with the updated definition.
	table_definition_draw(results['results'][1]['table']);
}

var field_editing = null;

function field_new() {
	$("#heading_field").html("Add a New Field");
	
	//remove the current field selection
	field_editing = null;
	
	//set name to ""
	$("#txt_new_field").val("");
	
	//remove error message
	$("#warning_newfield").css("display","none");
	$("#warning_newfield").html("");
	
}

function fieldForId(fieldid) {
	for(var i=0;i<current_definition.fields.length;i++) {
		var field = current_definition.fields[i];
		if( field.id == fieldid ) {
			return field;
		}
	}
	return null;
}

function field_edit(field_id, field_name) {
	$("#heading_field").html("Update Field: " + field_name);
	field_editing = field_id;
	
	var field = fieldForId(field_id);
	
	$("#txt_new_field").val(field.name);
	$("#txt_system_field").val(field.system_name);
	$("#list_type").val(field.type).change();
	
	var options = field.options;
	
	
	if( field.type == "CALCULATED NUMERIC" || field.type == "CALCULATED TEXT"  ) {
		$("#txt_formula").val(options.formula);
	}
	
	if( field.type == "CALCULATED NUMERIC" || field.type == "NUMERIC"  ) {
		$("#txt_format").val(options.format);
	}
	
	if( field.type == "REMOTE IDENTIFIER"  ) {
		$("#list_table").val(options.remote_table).change();
	}
	
	if( field.type == "FILE ATTACHMENT"  ) {
		$("#list_file_type").val(options.file_type).change();
	}
	
	if( field.type == "TEXT [LIST]"  ) {
		var str = "";
		for(var i=0;i<options.list.length;i++) {
			str += options.list[i] + "\r\n";
		}
		str = str.substr(0,str.length-2);
		$("#txt_options").val(str);
		
	}
	
}



function table_delete() {
	$('<div></div>').appendTo('body')
    .html('<div><b>Are you sure you want to delete the table "' + table_name + '"?</b><br/>Note: This action is not reversable.</div>')
    .dialog({
        modal: true,
        title: 'Confirm Action?',
        zIndex: 10000,
        autoOpen: true,
        width: '450',
        resizable: false,
        buttons: {
            Yes: function () {
				
				var tasks = {"tasks": [
						{"task": "table.delete", "id":model_id , "tableid": table_id },
				]};
				
				query("model.service",tasks,table_delete_callback);
				
				
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



function table_delete_callback(data) {
	var results = JSON.parse(data);
	//report on the success of the operation.
	
	if( results['results'][0]['result'] == 0 ) {
		$("#warning_newfield").html(results['results'][0]['error']);
		$("#warning_newfield").css("display","block");
	} else {
		window.location = '/model/?id=' + model_id;
	}
	
}

