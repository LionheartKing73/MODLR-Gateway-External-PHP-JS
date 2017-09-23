
$( document ).ready(function() {
	
	listPrimaryTableChange();
	
	updateFieldSelection();
	updateFieldOrdering();
});



function tableForId(table_id) {
	for(var i=0;i<model_detail.tables.length;i++) {
		if( model_detail.tables[i].id == table_id ) {
			return model_detail.tables[i];
		}
	}
	return null;
}

function savePage() {
	
	var id = getParameterByName("id");
	var activityid = getParameterByName("activityid");
	
	var pageDefinition = {};
	
	if( pageid ) {
		if( pageid != "" ) {
			pageDefinition.pageid = pageid;
		}
	}
	
	pageDefinition.type = "table";
	pageDefinition.style = "NONE";
	pageDefinition.name = $("#title").val();
	pageDefinition.table = definition;
	
	var tasks = {"tasks": [
		{"task": "activity.page.create.update", "id":id, "activityid":activityid, "definition":pageDefinition }
	]};
	query("model.service",tasks,formSaveCallback);
	
}
function formSaveCallback(data) {
	
	var results = JSON.parse(data);
	var result = results['results'][0].result;
	var message = results['results'][0].message;
	if( message == null ) {
		message = results['results'][0].error;
	}
	
	if( result == 1 ) {
		pageid = results['results'][0].pageid;
		
		installTableInDiv(pageid, definition, "table_preview");
		
	} else {
		
		alert(message);
	}
	
	
}

function listPrimaryTableChange() {
	
	var table_id = $("#table").val();
	
	
	//setup routine
	if( !definition.primary ) {
		//initial setup
		
		
		//ordered lists - all include table id and field id but then deviate
		definition.fields = [];		
		definition.ordering = [];	
		definition.filtering = [];
		definition.joins = [];
		definition.actions = [];
		
	} else {
		//check if some configuration has been completed 
		if( definition.primary != table_id ) {
			//wipe contents
			definition.fields = [];		
			definition.ordering = [];	
			definition.filtering = [];
			definition.joins = [];
			definition.actions = [];
		}
	}
	
	//changing the prmary table wipes the definition contents.
	definition.primary = table_id;
	definition.primary_name = $("#table option[value='"+table_id+"']").text()
	
	updateLinksList();
	listActiveTableChange();
}

function makeLink(from_table_id, field) {
	var link = {};
	link.from = from_table_id;
	link.to = field.options.remote_table;
	link.field = field.id;
	link.field_name = field.name;
	
	link.id = link.from + "_" + link.to + "_" + link.field;
	return link;
}

function linksFromAndToTable(table_id) {
	var links = [];
	
	var table = tableForId(table_id);
	//loop through this tables remote identifiers 
	for(var i=0;i<table.fields.length;i++) {
		var field = table.fields[i];
		if( field.type == "REMOTE IDENTIFIER" ) {
			
			links[links.length] = makeLink(table_id, field);
			
		}
	}
	
	//loop through all tables and list remote identifiers to this table.
	//TODO: 
	
	
	return links;
}

function linkInList(list, link_id) {
	for(var i=0;i<list.length;i++) {
		var link = list[i];
		if( link.id == link_id ) {
			return link;
		}
	}
	return null;
}
function fieldInList(list, field_id) {
	for(var i=0;i<list.length;i++) {
		var link = list[i];
		if( link.id == field_id ) {
			return link;
		}
	}
	return null;
}
function fieldExactInList(list, field_id, link_id) {
	for(var i=0;i<list.length;i++) {
		var link = list[i];
		if( link.id == field_id && link.link == link_id) {
			return link;
		}
	}
	return null;
}

