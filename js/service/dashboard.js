
var workview_definition = null;
var workview_metadata = null;
var workview_titles_store = {};

var taskQueue = [];
var titles = [];
var workview_execute_options = {"disable-set-instructions" : "0","define" : "1"};

var subscribers = [];
function Subscribe(widget, callback) {
	for(var i=0;i<subscribers.length;i++){
		if( subscribers[i].widget == widget ) {
			return;
		}
	}
	
	
	subscribers.push({"widget":widget,"callback":callback});

}

function DashboardInnerPage(widget_column, widget_row, pageId, pageName, pageType) {
	var id = "w" + widget_column + "_" + widget_row;
	
	
	
	$("#" + id).html(html);
	$("#" + id).css("text-align","left");

}


function refresh() {
	var widgets = $(".gridster li");
	for(var i=0;i<widgets.length;i++) {
		var widget = widgets[i];
		var type = getDataset(widget,"type");
		
		if( type == "Selectable" ) {
			var loaded = getDataset(widget,"loaded");
			if( loaded == "" || typeof loaded === "undefined" ) {
				processWidget(widgets[i]);
			}
		} else {
			processWidget(widgets[i]);
		}
	}
	processQueue();
}
function refreshAll() {
	var widgets = $(".gridster li");
	 
	for(var i=0;i<widgets.length;i++) {
		var widget = widgets[i];
		var type = getDataset(widget,"type");
			
			processWidget(widgets[i]);
	}
	processQueue();
}



function processWidget(widget) {
	var type = getDataset(widget,"type");
	
	
	var model_id = getParameterByName("id");
	var activity_id = getParameterByName("activityid");
	
	//load from local variables if set.
	if( typeof modelid !=  'undefined' ) {
		model_id = modelid;
	}
	if( typeof activityid != 'undefined' ) {
		activity_id = activityid;
	}
	
	
	var col = getDataset(widget,"col");
	var row = getDataset(widget,"row");
	var id = "w" + col + "_" + row;
	
	renderWidget(widget);
	if(  type == "Custom" || type == "Page") {
		titles = [];
		populateTitlesForWidget(widget);
		for(var i=0;i<subscribers.length;i++){
			if( subscribers[i].widget == widget ) {
				subscribers[i].callback(titles);
			}
		}
	} else {
		workview_execute_options.define = "1";
		//workview_execute_options = {"disable-set-instructions" : "0","define" : "1","expand":[]};
		
		var workview = getDataset(widget,"workview");
		var workview_name = getDataset(widget,"workview_name");
		
		titles = [];
		if( typeof workview !== "undefined" ) {
			var func = "workview.execute";
			if( type == "Selectable" ) {
				func = "workview.get";
			} else {
				if( typeof addWorkviewToSelectables !== 'undefined' ) {
					addWorkviewToSelectables(workview, workview_name);

				}
				
				populateTitlesForWidget(widget);
				
				
				if( type == "Workview Visualisation" ) {
					func = "workview.execute.visualisation";
				}
			}
			
			taskQueue[taskQueue.length] = {"task": func, "id" : model_id, "activityid" : activity_id,"workviewid" : workview, "titles" : titles, "options" : workview_execute_options , "tag" : id };
			//console.log(taskQueue[taskQueue.length]);
			//console.log(taskQueue.length);
			//console.log(taskQueue);
		}
	}
}



