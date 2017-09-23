var lastData = {};

var current_field = null;


var filters = [];
var pageSetting = 1;


var filterDescriptions = {};
filterDescriptions["begins"] = "Begins with";
filterDescriptions["contains"] = "Contains";
filterDescriptions["ends"] = "Ends with";
filterDescriptions["greater"] = "Is Greater Than";
filterDescriptions["less"] = "Is Less Than";
filterDescriptions["equals"] = "Equals";
filterDescriptions["notequals"] = "Does Not Equal";

function selectHeading(heading) {
	current_field = heading;
	$("#filter-modal-heading").html("Filter Dataset by " + heading);
	
	//dataType
}

function add_filter() {
	var lstFilterType = document.getElementById('lstFilterType');
	var filterType = lstFilterType.options[lstFilterType.selectedIndex].value;
	var filterExpression = document.getElementById('txtFilterExpression').value;
	
	filters[filters.length] = {"mode" : filterType, "column" : current_field, "expression" : filterExpression};
	update(defaultRequest(),[],update_process);
	current_field = null;
}

function removeFilter(filterIndex) {
	filters.splice(filterIndex,1);
	update(defaultRequest(),[],update_process);
}

function updateFiltersView() {
	var html = "";
	
	for(var i=0;i<filters.length;i++) {
		var filter = filters[i];
		
		var text = "Where: '" + filter['column'] + "' " + filterDescriptions[filter['mode']] + " '" + filter['expression'] + "'.";
		
		html += '<div class="btn-group" style="height:34px;"><span><span class="btn btn-xs btn-grey" style="top: -2px;position: relative;margin:5px;" onclick="" type="button"><i class="ico-close" style="font-size: 9px;" onclick="removeFilter(' + i + ')"></i> '+text+'</span></span></div>';
		
	}
	
	$(".filter-view").html(html);
}

$(function() {
	
	update(defaultRequest(),[],update_process);
	
	if( getParameterByName("action") == "upload" ) {
		var tableName = getParameterByName("table");
		if( tableName != "" ) 
			notice("Upload Successful","The data you have just uploaded has been imported successfully into the "+tableName+" table.");
		else
			notice("Upload Successful","The data you have just uploaded has been imported successfully creating a new table.");
	} else if( getParameterByName("action") == "rename" ) {
		notice("Rename Successful","The selected table has been renamed.");
	} else if( getParameterByName("action") == "drop" ) {
		notice("Delete Successful","The selected table has been removed.");
	}
	
});

function rename_table() {
	var newName = document.getElementById('txtNewTableName').value;
	
	showLoading();
	var request = new XMLHttpRequest();
	request.open("GET", defaultRequest() + '&action=rename&table=' + table + "&new=" + newName );
	
	request.onload = function(oEvent) {
		if (request.status == 200) {
		  	window.location = '/datastore/?action=rename&table=' + newName;
		} else {
		  	notice("Upload Failed","Unfortunately there were errors in processing your upload.");
		  	
		  	document.getElementById('renameForm').style.display = 'block';
		  	update(defaultRequest(),[],update_process);
		}
	};
	
	request.send();
}

function defaultRequest() {
	return "/json/datastore/?table=" + table + "&page=" + pageSetting
}

function update_page(pageNo) { 
	pageSetting = pageNo;
	update(defaultRequest(),[],update_process);
	
}

function update_page_box(index) {
	var pageToLoad = document.getElementById('pageNumberBox' + index).value;
	update_page(pageToLoad);
}

function notice(title, text) {
	var unique_id = $.gritter.add({
		title: title,
		text: text,
		image: '/images/logo_square-300x300.png',
		sticky: true,
		time: '',
		class_name: 'my-sticky-class'
	});
}	


function upload_file() {
	var formData = new FormData();
	var lstAction = document.getElementById('lstTables');
	var tableName = lstAction.options[lstAction.selectedIndex].value;
	
	var wipeAllCheck = document.getElementById('wipeAllCheck').checked;
	
	formData.append("file", fileUpload.files[0]);
	formData.append("action", "upload");
	formData.append("table", tableName );
	formData.append("wipe", wipeAllCheck );
	
	
	
	showLoading();
	var request = new XMLHttpRequest();
	request.open("POST", defaultRequest() + "&action=upload");
	
	request.onload = function(oEvent) {
		if (request.status == 200) {
		  	window.location = '/datastore/?action=upload&table=' + tableName;
		} else {
		  	notice("Upload Failed","Unfortunately there were errors in processing your upload.");
		  	
		  	document.getElementById('uploadForm').style.display = 'block';
		  	update(defaultRequest(),[],update_process);
		}
	};
	
	request.send(formData);
	
}


function downloadSample() {
	window.location = '/json/datastore/?action=sample&table=' + table;
	
}

