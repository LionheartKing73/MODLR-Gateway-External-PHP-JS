
var editingWidget = null;
function editWidget(itemIcon) {
	var widget = itemIcon.parentNode.parentNode.parentNode;
	if( widget.tagName == "UL" ) {
		widget = itemIcon.parentNode.parentNode;
	}
	editingWidget = widget;
	
	$( "#dlgEditWidget" ).dialog({
      resizable: true,
      height:400,
      width:500,
      autoOpen: true,
      modal: true,
      buttons: {
			'Save': function(event) {
				
				saveWidget(editingWidget);
				savePage_add();
				refreshAll();
				
            	$( this ).dialog( "close" );
       	 	},
       	 	'Close': function(event) {
            	$( this ).dialog( "close" );
       	 	}
    	}
    });
	
	$('#widget-type-select').chosen({
    	width: '280px;',
    	height: '60px;'
    });
	
	
	
	$('#widget-page-select').chosen({
    	width: '280px;',
    	height: '60px;'
    });
	$('#widget-workview-select').chosen({
    	width: '280px;',
    	height: '60px;'
    });
	$('#widget-existing-workview-select').chosen({
    	width: '280px;',
    	height: '60px;'
    });
	$('#widget-workview-dimension-select').chosen({
    	width: '280px;',
    	height: '60px;'
    });
	
	
	var type = getDataset(editingWidget,"type");
	var workview = getDataset(editingWidget,"workview");
	var html = getDataset(editingWidget,"html");
	var page = getDataset(editingWidget,"page");
	
	
	if( page == "undefined" ) {
		page = "";
	}
	if( type == "undefined" ) {
		type = "";
	}
	if( html == "undefined" ) {
		html = "";
	}
	if( workview == "undefined" ) {
		workview = "";
	}
	
	if( type ) {
		if( type != "" ) { 
			$('#widget-type-select').val(type);
			$('#widget-type-select').trigger('chosen:updated');
		}
	}
	
	

	
	
	
	$('#widget-type-select').on('change', function(event, params) {
		//changedSelectableWorkview();
		changedType();
	});
	
	changedType();
	
	if( workview ) {
		if( workview != "" ) { 
			
			if( type == "Selectable" ) {

				$('#widget-existing-workview-select').val(workview);
				$('#widget-existing-workview-select').trigger('chosen:updated');
			} else {
				$('#widget-workview-select').val(workview);
				$('#widget-workview-select').trigger('chosen:updated');
				
			}
		}
	}
	
		
	if( page ) {
		if( page != "" ) { 
			$('#widget-page-select').val(page);
			$('#widget-page-select').trigger('chosen:updated');
		}
	}
	
	//if( type != "Selectable" ) {
		$('#widget-existing-workview-select').on('change', function(event, params) {
			changedSelectableWorkview();
		});
		changedSelectableWorkview();
	//}
	
	if( html ) {
		if( html != "" ) {
			editor_widget.setValue(html);
		}
	}

	
}

function addWorkviewToSelectables(workviewId, workviewName) {
	//widget-existing-workview-select
	if( $("#widget-existing-workview-select option[value='" + workviewId + "']").length > 0 ) {
		//workview already exists in the select
	} else {
		$("#widget-existing-workview-select").append("<option value='"+workviewId+"'>"+workviewName+"</option>");
		$("#widget-existing-workview-select").trigger('chosen:updated');
	}
}

function modelDimById(dimId) {
	for(var i=0;i<model_detail['dimensions'].length;i++) {
		var dim = model_detail['dimensions'][i];
		if( dim.id == dimId ) 
			return dim;
	}
	return null;
}