function populateTitlesForWidget(targetWidget) {
	var col = getDataset(targetWidget,"col");
	var row = getDataset(targetWidget,"row");
	var targetId = "w" + col + "_" + row;

	var widgets = $("ul.gridsterList > li");
	for(var i=0;i<widgets.length;i++) {
		var widget = widgets[i];
		var type = getDataset(widget,"type");
		if( type == "Selectable") {
					
			var col = getDataset(widget,"col");
			var row = getDataset(widget,"row");
			var selectableId = "w" + col + "_" + row;
		
			var filter = getDataset(widget,"filter");
			var dimension = getDataset(widget,"dimension");
			if( filter.indexOf(targetId) > -1 ) {
				//selectables may not have loaded in which case we use their default selections.
				
				var elementDefault = getDataset(widget,"element");
				var elementSecondaryDefault = getDataset(widget,"elementSecondary");
				
				if( $("#" + selectableId + "selectable-select").length == 0 ) {
					
					//is there a selection.
					titles[titles.length] = {
						'type' : 'dimension',
						'id' : dimension,
						'element' : elementDefault,
						'hierarchy' : "Default"
					};
				} else {
					var element = $("#" + selectableId + "selectable-select option:selected").val();
					
					if( $('#' + selectableId + 'selectable-select-secondary').length > 0 ) {
						var elementTimeRange = $('#' + selectableId + 'selectable-select-secondary option:selected').text();
						element = gatherRangeElement($("#" + selectableId + "selectable-select option"), element, elementTimeRange);
						
						var reorder = element.split("[+]");
						if( $("#" + selectableId + "selectable-select option:selected").val() != reorder[0] ) {
							$("#" + selectableId + "selectable-select option:selected").val(reorder[0]);
							$("#" + selectableId + "selectable-select-secondary option:selected").val(reorder[reorder.length-1]);
							
						}
						
					}
					
					if( $("#" + selectableId + "selectable-select option").length > 0 ) {
						//is there a selection.
						titles[titles.length] = {
							'type' : 'dimension',
							'id' : dimension,
							'element' : element,
							'hierarchy' : "Default"
						};
					}
				}
			}
		}
	}
}

function processQueue() {
	if( taskQueue.length == 0 ) {
		
		if( finishedUpdatingNotify != null ) {
			finishedUpdatingNotify();
			finishedUpdatingNotify = null;
		}
		
		return;
	}
	bLoading = true;
	var tasks = {"tasks": taskQueue};
	                console.log(taskQueue);
	query("collaborator.service",tasks,processQueueCallback);
	taskQueue = [];
}

var finishedUpdatingNotify = null;
function processQueueCallback(data) {
	
	var results = JSON.parse(data);
	var taskList = results['results'];
	var bCleanup = true;
	console.log(taskList);
	//loop through widgets updating the selectables.
	updatingSelectables = true;
	
	//update all the workview type widgets first then afterwards update the selectables.
	for(var i=0;i<taskList.length;i++) {
		if( taskList[i]['result'] == 1 ) {
			
			var id = taskList[i]['tag'];
			var divElement = $("#" + id)[0];
			if( divElement != null ) {
				var widget = divElement.parentNode;
				
				var sizex = parseInt(getDataset(widget,"sizex")) * tileWidth;
				var sizey = parseInt(getDataset(widget,"sizey")) * tileHeight;
				var type = getDataset(widget,"type");
				 
				var height = $(widget).innerHeight();
				var width = $(widget).innerWidth();
					
				if( type == "Workview Visualisation") {
					$("#" + id).html("<b>" + taskList[i]['title'] + "</b><div id='" + id + "chart'></div>");
					//call the chart present function from the charts.js files
					try {
						presentChart(taskList[i], id + "chart" , bCleanup, width + "px", (height-10) + "px");
					}
					catch(e) {
						//avoid breaking the queue.
					}
					bCleanup = false;
				} else if( type == "Workview") {
					//
					$("#" + id).html("<div style='display:table-cell;vertical-align:middle;width:" + width + "px;height:" + height + "px'><center><table id='" + id + "table'></table></center></div>");
					
					workview_definition = taskList[i]['definition'];
					workview_metadata = taskList[i]['metadata'];
					
					
					outputTable(id + "table", taskList[i]);
					updateWorkviewData(taskList[i]);
					
					workview_definition = null;
					workview_metadata = null;
					
					var tableWidth = $("#" + id + "table").innerWidth();
					var tableHeight = $("#" + id + "table").innerHeight();
					if( tableWidth > width || tableHeight > height ) {
						$("#" + id).css("overflow", "scroll");
					}
				} else if( type == "Selectable") {
					$("#" + id).css('width','100%');
					$("#" + id).css('height','100%');
					$("#" + id).css('display','flex');
					$("#" + id).css('align-items','center');
					$("#" + id).css('padding-left','7px');
					$("#" + id).css('padding-right','7px');
				
					//$("#" + id).html("<div style='display:table-cell;vertical-align:middle;width:" + width + ";height:" + height + "px' id='" + id + "selectable'></div>");
					 
					$("#" + id).css('float','left');
					$("#" + id).html("<div style='display:flex;align-items:center;vertical-align:middle;width:100%;" + "height:" + height + "px' id='" + id + "selectable'></div>");

					addTitlesToSelectableStore(taskList[i]);
					renderSelectable(widget, id + "selectable");
					$("#" + id+" select").css('width','100%');
					$("#" + id+" div").css('width','100%');
					$("#" + id+" div").css('overflow','visible');
					$("#" + id).css('overflow','visible');
					$(".chosen-single div").css('width','20px');
				}
			}
		} else {
			
		}
	}
	
	
	updatingSelectables = false;
	
	if( finishedUpdatingNotify != null ) {
		finishedUpdatingNotify();
		finishedUpdatingNotify = null;
	}
}