function addData() {
	$('<div></div>').appendTo('body')
    .html('<div><p>Enter the new data to the fields below and press the save button to commit the record.</p><div id="data-editor" class="modal-body"></div></div>')
    .dialog({
        modal: true,
        title: 'Add Record:',
        zIndex: 10000,
        autoOpen: true,
        width: '500',
        height: '450',
        resizable: false,
        buttons: {
            Save: function () {
                
                var id = $("#data-editor").data("record-id");
                var fields = $(".add-field");
                
                var fieldValues = [];
                for(var i=0;i<fields.length;i++) {
                	var field = fields[i];
                	fieldValues[fieldValues.length] = {"field" : getDataset(field,"field"), "value" : field.value};
                }
               
                var updates = {"action" : "insert","values" :fieldValues };
                update("/json/datastore/?table=" + table + "&page=" + pageSetting,[updates],update_process);
                
                
                $(this).dialog("close");
            },
            Close: function () {
                $(this).dialog("close");
            }
        },
        close: function (event, ui) {
            $(this).remove();
        }
    });
    
    var rows = lastData['rows'];
    var headings = lastData['headings'];
    var html = '<form class="form" role="form">';
    
    for(var i=1;i<headings.length;i++) {
		html += '<div class="form-group">';
		html += '<label for="input'+i+'">' + headings[i]['name'] + '</label>';
		html += '<input type="text" data-field="' + headings[i]['name'] + '" class="form-control add-field" id="input' + i + '" placeholder="' + headings[i]['name'] + '" value=""/>';
		html += '</div>';
	}
    
    html += '</form>';
    $("#data-editor").css("height","280px");
    $("#data-editor").css("overflow-y","scroll");
    $("#data-editor").html(html);
    $("#data-editor").data("record-id", "");
    
}

function editData(cell) {
	$('<div></div>').appendTo('body')
    .html('<div><p>Enter amendments to the fields below and press the save button to commit the changes.</p><div id="data-editor" class="modal-body"></div></div>')
    .dialog({
        modal: true,
        title: 'Edit Record:',
        zIndex: 10000,
        autoOpen: true,
        width: '500',
        height: '450',
        resizable: false,
        buttons: {
            Save: function () {
                
                var id = $("#data-editor").data("record-id");
                var fields = $(".edit-field");
                
                var fieldValues = [];
                for(var i=0;i<fields.length;i++) {
                	var field = fields[i];
                	fieldValues[fieldValues.length] = {"field" : getDataset(field,"field"), "value" : field.value};
                }
               
                var updates = {"action" : "update","id" : id,"values" :fieldValues };
                update("/json/datastore/?table=" + table + "&page=" + pageSetting,[updates],update_process);
                
                
                $(this).dialog("close");
            },
            Close: function () {
                $(this).dialog("close");
            }
        },
        close: function (event, ui) {
            $(this).remove();
        }
    });
    
    var idStr = getDataset(cell.parentNode,"id");
    
    var rows = lastData['rows'];
    var headings = lastData['headings'];
    var html = '<form class="form" role="form">';
    
    for(var y=0;y<rows.length;y++) {
		if( rows[y]['id'] == idStr ) {
			
			for(var i=1;i<rows[y]['data'].length;i++) {
				html += '<div class="form-group">';
				html += '<label for="input'+i+'">' + headings[i]['name'] + '</label>';
				html += '<input type="text" data-field="' + headings[i]['name'] + '" class="form-control edit-field" id="input' + i + '" placeholder="' + headings[i]['name'] + '" value="' + rows[y]['data'][i] + '"/>';
				html += '</div>';
			}
			
		}
	}
    
    html += '</form>';
    $("#data-editor").css("height","280px");
    $("#data-editor").css("overflow-y","scroll");
    $("#data-editor").html(html);
    
    $("#data-editor").data("record-id", idStr);
    
}

function reload_page(results) {
	if( results.alert ) {
		alertBox( results.alert , "Action Result");
	} else {
		window.location = '/datastore/';
	}
}

function reload_page_delete_table(results) {
	if( results.alert ) {
		alertBox( results.alert , "Action Result");
	} else {
		window.location = '/datastore/?action=drop';
	}
}