function changedSelectableWorkview() {
	var type = getDataset(editingWidget,"type");
	var workview = getDataset(editingWidget,"workview");
	var dimension = getDataset(editingWidget,"dimension");
	var filter = getDataset(editingWidget,"filter");
	
	
	
	var workviewObject = workview_titles_store[workview];
	//alert(workview);
	var workviewObject1 = workview_titles_store[wkviewtmp];

	// addWorkviewToSelectables("02b42e0464be8fed69cfa8634352dbdd", "Scatter Plot");
	// addWorkviewToSelectables("3299ad68065376ce22f5fe7cc76e3502", "Transaction Cost Chart");
	// addWorkviewToSelectables("f04bfc950b7d3881f7676fa0e48f7343", "Bar Chart");
	// addWorkviewToSelectables("a731bd35abc4d0c433c503e35fa27c96", "Pie Chart");
	// addWorkviewToSelectables("977843c9fe90a258e562f045261fd82c", "Line Chart");


	$("#widget-workview-dimension-select option").remove();
	for (var dim in workviewObject) {
		var dimObj = modelDimById(dim);
		var dimName = dimObj.name;
	 	console.log(dim+"  "+dimName);
		$("#widget-workview-dimension-select").append("<option value='"+dim+"'>"+dimName+"</option>");
	}
	if (dim==null){
		$("#widget-workview-dimension-select").append("<option value='597d08330e2dad4bd6697326753361f2'>Customer</option>");
		$("#widget-workview-dimension-select").append("<option value='9dc58953c63f5192b3bc220c538d3f23'>Product</option>");
		$("#widget-workview-dimension-select").append("<option value='b79f6e118da10b1ae01a0ff29c30e6a3'>Interaction</option>");
		$("#widget-workview-dimension-select").append("<option value='ac365d95c392b56eb13e24b03880dc6e'>Scenario</option>");
		$("#widget-workview-dimension-select").append("<option value='c4709c20c0b03643601c28e2956f2438'>Time</option>");
	}
	$("#widget-workview-dimension-select").trigger('chosen:updated');
	
	if( typeof dimension === "undefined" ) {
		
	} else {
		var dimObj = modelDimById(dimension);
		var dimName = dimObj.name;
		$("#selectableEditorRowDimension .chosen-single").empty();
		$("#selectableEditorRowDimension .chosen-single").append("<span>"+dimName+"</span>");
	}


	
	
	var widgets = $("ul.gridsterList > li");
	var html = "";

	for(var i=0;i<widgets.length;i++) {
		var widget = widgets[i];
		var type = getDataset(widget,"type");
		var workview_name = getDataset(widget,"workview_name");

		var col = getDataset(widget,"col");
		var row = getDataset(widget,"row");
		var id = "w" + col + "_" + row;
		
		if( type != "Selectable" && type != "Custom" ) {
			var checked = "";
			//alert(i+"  "+filter);
			if (filter!=null){
				if( filter.indexOf(id) > -1 ) {
					checked = " checked";
				}
			}
			if (workview_name!=null){
				html += "<input type='checkbox'"+checked+" id='check_"+id+"' name='check_"+id+"' data-id='"+id+"'> " + type + " » " + workview_name + "<br/>";
			}
		}
		
	}
	$('#rowWidgets').html(html);
	
}

function changedType() {
	var type = $('#widget-type-select option:selected').text();
	
	//hide all
	
	$('#selectableEditorRow').css("display","none");
	$('#selectableEditorRowDimension').css("display","none");
	$('#selectableEditorRowWidgets').css("display","none");
	$('#workviewSelectRow').css("display","none");
	$('#customEditorRow').css("display","none");
	$('#pageSelectRow').css("display","none");


	if( type == "Workview Visualisation" || type == "Workview" ) {
		
		$('#workviewSelectRow').css("display","table-row");
		
	} else if( type == "Selectable"  ) {
		
		$('#selectableEditorRow').css("display","table-row");
		$('#selectableEditorRowDimension').css("display","table-row");
		$('#selectableEditorRowWidgets').css("display","table-row");
		
	} else if( type == "Page"  ) {
		
		$('#pageSelectRow').css("display","table-row");
	} else {
		
		$('#customEditorRow').css("display","table-row");
		
	}
}