function toggleLinkSelection(chk, link_id) {
	
	if( !chk.checked ) {
		
		//remove fields with that link
		for(var i=definition.fields.length-1;i>=0;i--) {
			var fieldItem = definition.fields[i];
			
			if( fieldItem.link == link_id ) {
				definition.fields.splice(i,1);
			}
		}
		
		//remove ordering with that link
		for(var i=definition.ordering.length-1;i>=0;i--) {
			var fieldItem = definition.ordering[i];
			
			if( fieldItem.link == link_id ) {
				definition.ordering.splice(i,1);
			}
		}

		//remove filtering with that link
		for(var i=definition.filtering.length-1;i>=0;i--) {
			var fieldItem = definition.filtering[i];
			
			if( fieldItem.link == link_id ) {
				definition.filtering.splice(i,1);
			}
		}
		
		//remove the link from the joins
		for(var i=0;i<definition.joins.length;i++) {
			var link = definition.joins[i];
			if( link.id == link_id ) {
				definition.joins.splice(i,1);
				break;
			}
		}
		
	} else {
		
		var link = linkInList(optional_links, link_id);
		//make sure its not already in the definition
		if( linkInList(definition.joins, link_id) == null ) {
			definition.joins[definition.joins.length] = link;
		}
	}
	
	//re calculate the available links, fields and ordering
	updateLinksList();
	
	//as when a link is removed so are the possible fields for selection and ordering
	updateFieldSelection();
	updateFieldOrdering();
	listActiveTableChange();
}


var optional_links = [];
function updateLinksList() {
	optional_links = [];
	
	var html = "";
	
	//loop through the primary table remote references
	var table_id = definition.primary;
	var links = linksFromAndToTable(table_id);
	optional_links = optional_links.concat(links);

	//loop through active links and add their remote references
	for(var i=0;i<definition.joins.length;i++) {
		var link = definition.joins[i];
		
		table_id = link.to;
		links = linksFromAndToTable(table_id);
		optional_links = optional_links.concat(links);
	}
	
	//render all the available links
	for(var i=0;i<optional_links.length;i++) {
		var link = optional_links[i];
		
		
		var tableTo = tableForId(link.to);
		var tableFrom = tableForId(link.from);
		
		//check if link is already included in definition
		var selected = "";
		for(var k=0;k<definition.joins.length;k++) {
			var linkCheck = definition.joins[k];
			if( linkCheck.id == link.id ) {
				selected = " CHECKED ";
				break;
			}
		}
		
		var description = "Join the \"" + tableTo.name + "\" Table using \"" + tableFrom.name + "\" field \"" + link.field_name + "\"";
		html += "<input type='checkbox' onclick=\"toggleLinkSelection(this, '"+link.id+"');\" value='' "+selected+" id='link"+link.id+"'/>&nbsp;" + description + "<br/><hr/>";
		
	}
	
	$("#table_joins").html(html);
	
	var active_table = $("#active_table")[0];
	active_table.options.length = 1;
	
	for(var i=0;i<definition.joins.length;i++) {
		var link = definition.joins[i];
		var tableTo = tableForId(link.to);
		
		OptionAdd(active_table, link.id, tableTo.name + " on " + link.field_name);
		
	}
	
}


function listActiveTableChange() {
	var link_id = "";
	var table_id = $("#active_table").val();
	if( table_id == "primary" ) {
		table_id = $("#table").val();
	} else {
		//get the table id from the active link.
		link_id = table_id;
		var link = linkInList(definition.joins, link_id);
		table_id = link.to;
		if( table_id == definition.primary )
			table_id = link.from;
	}
	
	var table = tableForId(table_id);
	
	$("#fields tr").remove(); 
	var html = "";
	
	for(var i=0;i<table.fields.length;i++) {
		var field = table.fields[i];
		
		var seleted = "";
		
		for(var k=0;k<definition.fields.length;k++) {
			var fieldCheck = definition.fields[k];
			if( fieldCheck.id == field.id && link_id == fieldCheck.link ) {
				seleted = " CHECKED ";
			}
		}
	
		var btnFilter = '<button type="button" class="btn btn-info btn-xs" title="Filter this Field" onclick="fieldFilter(\''+field.id+'\');"><i class="fa fa-filter"></i></button>';
		var btnSortAZ = '<button type="button" class="btn btn-info btn-xs" title="Sort Ascending" onclick="fieldOrdering(\'ASC\',\''+field.id+'\');"><i class="fa fa-sort-amount-asc"></i></button>';
		var btnSortZA = '<button type="button" class="btn btn-info btn-xs" title="Sort Descending" onclick="fieldOrdering(\'DESC\',\''+field.id+'\');"><i class="fa fa-sort-amount-desc"></i></button>';
		
		
		html += "<tr>";
		html += "	<td width='45%' style='padding-bottom:3px;'><input type='checkbox' "+seleted+" onclick=\"toggleFieldSelection(this, '"+field.id+"');\"  value='' id='field"+field.id+"'/>&nbsp;"+field.name+"</td>";
		html += "	<td width='35%' style='padding-bottom:3px;'>"+field.type+"</td>";
		html += "	<td width='20%' style='padding-bottom:3px;'>"+btnFilter+btnSortAZ+btnSortZA+"</td>";
		html += "</tr>";
	}
	
	$("#fields").html(html);
	previewUpdate();
	
}

