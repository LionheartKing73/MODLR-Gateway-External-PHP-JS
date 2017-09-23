
var is_editor = true;
var header_count = 0; 

function workviewExportPerform(tasks) {
	query("model.service",tasks,workviewExportCallback);
}

function workviewBindings() {
    
    $( "body" ).bind( "click", function() {
		hideRibbonFileMenu();
		
		if( selCellAddress == null )
			cleanUpDataEntry();
		else
			return true;
			
		enableButtonsFor('');
		return false;
	});
}
$(function() {


	//initialise the workview:
	if( workview_definition == null ) {
		//failed to load a specific workview
		window.location = '/model/?id=' + model_detail.id;
	} else {
		if( !workview_definition['header_counter'] ) {
			workview_definition['header_counter'] = 0;
		}
		header_count = workview_definition['header_counter'];
		
		checkDefinitionForErrors();
		
	}

	$("#dlgColumnWidth").dialog({
        autoOpen: false
    });
	$("#dlgFormulaTracer").dialog({
        autoOpen: false
    });
	$("#dlgDimensionEditor").dialog({
        autoOpen: false
    });
	$("#dlgFormulaEditor").dialog({
        autoOpen: false
    });
	$("#dlgHeaderEditor").dialog({
        autoOpen: false
    });
	$("#dlgConditionalFormatEditor").dialog({
        autoOpen: false
    });
	
	
	$("#dialog-dimension-element-select").dialog({
        autoOpen: false
    });
	
	
	
	$('#toggle').toggle(function(){
		$('#A').stop(true).animate({width:0});
		$('#B').stop(true).animate({left:0});
	},function(){
		$('#A').stop(true).animate({width:200});
		$('#B').stop(true).animate({left:200});
	})
	
	loadWorkview();
	
	
	$.contextMenu({
        selector: '.h,.spacer_rows,.spacer_columns', 
        callback: function(key, options) {
        
            if( key == "edit-set" ) {
				var dims = getDimensionsSelected();
				if( dims.length > 0 ) {
					displayDimensionElementPicker();
				} else {
					displayHeadingEditor();
				}
            } else if( key == "manage-dimensions" ) {
            	displayManageDimensions();
            } else if( key == "insert-prior" ) {
            	var sel = $(".ui-selected");
			  	if( sel.length == 1 ) {
			  		if( getDataset(sel[0],"position") == "column" ) {
            			addSet("columns",parseInt(getDataset(sel[0],"set")));
            		} else if( getDataset(sel[0],"position") == "row" ) {
            			addSet("columns",-1);
            		}
            	}
            } else if( key == "insert-after" ) {
            	var sel = $(".ui-selected");
			  	if( sel.length == 1 ) {
			  		if( getDataset(sel[0],"position") == "column" ) {
            			addSet("columns",parseInt(getDataset(sel[0],"set")+1));
            		} else {
						addSet("columns",0);
					}
            	}
            } else if( key == "insert-row-prior" ) {
            	var sel = $(".ui-selected");
			  	if( sel.length == 1 ) {
			  		if( getDataset(sel[0],"position") == "row" ) {
            			addSet("rows",parseInt(getDataset(sel[0],"set")));
            		} else if( getDataset(sel[0],"position") == "column" ) {
            			addSet("rows",-1);
            		}
            	}
            } else if( key == "insert-row-after" ) {
            	var sel = $(".ui-selected");
			  	if( sel.length == 1 ) {
			  		if( getDataset(sel[0],"position") == "row" ) {
            			addSet("rows",parseInt(getDataset(sel[0],"set")+1));
            		}
            	}
            } else if( key == "remove-row" ) {
            	var sel = $(".ui-selected");
			  	if( sel.length == 1 ) {
            		removeSet("rows", parseInt(getDataset(sel[0],"set")));
            	}
            } else if( key == "remove-column" ) {
            	var sel = $(".ui-selected");
			  	if( sel.length == 1 ) {
            		removeSet("columns", parseInt(getDataset(sel[0],"set")));
            	}
            } 
            
            if( key == "column-width" ) {
            	var sel = $(".ui-selected");
			  	if( sel.length == 1 ) {
            		displayColumnWidthDialog(sel[0]);
            		
            	}
            } else if( key == "workview-save-as" ) {
            	displaySaveAs();
            }
            
            //removeSet
            //
            
        },
        items: {
        	"edit-set": {name: "Edit Set", icon: "edit-set", "disabled": function(key, opt) { 
                    return this.data('setDisabled'); 
                }},
        	"manage-dimensions": {name: "Manage Dimensions", icon: "manage-dimensions"},
        	
        	"table-fold": {
                "name": "Change Table", 
                icon: "table",
                "items": {
                    "insert-prior": {name: "Insert column before selection", icon: "column"},
					"insert-after": {name: "Insert column after selection", icon: "column"},
					"insert-row-prior": {name: "Insert row before selection", icon: "row"},
					"insert-row-after": {name: "Insert row after selection", icon: "row"},
					"remove-column": {name: "Remove this column", icon: "remove-column"},
					"remove-row": {name: "Remove this row", icon: "remove-row"}
                }
            },
        	
        	"column-width": {name: "Change Column Width", icon: "column-width"},
        	
        	"workview-save-as": {
                "name": "Duplicate Workview", icon: "save-as"
            }
            
        },
        events: {
            show: function(opt) {
                var $this = this;
                $.contextMenu.setInputValues(opt, $this.data());
                unselect();
                dismissTooltips();
                $this.addClass("ui-selected");
                
            }, 
            hide: function(opt) {
                
            }
        }
    });
    
    $.contextMenu({
        selector: '.c', 
        callback: function(key, options) {
            //var m = "clicked: " + key;
            // window.console && console.log(m) || alert(m); 
            
            if( key == "edit-formula" ) {
            	var sel = $(".ui-selected");
			  	if( sel.length == 1 ) {
            		displayFormulaEditor(sel[0]);
            	}
            } else if( key == "formula-trace" ) {
            	var sel = $(".ui-selected");
			  	if( sel.length == 1 ) {
            		displayFormulaTrace(sel[0]);
            	}
            } 
            
            
            
        },
        items: {
        	"edit-formula": {name: "Edit Formula", icon: "formula", "disabled": function(key, opt) { 
                    return this.data('formulaDisabled'); 
                }},
            "formula-trace": {name: "Explain Value", icon: "formula-trace" },
            
        	
        	"table-fold": {
                "name": "Change Table", 
                icon: "table",
                "items": {
                    "insert-prior": {name: "Insert set before to this column", icon: "column"}
                }
            }
            
        },
        events: {
            show: function(opt) {
                var $this = this;
                $.contextMenu.setInputValues(opt, $this.data());
                unselect();
                dismissTooltips();
                $this.addClass("ui-selected");
                
            }, 
            hide: function(opt) {
                
            }
        }
    });
	
	$(document).ready(function(){
		resizeWorkview();
	});

	window.onresize = function(event) {
		resizeWorkview();
	}

		
	$("#btn_add_set_instruction").bind( "touchstart", function(e) {
		addInstructionFunction();
		return false;
	});
	
});

