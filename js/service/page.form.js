
$( document ).ready(function() {
	listTableChange();
});


function savePage() { 
	document.getElementById("page_contents_prior").innerHTML = editor_prior.val();
	document.getElementById("page_contents_post").innerHTML = editor_post.val();
	
	if( document.getElementById("page_contents_prior").innerHTML == "" ) {
		document.getElementById("page_contents_prior").innerHTML = "&nbsp;";
	}
	
	if( document.getElementById("page_contents_post").innerHTML == "" ) {
		document.getElementById("page_contents_post").innerHTML = "&nbsp;";
	}
	
	$("#fields").val(JSON.stringify(fieldList));
	
	document.pageUpdateForm.submit();
}


function listTableChange() {
	
	var table_id = $("#table").val();
	
	var tasks = {"tasks": [
		{"task": "table.get", "id":model_id, "tableid": table_id }
	]};
	
	query("model.service",tasks,table_definition_callback);

}


function table_definition_callback(data) {
	var results = JSON.parse(data);
	table_definition = results['results'][0]['table'];
	var fields = table_definition.fields;
	
	var html = "<table style='width:100%;'>";
	
	html += "<tr>";
	html += "<td><b>Include</b></td>";
	html += "<td><b>Field Name</b></td>";
	html += "<td><b>Data Type</b></td>";
	html += "<td><b>Options</b></td>";
	html += "</tr>";
	
	
	for(var i=0;i<fields.length;i++) {
		var field = fields[i];

		html += '<tr>';
		
		if( field.type == "USER ID CREATOR"  ||  field.type == "DATE TIME CREATED" ||  
			field.type == "CALCULATED NUMERIC"   ||  field.type == "CALCULATED TEXT"  ||  
			field.type == "USER ID LAST MODIFIED"   ||  field.type == "UNIQUE IDENTIFIER" ) {
			
			//automatic fields cannot be added to the form.
			html += '<td><i>Auto</i></td>';
		} else {
			html += '<td><input type="checkbox" id="'+field.id+'" name="'+field.id+'" value="'+field.id+'" onclick="fieldAdd(this);"></td>';
		}
		
		
		html += '<td>' + field.name + '</td>';
		html += '<td>' + field.type + '</td>';
		
		//fa-lock
		//fa-unlock-alt
		$iconLock = '<button class="btn btn-xs btn-success" type="button" onclick="toggle_lock(this, \''+field.id+'\');"><i class="fa fa-lock readonly_'+field.id+'"></i></button>';
		
		if( field.type == "REMOTE IDENTIFIER" ) {
			$iconLock += '&nbsp;<button class="btn btn-xs btn-success" type="button" onclick="configure(this, \''+field.id+'\');"><i class="fa fa-cog"></i></button>';
		}
		
		html += '<td><div class="options_'+field.id+'">' + $iconLock  + '</div></td>';
		
		html += '</tr>';
	
	}
	
	html += "</table>";
	
	$("#table_fields").html(html);
	previewUpdate();
}

function toggle_lock(obj, fieldId) {
	var icon = obj.childNodes[0];
	var locked = false;
	if( $(icon).hasClass('fa-lock') ) {
		$(icon).removeClass('fa-lock');
		$(icon).addClass('fa-unlock-alt');
		locked = true;
	} else {
		$(icon).addClass('fa-lock');
		$(icon).removeClass('fa-unlock-alt');
	}
	
	for(var i=fieldList.length-1;i>=0;i--) {
		if( fieldList[i].fieldid == fieldId ){ 
			fieldList[i].readonly = locked;
		} 
	}
	
	previewUpdate();;
}

function fieldAdd(obj) {
	//fieldList
	
	var fieldId = obj.id;
	
	if( obj.checked ) {
		//add id to list
		fieldList[fieldList.length] = {"fieldid":fieldId};
	} else {
		//remove id from 
		for(var i=fieldList.length-1;i>=0;i--) {
			if( fieldList[i].fieldid == fieldId ){ 
				fieldList.splice(i, 1);
			}
		}
		
	}
	
	previewUpdate();
}


function previewUpdate() {
	var fields = table_definition.fields;
	
	var html = "";
	
	
	for(var i=0;i<fields.length;i++) {
		var field = fields[i];
		$(".options_"+field.id).css("display","none");
	}
	
	for(var i=0;i<fieldList.length;i++) {
		var fieldObject = fieldList[i];
		
		var field = fieldForId(table_definition, fieldObject.fieldid);
		
		$(".options_"+field.id).css("display","block");
		
		var checkbox = $("#" + field.id);
		checkbox.prop( "checked" , "checked");
		
		var iconReadonly = ".readonly_" + field.id;
		if( fieldObject.readonly ) {
			$(iconReadonly).removeClass('fa-lock');
			$(iconReadonly).addClass('fa-unlock-alt');
		} else {
			$(iconReadonly).addClass('fa-lock');
			$(iconReadonly).removeClass('fa-unlock-alt');
		}
			
		html += fieldCode( form_id, field, fieldObject, "");
		
	}
	
	$("#form_preview").html(html);
}