function delete_table() {
	var txtPassword = "<center><input type='password' id='delete_table_pwd' name='delete_table_pwd' placeholder='password' value=''/></center>";
	$('<div></div>').appendTo('body')
    .html('<div><b>Are you sure you want to delete this table?</b><br/>This action is irreversible.<br/><br/>Due to the serious nature of this action please verify your intent by providing your login password below:<br/>'+txtPassword+'</div>')
    .dialog({
        modal: true,
        title: 'Confirm Action?',
        zIndex: 10000,
        autoOpen: true,
        width: '350',
        resizable: false,
        buttons: {
            Yes: function () {
                
                var updates = {"action" : "delete","password" : md5($("#delete_table_pwd").val())};
                update("/json/datastore/?table=" + table + "&page=" + pageSetting,[updates],reload_page_delete_table);
                
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

function remove_selected() {
	$('<div></div>').appendTo('body')
    .html('<div><b>Are you sure you want to remove these rows of data?</b><br/>This action is irreversible.</div>')
    .dialog({
        modal: true,
        title: 'Confirm Action?',
        zIndex: 10000,
        autoOpen: true,
        width: 'auto',
        resizable: false,
        buttons: {
            Yes: function () {
                
                var items = $(".custom-check");
                var ids = [];
                for(var i=0;i<items.length;i++) {
                	if( $(items[i]).hasClass("fa-square") ) {
                		ids[ids.length] = getDataset(items[i],"id");
                	}
                }
                if( ids.length > 0 ) {
                	var updates = {"action" : "remove","items" : ids};
                	update("/json/datastore/?table=" + table + "&page=" + pageSetting,[updates],update_process);
                }
                
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


function remove_all() {
	var txtPassword = "<center><input type='password' id='delete_rows_pwd' name='delete_rows_pwd' placeholder='password' value=''/></center>";
	$('<div></div>').appendTo('body')
    .html('<div><b>Are you sure you want to remove <u>all</u> rows of data?</b><br/>This action is irreversible.<br/><br/>Due to the serious nature of this action please verify your intent by providing your login password below:<br/>'+txtPassword+'</div>')
    .dialog({
        modal: true,
        title: 'Confirm Action?',
        zIndex: 10000,
        autoOpen: true,
        width: '350',
        resizable: false,
        buttons: {
            Yes: function () {
                
                var updates = {"action" : "remove_all","password" : md5($("#delete_rows_pwd").val())};
                update("/json/datastore/?table=" + table + "&page=1",[updates],update_process);
                
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


function update_process(data) {
	var results = JSON.parse(data);
	lastData = results;
	
	var headings = results['headings'];
	var rows = results['rows'];
	var count = results['count'];
	var page = results['page'];
	var size = results['size'];
	
	var html = "<table  class='display table table-bordered table-striped'>";
	
	html += "<thead>";
	html += "<th style='width:25px;' class='read-only'>Actions</th>";
	for(var i=0;i<headings.length;i++) {
		html += "<th><a data-toggle='modal' href='#filterForm' onclick='selectHeading(\"" + headings[i]['name'] + "\");'>" + headings[i]['name'] + "</a></th>";
	}
	html += "</thead>";
	
	
	
	for(var y=0;y<rows.length;y++) {
		html += "<tr data-id='"+rows[y]['id']+"'>";
		html += "<td class='read-only'><i onclick='toggle(this);' data-id='"+rows[y]['id']+"' class='fa fa-square-o custom-check' style='cursor:pointer;margin-right:10px;'></i></td>";
		for(var i=0;i<rows[y]['data'].length;i++) {
			var dataValue = rows[y]['data'][i];
			if( dataValue.length > 255 )
				dataValue = dataValue.substr(0,255) + "...";
			html += "<td onclick='editData(this);'>" + dataValue + "</td>";
		}
		html += "</tr>";
	}
	
	html += "</table>";
	$("#table-data").html(html);
	
	html = "";
	var noPages = parseInt(count / size)+1;
	if( noPages > 10 ) {
		html = '<span>Page: <input type="text" class="form-control" id="pageNumberBox1" style="width:80px;display:inline-block;" value="'+page+'"/>&nbsp;<button class="btn btn-info" style="top: -2px;position: relative;" onclick="update_page_box(1);" type="button">Go</button></span>';
		$(".custom_pagination_1").html(html);
		html = '<span>Page: <input type="text" class="form-control" id="pageNumberBox2" style="width:80px;display:inline-block;" value="'+page+'"/>&nbsp;<button class="btn btn-info" style="top: -2px;position: relative;" onclick="update_page_box(2);" type="button">Go</button></span>';
		$(".custom_pagination_2").html(html);
	} else {
		for(var i=0;i<noPages;i++) {
			var classAdd = "";
			if( i+1 == parseInt(page) ) 
				 classAdd = " active";
			html += '<button class="btn btn-info'+classAdd+'" onclick="update_page('+(i+1)+')" type="button">'+(i+1)+'</button>';
		}
		$(".custom_pagination_1").html(html);
		$(".custom_pagination_2").html(html);
	}
	
	
	
	pageSetting = page;
	//headings
	$(".pageMarker").html("Page " + pageSetting + " of " + noPages);
	
	
	updateFiltersView();
	
	if( results.alert ) {
		alertBox( results.alert , "Action Result");
	}
}

function select_all() {
	//
	$(".custom-check").removeClass('fa-square-o');
	$(".custom-check").addClass('fa-square');
}
function select_none() {
	//custom-check
	$(".custom-check").removeClass('fa-square');
	$(".custom-check").addClass('fa-square-o');
}

function toggle(obj) {
	
	if( $(obj).hasClass('fa-square-o') ) {
		$(obj).removeClass('fa-square-o');
		$(obj).addClass('fa-square');
	} else {
		$(obj).removeClass('fa-square');
		$(obj).addClass('fa-square-o');
	}
}

function update(query, changes, callback_func) {
	//table-data
	showLoading();
	
	var jsonFinal = {"actions" : changes, "filters" : filters};
	
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

window.onresize = function(event) {
	windowResize();
}
$( document ).ready(function() {
	windowResize();
});
function windowResize() {
	$("#table-data").css("height", $(window).height()-360 );
}