var windowWidth = 0;
function resizeWorkview() {
	vpw = $(window).width();
	windowWidth = vpw;
	vph = $(window).height();
	vph -= 6;
	$('#workview').css({'height': (vph-50) + 'px'});
	$('#left-sidebar').css({'height': (vph-50) + 'px'});
	$('#right-sidebar').css({'height': (vph-50) + 'px'});
	
}


function displaySaveAs() {
	document.getElementById('workviewNewName').value = workview_definition['name'] ;
	$( "#dlgSaveAs" ).dialog({
      resizable: false,
      height:140,
      width:350,
      autoOpen: true,
      modal: true,
      buttons: {
			Save: function() {
				workviewSaveAs();
				
			},
			Cancel: function() {
				$( this ).dialog( "close" );
			}
		}
     });
}

function workviewChangeData() {
	var titles_collection = [];
	var titles = workview_definition['positioning']['titles'];
	
	for(var i=0;i<titles.length;i++) {
		var title = titles[i];
		
		if( title.type == 'header' ) {
			
			titles_collection[titles_collection.length] = {
				'type' : 'header',
				'id' : '',
				'element' : ''
			};
			
		} else {
			var listItem = $('#title' + title.id + ' option:selected');
			var dimElmSelection = listItem.text();
			
			titles_collection[titles_collection.length] = {
				'type' : 'dimension',
				'id' : title.id,
				'element' : dimElmSelection,
				'hierarchy' : getDataset(listItem[0],"hierarchy") 
			};
			
		}
	}
	
	var tasks = {"tasks": [
		{"task": "cube.update", "id" : model_detail.id, "cubeid" : workview_definition['cube'], "definition" : updates_definition },
		{"task": "workview.execute", "id" : model_detail.id, "workviewid" : workview_definition['id'], "titles" : titles_collection, "options" : workview_execute_options }
		
	]};
	
	if( bDisplayingRightPanel ) {
		tasks['tasks'][tasks['tasks'].length] = {"task": "workview.execute.visualisation", "id" : model_detail.id, "workviewid" : workview_definition['id'], "titles" : titles_collection, "options" : workview_execute_options };
	}
	
	query("model.service",tasks,workviewChangeDataCallback);	
	 updates_definition = {"updates" : []};
}