function updateWidgetEditor() {
	var widgets = $("li.gs-w");
	for(var i=0;i<widgets.length;i++) {
		var w = widgets[i];
		if($(w).find('div.toolbox').length != 0 ) {
			//no need to add more toolbox icons
		} else {
			$(w).prepend(widgetInnerHTML);
		}
	}
}

function saveSelectableDefaults() {
	var selectables = $(".selectable");
	
	for(var i=0;i<selectables.length;i++) {
		var widget = $(selectables[i]).parent().parent().parent();
		var val = $(selectables[i]).find('option:selected').text();
		$(widget).data("element", val);
	}
	
}

function saveWidget(editingWidget) {
	
	var type = $('#widget-type-select option:selected').text();
	var workviewId = $('#widget-workview-select option:selected').val();
	var workviewName = $('#widget-workview-select option:selected').text();
	var pageId = $('#widget-page-select option:selected').val();
	var pageName = $('#widget-page-select option:selected').text();
	
	setDataset(editingWidget,"type",type);
	setDataset(editingWidget,"workview",workviewId);
	setDataset(editingWidget,"workview_name",workviewName);
	
	if( type == "Workview" || type == "Workview Visualisation" ) {
		setDataset(editingWidget,"html","");
	
	} else if( type == "Page" ) {
		
		setDataset(editingWidget,"page",pageId);
		setDataset(editingWidget,"page_name",pageName);
	
	
	} else if( type == "Selectable" ) {
		var workviewId = $('#widget-existing-workview-select option:selected').val();
		var workviewName = $('#widget-existing-workview-select option:selected').text();
		setDataset(editingWidget,"workview",workviewId);
		setDataset(editingWidget,"workview_name",workviewName);
		
		var dimensionId = $('#widget-workview-dimension-select option:selected').val();
		var dimensionName = $('#widget-workview-dimension-select option:selected').text();
		setDataset(editingWidget,"dimension",dimensionId);
		setDataset(editingWidget,"dimension_name",dimensionName);
		
		var filter = "";
		var widgets = $("#rowWidgets > input[type=checkbox]:checked");
		for(var i=0;i<widgets.length;i++) {
			filter+=getDataset(widgets[i],"id")+",";
		}
		setDataset(editingWidget,"filter",filter);
		
		
	} else {
		setDataset(editingWidget,"html",editor_widget.getValue());
	}
	
	finishedUpdatingNotify = updateWidgetEditor;
	refresh();
	
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


function savePage() { 
	var gridData = null;
	if( dashboard == "1" ) {
		
		saveSelectableDefaults();
		
		var gridster = $("div.gridster > ul.gridsterList").gridster().data('gridster');
        gridData = gridster.serialize();
		document.getElementById("page_contents").innerHTML = JSON.stringify(gridData);
		
	} else {
	
		if( page_type == "advanced" || page_type == "server-side") {
			
			showLoading();

			var post = { page_contents: editor.getValue(), style: $("#style").val(), action:"save", title: $("#title").val(), type: page_type };
			
			$.ajax({
				type: "POST",
				dataType: "text",
				contentType: "application/text; charset=utf-8",
				processData: false,
				url: "/json/page/?action=save&id="+model_id+"&activityid="+activity_id+"&page="+page_id,
				data: JSON.stringify( post )
			}).done(function(data) {
				var result = JSON.parse(data);
				page_id = result.pageid;
				notice("Document Saved","The document has been saved.");
			}).fail(function() {
				notice("Document Save Failed","There was an error while saving the page. Please copy your code to prevent losing it.");
			}).always(function(result) {
				hideLoading();
			});

			return;
		}
		
		
		document.getElementById("page_contents").innerHTML = editor.getValue();
	}
	document.pageUpdateForm.submit();
	
}


function removeWidget(itemIcon) {
	var gridster = $("div.gridster > ul.gridsterList").gridster().data('gridster');
	var widget = itemIcon.parentNode.parentNode.parentNode;
	if( widget.tagName == "UL" ) {
		widget = itemIcon.parentNode.parentNode;
	}
	gridster.remove_widget(widget);
}	
function addWidget() {
	var gridster = $("div.gridster > ul.gridsterList").gridster().data('gridster');
	var widgetInnerHTML_li = '<li data-row="1" data-col="1" data-sizex="6" data-sizey="6" class="gs-w"><div class="toolbox"><div style="position:absolute;cursor:pointer;font-size:20px;margin:4px;z-index:1000;"><i class="fa fa-pencil" style="padding:2px;" onclick="editWidget(this);"></i><i class="fa fa-times" style="padding:2px;" onclick="removeWidget(this);"></i></div></div><span class="gs-resize-handle gs-resize-handle-both"></span></li>';

	gridster.add_widget(widgetInnerHTML_li);

}

function changeMode(bDashboard, bAdvanced) {
	var url = "/activity/page/?id=" + getParameterByName("id") + "&activityid=" + getParameterByName("activityid") + "&action=" + getParameterByName("action");
	
	if( getParameterByName("page") != "" )
		url += "&page=" + getParameterByName("page");
	
	
	if( bDashboard )
		url += "&dashboard=1";
	
	if( bAdvanced )
		url += "&advanced=1";
		
	if( !bDashboard && !bAdvanced ) 
		url += "&advanced=0&dashboard=0";
		
	window.location = url;
}

function viewRevision() {
	
	$("#revision_group").css("display","block");
	
	var html_content = $( "#revisions option:selected" ).data("document");
	//html_content = html_content.replace(/'/gi,"\\\'");
	$("#revision_panel").html("<textarea class='wysihtml5 form-control' id='page_revision' name='page_revision' ></textarea>");
	$("#page_revision").val( html_content );
	
	if( page_type == "advanced" ) {
		var mixedMode = {
			name: "htmlmixed",
			scriptTypes: [{matches: /\/x-handlebars-template|\/x-mustache/i,
					mode: null},
					{matches: /(text|application)\/(x-)?vb(a|script)/i,
                       mode: "vbscript"}]
		};
	
		CodeMirror.fromTextArea( $("#page_revision")[0] , {
			value: html_content,
			mode: mixedMode,
			lineNumbers: true,
			readOnly: true
		});
	
	} else {
		var mixedMode = {
			name: "javascript",
			scriptTypes: [{matches: /\/x-handlebars-template|\/x-mustache/i,
					   mode: null},
					  {matches: /(text|application)\/(x-)?vb(a|script)/i,
					   mode: "vbscript"},
					   {matches: /\/text|\/x-rsrc/i,
					   mode: "r"}]
		};
	
		CodeMirror.fromTextArea( $("#page_revision")[0] , {
			value: html_content,
			mode: mixedMode,
			lineNumbers: true,
			readOnly: true
		});
		
	}
	
	//
	
}


function savePage_add() { 
	var gridData = null;
	if( dashboard == "1" ) {
		
		saveSelectableDefaults();
		
		var gridster = $("div.gridster > ul.gridsterList").gridster().data('gridster');
        gridData = gridster.serialize();
		document.getElementById("page_contents").innerHTML = JSON.stringify(gridData);
		
	} else {
	
		if( page_type == "advanced" || page_type == "server-side") {
			
			showLoading();

			var post = { page_contents: editor.getValue(), style: $("#style").val(), action:"save", title: $("#title").val(), type: page_type };
			
			$.ajax({
				type: "POST",
				dataType: "text",
				contentType: "application/text; charset=utf-8",
				processData: false,
				url: "/json/page/?action=save&id="+model_id+"&activityid="+activity_id+"&page="+page_id,
				data: JSON.stringify( post )
			}).done(function(data) {
				var result = JSON.parse(data);
				page_id = result.pageid;
				notice("Document Saved","The document has been saved.");
			}).fail(function() {
				notice("Document Save Failed","There was an error while saving the page. Please copy your code to prevent losing it.");
			}).always(function(result) {
				hideLoading();
			});

			return;
		}
		
		
		document.getElementById("page_contents").innerHTML = editor.getValue();
	}
	
}