function fieldFilterSave() {
	
	
}

function fieldFilter(field_id) {
	
	var link_id = "";
	var table_id = $("#active_table").val();
	if( table_id == "primary" ) {
		table_id = $("#table").val();
	} else {
		//get the table id from the active link.
		link_id = table_id;
		var link = linkInList(definition.joins, link_id);
		table_id = link.to;
		if( table_id == definition.primary )
			table_id = link.from;
	}
	
	var table = tableForId(table_id);
	var field = fieldInList(table.fields, field_id);
	
	var types = $("#filterType")[0];
	types.options.length = 0;
	
	$("#filterExpressionDiv").css("display","none");
	$("#filterOptionsDiv").css("display","none");
	$("#filterDateTip").css("display","none");
		
	if( field.type == "USER ID CREATOR"  ||  field.type == "USER ID LAST MODIFIED" || field.type == "USER ID"  ) {
		OptionAdd(types, "USER MATCH", "Record User matches the Viewer");
		OptionAdd(types, "NOT USER MATCH", "Record User doesn't matches the Viewer");
		
		$("#filterExpressionDiv").css("display","block");
	} else if( field.type.indexOf("TEXT") > -1  ) {
		OptionAdd(types, "BEGINNING EQUALS", "Begins With");
		OptionAdd(types, "END EQUALS", "Ends With");
		OptionAdd(types, "CONTAINS", "Contains");
		OptionAdd(types, "EQUALS", "Equals");
		
		$("#filterExpressionDiv").css("display","block");
	} else if( field.type.indexOf("NUMERIC") > -1  ) {
		OptionAdd(types, "GREATER", "Greater Than");
		OptionAdd(types, "LESS", "Less Than");
		OptionAdd(types, "GREATER EQUALS", "Greater Than or Equal To");
		OptionAdd(types, "LESS EQUALS", "Less Than or Equal To");
		OptionAdd(types, "EQUALS", "Equals");
		
		$("#filterExpressionDiv").css("display","block");
	} else if( field.type.indexOf("ATTACHMENT") > -1  ) {
		OptionAdd(types, "ATTACHMENT", "Has Attachment");
		OptionAdd(types, "NO ATTACHMENT", "No Attachment");
		
		$("#filterExpressionDiv").css("display","block");
	} else if( field.type.indexOf("TIME") > -1  ) {
		OptionAdd(types, "GREATER", "After Time");
		OptionAdd(types, "LESS", "Before Time");
		OptionAdd(types, "GREATER EQUALS", "After or Equal To");
		OptionAdd(types, "LESS EQUALS", "Before or Equal To");
		OptionAdd(types, "EQUALS", "Equals");
		OptionAdd(types, "HOUR", "Within the Hour");
		OptionAdd(types, "MINUTE", "Within the MINUTE");
		
		$("#filterDateTip").css("display","block");
		$("#filterExpressionDiv").css("display","block");
	} else if( field.type.indexOf("DATE") > -1  ) {
		OptionAdd(types, "GREATER", "After Date");
		OptionAdd(types, "LESS", "Before Date");
		OptionAdd(types, "GREATER EQUALS", "After or Equal To");
		OptionAdd(types, "LESS EQUALS", "Before or Equal To");
		OptionAdd(types, "EQUALS", "Equals");
		OptionAdd(types, "DAY", "Within the Day");
		OptionAdd(types, "MONTH", "Within the Month");
		OptionAdd(types, "YEAR", "Within the Month");
		
		$("#filterDateTip").css("display","block");
		$("#filterExpressionDiv").css("display","block");
	} else if( field.type.indexOf("REMOTE IDENTIFIER") > -1  ) {
		OptionAdd(types, "IN", "Is one of the selected items below");
		OptionAdd(types, "NOT IN", "Is not one of the selected items below");
		OptionAdd(types, "NULL", "Is not Populated");
		OptionAdd(types, "NOT NULL", "Is Populated");
		
		$("#filterOptionsDiv").css("display","block");
	} else {
		
	}
	
	dialog = $("#dialog-filter").dialog({
		autoOpen: true,
		height: 410,
		width: 400,
		title:"Filter Creator - " + field.name,
		modal: true,
		buttons: {
			"Save": fieldFilterSave,
			Cancel: function() {
				dialog.dialog("close");
			}
		},
		close: function() {

		}
	});

}

