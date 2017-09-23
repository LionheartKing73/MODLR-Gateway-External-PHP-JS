
/*
Prerequisit Variables
 var table_definition 
*/

function fieldForId(table_definition, fieldid) {
	for(var i=0;i<table_definition.fields.length;i++) {
		var field = table_definition.fields[i];
		if( field.id == fieldid ) {
			return field;
		}
	}
	return null;
}

function formClose(form_id) {
	var form = formForId(form_id);
	
	if( form.pageBack ) {
		if( form.pageBack != "" ) {
			//go to the specified page
			var loc = "/activities/view/?id="+id+"&activityid="+activityid+"&serverid="+serverid+"&title="+form.pageBack;
			
			if( window.parent ) {
				window.parent.location =  loc;
			} else {
				window.location = loc;
			}
			return;
		}
	}
	//go to the actiivty homepage
	var id = getParameterByName("id");
	var activityid = getParameterByName("activityid");
	var serverid = getParameterByName("serverid");
	
	if( window.parent ) {
		window.parent.location =  "/activities/view/?id="+id+"&activityid="+activityid+"&serverid="+serverid;
	} else {
		window.location =  "/activities/view/?id="+id+"&activityid="+activityid+"&serverid="+serverid;
	}
		
	return;
}


function formForId(form_id) {
	for(var i=0;i<forms.length;i++) {
		var form = forms[i];
		if( form.id == form_id ) {
			return form;
		}
	}
	return null;
}

function setupForm(form_id) {
	var form = formForId(form_id);
	
	var html = "";
	for(var i=0;i<form.fieldList.length;i++) {
		var fieldObject = form.fieldList[i];
		
		var field = fieldForId(form.table_definition, fieldObject.fieldid);
		var value = null;
		
		var system_name = field.system_name;
		for(var k=0;k<form.record.length;k++) {
			if( form.record[k].field == system_name ) {
				value = form.record[k].value;
				break;
			}
		}
		
		html += fieldCode(form_id, field, fieldObject, value);
	}
	
	html += "<div style='width:100%;text-align:right;'>";
	
	if( form.method == "CREATE" ) {
		html += "<button type='button' class='btn btn-success' style='margin-left:10px;padding-right:20px;padding-left:20px;' onclick=\"formSave('"+form_id+"');\">Add</button>";
	} else {
		html += "<button type='button' class='btn btn-success' style='margin-left:10px;padding-right:20px;padding-left:20px;' onclick=\"formSave('"+form_id+"');\">Save</button>";
	}
	
	if( form.pageBack != "" ) {
		html += "<button type='button' class='btn btn-danger' style='margin-left:3px;' onclick=\"formClose('"+form_id+"');\">Close</button>";
	}
	
	html += "</div>";
	
	
	$("#form"+form_id).html(html);
	
	$('.formatted-text').wysihtml5();

	$( ".clsDateField" ).datepicker({
		dateFormat: "yy-mm-dd"
	});
	
	$( ".clsDateTimeField" ).datepicker({
		dateFormat: "yy-mm-dd hh:MM"
	});
	
	

}