var updatingSelectables = false;
var wkviewtmp;
function addTitlesToSelectableStore(workviewGet) {
	var workview_id = workviewGet['id'];
	wkviewtmp=workview_id;
	workview_titles_store[workview_id] = JSON.parse(JSON.stringify(workviewGet['titles']));
	// console.log(workview_id);
	// console.log(workview_titles_store[workview_id]);

}

//this function should only be called when the titles from the required workview have been cached in the selectables store.
function renderSelectable(widget, divId) {
	var html = '';
	var maxWidth = $("#" + divId).innerWidth();
	var selectSearch = false;
	
	var id = getDataset(widget,"id");
	var type = getDataset(widget,"type");
	var workview = getDataset(widget,"workview");
	var dimension = getDataset(widget,"dimension");
	
	var element = getDataset(widget,"element");
	
	var workviewObject = workview_titles_store[workview];
	if( dimension == "undefined" || typeof dimension === "undefined" ) {
		html = "Incomplete Selectable Configuration";
	} else {
		var titleObject = workviewObject[dimension];
		
		if( titleObject ) {
			if( titleObject.elements.length > 0 ) {
				var timeRange = titleObject['time-range'];
				var elements = titleObject.elements;
				if( timeRange ) {
					html += "<select id='" + divId + "-select' class='selectable' style='width:" + ((maxWidth/2)-16) + "px;'>";
					for(var i=0;i<elements.length;i++) {
						var elmName = elements[i].name;
						var select = "";
						if( element == elmName )
							select = " selected ";
						html += "<option value='"+elmName+"'"+select+">"+elmName+"</option>";
					}
					html += "</select>";
					html += " to ";
					html += "<select id='" + divId + "-select-secondary' class='selectable-secondary' style='width:" + ((maxWidth/2)-16) + "px;'>";
					for(var i=0;i<elements.length;i++) {
						var elmName = elements[i].name;
						var select = "";
						if( element == elmName )
							select = " selected ";
						html += "<option value='"+elmName+"'"+select+">"+elmName+"</option>";
					}
					html += "</select>";
				} else {
					html += "<select id='" + divId + "-select' class='selectable' style='width:" + (maxWidth-16) + "px;'>";
					for(var i=0;i<elements.length;i++) {
						var elmName = elements[i].name;
						var select = "";
						if( element == elmName )
							select = " selected ";
						html += "<option value='"+elmName+"'"+select+">"+elmName+"</option>";
					}
					html += "</select>";
				}
				
			}
			
			if( titleObject.elements.length > 13 ) {
				selectSearch = true;
			}
		} else {
			html = "Dimension is no longer in the Selectable Position in the Workview.";
		}
	
	}
	
	setDataset(widget,"loaded","true");
	
	$("#" + divId).html(html);
	
	$("#" + divId + "-select").change(function() {
		refresh();
	});
	$("#" + divId + "-select-secondary").change(function() {
		refresh();
	});
	 
	
	$("#" + divId + "-select").chosen({search_contains: true}); 
	$("#" + divId + "-select-secondary").chosen({search_contains: true}); 
		
	$("#" + id).css("overflow","visible");
	$("#" + id).css("overflow-x","visible");
	$("#" + id).css("overflow-y","visible");
}