function OptionAdd(list, val, text) {
	var opt = document.createElement('option');
	opt.value = val;
	opt.innerHTML = text;
	list.appendChild(opt);
}

function fieldOrdering(direction, field_id) {
	
	var link_id = "";
	var table_id = $("#active_table").val();
	if( table_id == "primary" ) {
		table_id = $("#table").val();
	} else {
		//get the table id from the active link.
		link_id = table_id;
		var link = linkInList(definition.joins, link_id);
		table_id = link.to;
		if( table_id == definition.primary )
			table_id = link.from;
	}
	
	var table = tableForId(table_id);
	
	var field = fieldInList(table.fields, field_id);
	//make sure its not already in the definition
	if( fieldExactInList(definition.ordering, field_id, link_id) == null ) {
		
		var link_id = "";
		if( $("#active_table").val() != "primary" ) {
			//if this is not the primary table then we are using a link. To ensure the correct field is added to the query we need to store the link Id of this selection.
			
			var index = $("#active_table")[0].selectedIndex;
			link_id = definition.joins[index-1].id;
		}
		
		definition.ordering[definition.ordering.length] = {"id" : field.id, "table" : table_id, "link" : link_id, "direction" :  direction};
	}
	
	
	updateFieldOrdering();
}

function fieldOrderRemove(liIcon) {
	var li = liIcon.parentNode;
	var id = $(li).data("field");
	var table =  $(li).data("table");
	var link =  $(li).data("link");
	var direction =  $(li).data("direction");
	
	for(var i=0;i<definition.ordering.length;i++) {
		var orderItem = definition.ordering[i];
		
		if( orderItem.id == id && 
			orderItem.table == table && 
			orderItem.link == link ) {
			
			definition.ordering.splice(i,1);
			break;
		}
	}
	
	updateFieldOrdering();
}


function fieldOrderSorted() {
	var ordering = [];
	
	var list = $( "#orders_sortable" )[0];
	for(var i=0;i<list.childNodes.length;i++) {
		var li = list.childNodes[i];
		
		var id = $(li).data("field");
		var table =  $(li).data("table");
		var link =  $(li).data("link");
		var direction =  $(li).data("direction");
		
		ordering[ordering.length] = {"id" : id, "table" : table, "link" : link ,"direction" : direction};
	}
	
	definition.ordering = ordering;
	updateFieldOrdering();
}