function fieldCode(form_id, field, options, value, preview) {
	var html = '';
	
	html+= '<div class="form-group">';
	html+= '<label class="control-label col-md-2" for="F'+field.id+'">'+field.name+':</label>';
	html+= '<div class="col-md-10">';
	
	
	
	var readonly = "";
	if( options.readonly ) {
		readonly = " DISABLED ";
	}
	
		
	
	if( field.type == "TEXT [512]" ||  field.type == "TEXT [55]" ) {
		
		if( value == null )
			value = "";
		html+= '<input class="form-control" type="text" class="form-control" id="F'+form_id+"_"+field.id+'" name="F'+form_id+"_"+field.id+'" placeholder="" value="'+value+'" '+readonly+'>';
	
	} else if(  field.type == "NUMERIC" ) {
		
		if( value == null )
			value = "0";
		html+= '<input class="form-control" type="text" class="form-control" id="F'+form_id+"_"+field.id+'" name="F'+form_id+"_"+field.id+'" placeholder="" value="'+value+'" '+readonly+'>';
	
	} else if( field.type == "TEXT [25,000]" ) {
		
		if( value == null )
			value = "";
		html+= '<textarea class="form-control" type="text" class="form-control" rows="3" id="F'+form_id+"_"+field.id+'" name="F'+form_id+"_"+field.id+'" '+readonly+'>'+value+'</textarea>';
	
	} else if( field.type == "DATE" ) {
		
		if( value == null )
			value = "";
		html+= '<input type="text" class="form-control clsDateField" id="F'+form_id+"_"+field.id+'" name="F'+form_id+"_"+field.id+'" placeholder="" value="'+value+'" '+readonly+'>';
		
	} else if( field.type == "DATE TIME" ) {
		
		if( value == null )
			value = "";
		html+= '<input type="text" class="form-control clsDateTimeField" id="F'+form_id+"_"+field.id+'" name="F'+form_id+"_"+field.id+'" placeholder="" value="'+value+'" '+readonly+'>';
		
	} else if( field.type == "TEXT FORMATTED [25,000]" ) {
		
		if( value == null )
			value = "";
		if( preview ) {
			html+='<label class="control-label">The input for this field will be a formatted document editor. </label>';
		} else {
			
			if( readonly ) {
				html += value;
			} else {
				html+= '<textarea class="form-control formatted-text" type="text" class="form-control" rows="5" id="F'+form_id+"_"+field.id+'" name="F'+form_id+"_"+field.id+'" '+readonly+'>'+value+'</textarea>';
			}
			
		}
		
	} else if( field.type == "REMOTE IDENTIFIER" ) {
		
		if( value == null )
			value = "";
		html+= '<select class="form-control" id="F'+form_id+"_"+field.id+'" name="F'+form_id+"_"+field.id+'" '+readonly+'>';
		
		if( options.values ) {
			for(var i=0;i<options.values.length;i++){
				var record = options.values[i];
				var identifier_value = record[0].value;
				var descriptive_value = record[1].value;
				
				var selected = "";
				if( value == identifier_value ) {
					selected = " SELECTED";
				}
				
				html+= "<option value='"+identifier_value+"'"+selected+">"+descriptive_value+"</option>";
			}
		}
		
		html+= '</select>';
		
	} else if( field.type == "USER ID" ) {
		
		if( value == null )
			value = "";
			
		html+= '<select class="form-control" id="F'+form_id+"_"+field.id+'" name="F'+form_id+"_"+field.id+'" '+readonly+'>';
		if( field.options ) {
			if( field.options.list ) {
				for(var i=0;i<field.options.list.length;i++) {
					var selected = "";
					if( value == field.options.list[i] ) {
						selected = " SELECTED";
					}
					html+= "<option"+selected+">"+field.options.list[i]+"</option>";
				}
			}
		}
		html+= '</select>';
	
		
	} else if( field.type == "TEXT [LIST]" ) {
		
		if( value == null )
			value = "";
			
		html+= '<select class="form-control" id="F'+form_id+"_"+field.id+'" name="F'+form_id+"_"+field.id+'" '+readonly+'>';
		if( field.options ) {
			if( field.options.list ) {
				for(var i=0;i<field.options.list.length;i++) {
					var selected = "";
					if( value == field.options.list[i] ) {
						selected = " SELECTED";
					}
					html+= "<option"+selected+">"+field.options.list[i]+"</option>";
				}
			}
		}
		html+= '</select>';
	
	} else if( field.type == "FILE ATTACHMENT" ) {
		
		if( value == null )
			value = "";
		if( preview ) {
			html+='<label class="control-label">The input for this field will be a file upload button. </label>';
		} else {
			
			if( value != null || value != "" ) {
				html+= '<input class="form-control" type="file" class="form-control" id="F'+form_id+"_"+field.id+'" name="F'+form_id+"_"+field.id+'" placeholder="" >';
			} else {
				
			}
			
		}
	}
	
	
	
	html+= '</div>';
	html+= '</div>';
	
	return html;
}

var active_form_id = null;
function formSave(form_id) {
	active_form_id = form_id;
	var form = formForId(form_id);

	
	var record = {};
	for(var i=0;i<form.fieldList.length;i++) {
		var fieldObject = form.fieldList[i];
		
		var field = fieldForId(form.table_definition,fieldObject.fieldid);
		record[fieldObject.fieldid] = $("#F"+form_id+"_"+fieldObject.fieldid).val();
		
		if( field.type == "NUMERIC" ) {
			record[fieldObject.fieldid] = record[fieldObject.fieldid].replace(/$/gi,"").replace(/,/gi,"");
		}
		
	}
	
	if( form.primary_id != "" ) {
		var primary_key = form.table_definition.fields[0];
		record[primary_key.id] = form.primary_id;
	}
	
	var id = getParameterByName("id");
	var activityid = getParameterByName("activityid");
	var pageid = form_id;
	var tasks = {"tasks": [
		{"task": "form.post", "id":id, "activityid":activityid, "pageid":pageid,  "record": record }
	]};
	query("collaborator.service",tasks,formSaveCallback);
	
}

function formSaveCallback(data) {
	var form_id = active_form_id;
	var form = formForId(form_id);
	active_form_id = null;
	
	var results = JSON.parse(data);
	var result = results['results'][0].result;
	var message = results['results'][0].message;
	if( message == null ) {
		message = results['results'][0].error;
	}
	
	if( parseInt(result) == 1 ) {
		//redirect as per the form settings.
		if( form.pageSuccess ) {
			if( form.pageSuccess != "" ) {
				//go to the specified page
				
				var loc = "/activities/view/?id="+id+"&activityid="+activityid+"&serverid="+serverid+"&title="+form.pageSuccess;
				
				if( window.parent ) {
					window.parent.location = loc;
				} else {
					window.location = loc;
				}
				return;
			}
		}
		//go to the actiivty homepage
		var id = getParameterByName("id");
		var activityid = getParameterByName("activityid");
		var serverid = getParameterByName("serverid");
		if( window.parent ) {
			window.parent.location =  "/activities/view/?id="+id+"&activityid="+activityid+"&serverid="+serverid;
		} else {
			window.location =  "/activities/view/?id="+id+"&activityid="+activityid+"&serverid="+serverid;
		}
		return;
		
		
	} else {
		$('<div></div>').appendTo('body')
		.html('<div>'+message+'</div>')
		.dialog({
			modal: true,
			title: 'Form Save Failed',
			zIndex: 10000,
			autoOpen: true,
			width: '450',
			resizable: false,
			buttons: {
				Confirm: function () {
					$(this).dialog("close");
				}
			},
			close: function (event, ui) {
				$(this).remove();
			}
		});
	}
}