function workviewSave() {
	var titles_collection = [];
	var titles = workview_definition['positioning']['titles'];
	
	for(var i=0;i<titles.length;i++) {
		var title = titles[i];
		
		if( title.type == 'header' ) {
			
			titles_collection[titles_collection.length] = {
				'type' : 'header',
				'id' : '',
				'element' : ''
			};
		} else {
			var listItem = $('#title' + title.id + ' option:selected');
			var dimElmSelection = listItem.text();
			
			if( $('#title' + title.id + '_secondary').length > 0 ) {
				var elementTimeRange = $('#title' + title.id + '_secondary option:selected').text();
				dimElmSelection=gatherRangeElement($('#title' + title.id + '_secondary option'), dimElmSelection, elementTimeRange);
			}
			
			titles_collection[titles_collection.length] = {
				'type' : 'dimension',
				'id' : title.id,
				'element' : dimElmSelection,
				'hierarchy' : getDataset(listItem[0],"hierarchy") 
			};
		}
	}
	
	var tasks = {"tasks": [
		{"task": "workview.update", "id" : model_detail.id, "workviewid" : workview_definition['id'], "definition" : workview_definition },
		{"task": "workview.execute", "id" : model_detail.id, "workviewid" : workview_definition['id'], "titles" : titles_collection, "options" : workview_execute_options }
		
	]};
	
	
	if( bDisplayingRightPanel ) {
		tasks['tasks'][tasks['tasks'].length] = {"task": "workview.execute.visualisation", "id" : model_detail.id, "workviewid" : workview_definition['id'], "titles" : titles_collection, "options" : workview_execute_options };
	}
	
	query("model.service",tasks,workviewSaveCallback);
}

function workviewSaveCallback(data) {
	if( data == "{}" ) {
		workviewSave();
		return;
	}
	
	var results = JSON.parse(data);
	
	if( results['results'][1]['result'] == 1 ) {
		//the workview executed successfully
		titlesList = results['results'][0]['titles'];
		updateGridWithDefinition();
		updateGridRowsAndColumns(results['results'][1]);
		updateWorkviewData(results['results'][1]);

		if( results['results'].length == 3 ) {
			presentChart(results['results'][2]);
		}
		
		enableButtonsFor('');
	} else {
		//the workview failed to execute
		//the workview save failed, typically this is only happening if the session has expired.
		if( results['results'][0]['error'] ) {
			error = results['results'][0]['error'];
			alert(error);
		}
	}
	updateManageDimensions();
	
}


function workviewSaveAs() {
	var newName = document.getElementById('workviewNewName').value;
	var tasks = {"tasks": [
		{"task": "workview.duplicate", "id" : model_detail.id, "workviewid" : workview_definition['id'], "name" : newName }
	]};
	query("model.service",tasks,workviewSaveAsCallback);
}
function workviewSaveAsCallback(data) {
	var results = JSON.parse(data);
	
	if( results['results'][0]['result'] == 1 ) {
		titlesList = results['results'][0]['titles'];
		$(  "#dlgSaveAs" ).dialog( "close" );
		window.location = "/workview/editor/?id=" + model_detail.id + "&workview=" + results['results'][0]['id'];
	} else {
		//the workview save as failed, typically this is only happening if the name is taken
		if( results['results'][0]['error'] ) {
			var error = results['results'][0]['error'];
			alert(error);
		}
		if( results['results'][0]['message'] ) {
			var error = results['results'][0]['message'];
			alert(error);
		}
	}
}