function outputTable(divId, dataset) {
	cell_prefix = divId + "_";
	
	var html = '';
	//update the titles table first then the worksheet table.
	var rows = dataset['rows'];
	var columns = dataset['columns'];
	var column_start = 0;
	var row_start = 0;
	var column_start_prior = 0;
	var row_count_prior = 0;
	
	//return the number of set rows and columns in the grid.
	row_count = dataset['rows'][0]['members'].length;
		
	var column_count = 0;
	var column_count_prior = 0;
	if( dataset['columns'][0] )
		column_count = dataset['columns'][0]['members'].length;
	
	//count the rows/columns in the sets prior to 0
	if( dataset['columns'].length > 0 ) {
		for(var i=0;i<dataset['columns'][0]['members'].length;i++) {
			if( parseInt(dataset['columns'][0]['members'][i]['set']) < Math.abs(column_start) ) {
				column_count_prior++;
			}
		}
		column_count -= column_count_prior;
	}
	
	if( dataset['rows'].length > 0 ) {
		for(var i=0;i<dataset['rows'][0]['members'].length;i++) {
			if( parseInt(dataset['rows'][0]['members'][i]['set']) < Math.abs(row_start) ) {
				row_count_prior++;
			}
		}
		row_count -= row_count_prior;
	}
	
	//for oncolumn loop (creating rows)
		//for onrow loop (creating columns)
		//for oncolumn sets (creating columns)
	for(var yy=0;yy<row_count_prior;yy++) {
		var y = yy + columns.length;
		var row = dataset['rows'][0]['members'][yy];
		
		html += '<tr>';
		
		for(var xx=0;xx<Math.abs(column_count_prior);xx++) {
			var x = xx + rows.length;
			
			var col = dataset['columns'][0]['members'][xx];
			
			html += '<td id="' + cell_prefix + 'c' + col['column'] + 'r' + row['row'] + '" class="c c' + x + ' r' + y + ' c' + xx + 'r' + yy + '" data-address="c' + xx + 'r' + yy + '" >&nbsp;</td>';
		}
		
		for(var x=0;x<dataset['rows'].length;x++) {
			
			html += updateGridWithDefinitionProduceHeader(dataset,dataset['rows'][x],x,y,yy,x,'rows');
			
		}
		for(var xx=Math.abs(column_count_prior);xx<column_count+column_count_prior;xx++) {
			var x = xx + rows.length;
			
			var col = dataset['columns'][0]['members'][xx];
			
			html += '<td id="' + cell_prefix + 'c' + col['column'] + 'r' + row['row'] + '" class="c c' + x + ' r' + y + ' c' + xx + 'r' + yy + '" data-address="c' + xx + 'r' + yy + '" >&nbsp;</td>';
		}
		
		html += '</tr>';
	}
	
	for(var y=0;y<dataset['columns'].length;y++) {
		//ensure the dimension is not hidden
		
		
		html += '<tr>';
		for(var xx=0;xx<Math.abs(column_count_prior);xx++) {
			var x = xx + rows.length;
			
			html += updateGridWithDefinitionProduceHeader(dataset,dataset['columns'][y],x,y,xx,y,'columns');
		}
		
		for(var x=0;x<dataset['rows'].length;x++) {
			html += '<td class="z hc' + x + 'r' + y + '">&nbsp;</td>';
		}
		
		for(var xx=Math.abs(column_count_prior);xx<column_count+column_count_prior;xx++) {
			var x = xx + rows.length;
			
			html += updateGridWithDefinitionProduceHeader(dataset,dataset['columns'][y],x,y,xx,y,'columns');
		}
		html += '</tr>';
	}
	
	//for onrow sets (creating rows)
		//for onrow loop (creating columns)
		//for oncolumn sets (creating columns)
		
	for(var yy=row_count_prior;yy<row_count+row_count_prior;yy++) {
		var y = yy + columns.length;
		
		var row = dataset['rows'][0]['members'][yy];
		
		html += '<tr>';
		
		for(var xx=0;xx<Math.abs(column_count_prior);xx++) {
			var x = xx + rows.length;
			
			var col = dataset['columns'][0]['members'][xx];
			
			html += '<td id="' + cell_prefix + 'c' + col['column'] + 'r' + row['row'] + '" class="c c' + x + ' r' + y + ' c' + xx + 'r' + yy + '" data-address="c' + xx + 'r' + yy + '" >&nbsp;</td>';
		}
		
		for(var x=0;x<dataset['rows'].length;x++) {
			
			html += updateGridWithDefinitionProduceHeader(dataset,dataset['rows'][x],x,y,yy,x,'rows');
			
		}
		for(var xx=Math.abs(column_count_prior);xx<column_count+column_count_prior;xx++) {
			var x = xx + rows.length;
			
			var col = dataset['columns'][0]['members'][xx];
			
			html += '<td id="' + cell_prefix + 'c' + col['column'] + 'r' + row['row'] + '" class="c c' + x + ' r' + y + ' c' + xx + 'r' + yy + '" data-address="c' + xx + 'r' + yy + '" >&nbsp;</td>';
		}
		
		html += '</tr>';
	}
	
	
	$("#" + divId).html(html);
	
	$( "i.toggle" ).unbind();
	$( "i.toggle" ).bind( "touchend click", function(e) {
		toggle(this.parentNode);
	});
}