function updateFieldOrdering() {
	
	var html = '<ul id="orders_sortable">';
	for(var i=0;i<definition.ordering.length;i++) {
		var field = definition.ordering[i];
		var table = tableForId(field.table);
		var fieldData = fieldInList(table.fields, field.id);
		var direction = field.direction.toLowerCase();
		
		var linkAdd = "";
		if( field.link != "" ) {
			var link = linkInList(definition.joins, field.link);
			linkAdd = " » via " + link.field_name;
		}
		
		html += "<li data-table='"+field.table+"' data-field='"+field.id+"' data-link='"+field.link+"' data-direction='"+field.direction+"'><i class='fa fa-sort-amount-"+direction+"'></i>&nbsp;"+table.name+" » "+fieldData.name+linkAdd+"<i onclick=\"fieldOrderRemove(this);\" class='fa fa-times' style='float:right; font-size:16px;padding:4px;cursor:pointer;'></i></li>";
	}
	html+= "</ul>";
	
	$("#field_ordering").html(html);
	
	$( "#orders_sortable" ).sortable({
        stop:  fieldOrderSorted
		});
    $( "#orders_sortable" ).disableSelection();
}


function toggleFieldSelection(chk, field_id) {
	
	var link_id = "";
	var table_id = $("#active_table").val();
	if( table_id == "primary" ) {
		table_id = $("#table").val();
	} else {
		//get the table id from the active link.
		link_id = table_id;
		var link = linkInList(definition.joins, link_id);
		table_id = link.to;
		if( table_id == definition.primary )
			table_id = link.from;
	}
	
	if( !chk.checked ) {
		
		//remove the link from the joins
		for(var i=0;i<definition.fields.length;i++) {
			var field = definition.fields[i];
			if( field.id == field_id && field.link == link_id ) {
				definition.fields.splice(i,1);
				break;
			}
		}
		
	} else {
		var table = tableForId(table_id);
		
		var field = fieldInList(table.fields, field_id);
		//make sure its not already in the definition
		if( fieldExactInList(definition.fields, field_id, link_id) == null ) {
			definition.fields[definition.fields.length] = {"id" : field.id, "table" : table_id, "link" : link_id, "display" :  field.name };
		}
	}
	
	//re render the fields sortable
	updateFieldSelection();
}


function fieldRemove(liIcon) {
	
	var li = liIcon.parentNode;
	
	var id = $(li).data("field");
	var table =  $(li).data("table");
	var link =  $(li).data("link");
	
	for(var i=0;i<definition.fields.length;i++) {
		var fieldItem = definition.fields[i];
		
		if( fieldItem.id == id && 
			fieldItem.table == table && 
			fieldItem.link == link ) {
			
			definition.fields.splice(i,1);
			break;
		}
	}
	
	updateFieldSelection();
	listActiveTableChange();
}



function fieldSorted(event, ui) {
	var fields = [];
	
	var list = $( "#fields_sortable" )[0];
	for(var i=0;i<list.childNodes.length;i++) {
		var li = list.childNodes[i];
		
		var id = $(li).data("field");
		var table =  $(li).data("table");
		var link =  $(li).data("link");
		var display =  $(li).data("display");
		
		fields[fields.length] = {"id" : id, "table" : table, "link" : link, "display" : display };
	}
	
	definition.fields = fields;
	updateFieldSelection();
}


function updateFieldSelection() {
	
	var html = '<ul id="fields_sortable">';
	for(var i=0;i<definition.fields.length;i++) {
		var field = definition.fields[i];
		var table = tableForId(field.table);
		var fieldData = fieldInList(table.fields, field.id);
		
		var linkAdd = "";
		if( field.link != "" ) {
			var link = linkInList(definition.joins, field.link);
			linkAdd = " » via " + link.field_name;
		}
		
		html += "<li data-table='"+field.table+"' data-field='"+field.id+"' data-display='"+field.display+"' data-link='"+field.link+"'>"+table.name+" » "+fieldData.name+linkAdd+"<i onclick=\"fieldRemove(this);\" class='fa fa-times' style='float:right; font-size:16px;padding:4px;cursor:pointer;'></i></li>";
	}
	html+= "</ul>";
	
	$("#field_selection").html(html);
	
	$( "#fields_sortable" ).sortable({
        stop:  fieldSorted
		});
    $( "#fields_sortable" ).disableSelection();
}

function previewUpdate() {
	var html = "";
	
	$("#form_preview").html(html);
}