function renderWidget(widget) {
	var type = getDataset(widget,"type");
	
	if(typeof type === 'undefined') {
		return;
	}
	
	var workview = getDataset(widget,"workview");
	var html = getDataset(widget,"html");
	
	var col = getDataset(widget,"col");
	var row = getDataset(widget,"row");
	var sizex = parseInt(getDataset(widget,"sizex")) * tileWidth;
	var sizey = parseInt(getDataset(widget,"sizey")) * tileHeight;
	var id = "w" + col + "_" + row;
	
	//cleanup prior widget div tags etc.
	for(var k=widget.childNodes.length-1;k>=0;k--) {
		var child = widget.childNodes[k];
		var childId = child.id;
		if( childId ) {
			if( childId.substring(0,1) == "w" ) {
				widget.removeChild(child);
			}
		}
	}
	
	
	if( $("#" + id).length > 0 ) {
	
	} else {
		if( $(widget).find( "div.toolbox" ).length > 0 ) { 
			$(widget).find( "div.toolbox" ).after("<div id='" + id + "'></div>");
		} else {
			$(widget).prepend("<div id='" + id + "'></div>");
		}
	}
	
	
	$("#" + id).css("vertical-align","middle");
	$("#" + id).css("text-align","center");
	$("#" + id).css("overflow","hidden");
	
	var height = $(widget).innerHeight();
	var width = $(widget).innerWidth();
	
	$("#" + id).css("height",height + "px");
	$("#" + id).css("width",width + "px");
	
	if( type == "Custom" ) {
		
		$("#" + id).html(html);
		$("#" + id).css("text-align","left");
		
	} else if( type == "Page"  ) {
		$("#" + id).css("text-align","left");
		
		if( debug_mode ) { 
			html = "<div style='padding:5px;text-align:center;'><br/><h4>Page: "+getDataset(widget,"page_name")+"</h4></div>";
			$("#" + id).html(html);
		} else {
			var page = JSON.parse(html);
			var page_type = page.type;
			
			if( page_type == "advanced" || page_type == "simple" ) {
				$("#" + id).html(page.contents);
			} else if( page_type == "table" ) {
				
			} else if( page_type == "form" ) {
				
				$("#" + id).css("overflow-y","scroll");
				
				html = "<div style='margin: 10px;'>";
				html += page.contents_prior;
				
				html += '<div class="panel-body" id="panel'+page.pageid+'" style="">';
				html += '<form action="#" class="form-horizontal " method="post" name="form<? echo $page_id;?>" id="form'+page.pageid+'"></form></div>';
				
				html += page.contents_post;
				html += "</div>";
				
				$("#" + id).html(html);
				
				//define the form
				var form = {};
				form.id = page.pageid;

				form.fieldList = page.fields;
				form.record = page.record;
				if( form.record == null ) {
					form.record = [];
				}
				
				form.method = page.method;
				if( page.primary_id  ) {
					form.primary_id = page.primary_id;
				}

				form.pageSuccess = page.pageSuccess;
				form.pageBack = page.pageBack;
				form.table_definition = page.table_definition;

				forms[forms.length] = form;
				setupForm(form.id);
				
			} else {
				
			}
		}
		
	} else {
		var loadingHtml = "<div style='position:relative;height:"+height+"px;width:100%;align-items:center;justify-content:center;display:flex;'><img src='/img/loader.gif' style='margin-top:5px;width:16px;height;16px;'/>&nbsp;<span id='txtLoading'>Communicating with the analytics server.<span></div>";
		var loadingHtmlSmall = "<center><img src='/img/loader.gif' style='margin-top:5px;width:16px;height;16px;' /> <span id='txtLoading'>Communicating with the analytics server.<span></center>";
		if( type == "Workview" ) {
			$("#" + id).html(loadingHtml);
		} else if( type == "Workview Visualisation" ) {
			$("#" + id).html(loadingHtml);
		} else if( type == "Selectable" ) {
			$("#" + id).html(loadingHtmlSmall);
		} 
	}

}
