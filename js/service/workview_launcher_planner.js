
var is_editor = false;

function workviewBindings() {
    
    
}

function workviewExportPerform(tasks) {
    tasks.tasks[0].activityid = activity_id;
	query("collaborator.service",tasks,workviewExportCallback);
}


$(function() {
	
	//initialise the workview:
	if( workview_definition == null ) {
		//failed to load a specific workview
		window.location = '/model/?id=' + model_detail.id;
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
	$("#dialog-dimension-element-select").dialog({
        autoOpen: false
    });
    
	loadWorkview();
	
	
	var cssApplied = ["custom-css-class-01"];
	if( localStorage ) {
		var toolbarHidden = localStorage.getItem("Workview.Toolbar.Hidden");
		if( toolbarHidden ) {
			if( toolbarHidden == "1" ) {
				cssApplied = ["custom-css-class-01","acidjs-ribbon-collapsed"];
			}
		}
	}
	
	
    
    $.contextMenu({
        selector: '.c', 
        callback: function(key, options) {
            
            if( key == "formula-trace" ) {
            	var sel = $(".ui-selected");
			  	if( sel.length == 1 ) {
            		displayFormulaTrace(sel[0]);
            	}
            }
        },
        items: {
            "formula-trace": {name: "Explain Value", icon: "formula-trace" }
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
	
	window.parent.enableExport();
});

function btnExport() {
	workviewExport("XLSX");
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
		{"task": "cube.update", "id" : model_detail.id, "activityid" : activity_id, "cubeid" : workview_definition['cube'], "definition" : updates_definition },
		{"task": "workview.execute", "id" : model_detail.id, "activityid" : activity_id, "workviewid" : workview_definition['id'], "titles" : titles_collection, "options" : workview_execute_options }
		
	]};
	query("collaborator.service",tasks,workviewChangeDataCallback);	
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
			
			titles_collection[titles_collection.length] = {
				'type' : 'dimension',
				'id' : title.id,
				'element' : dimElmSelection,
				'hierarchy' : getDataset(listItem[0],"hierarchy") 
			};
		}
	}
	
	var tasks = {"tasks": [
		{"task": "workview.update", "id" : model_detail.id, "workviewid" : workview_definition['id'], "activityid" : activity_id, "definition" : workview_definition },
		{"task": "workview.execute", "id" : model_detail.id, "workviewid" : workview_definition['id'], "activityid" : activity_id, "titles" : titles_collection, "options" : workview_execute_options }
		
	]};
	query("collaborator.service",tasks,workviewSaveCallback);
	
	if( parent ) {
		if (typeof( parent.refreshOthers ) === "function") {
			parent.refreshOthers(getParameterByName("page") + getParameterByName("workview"));
		}
	}	
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
	
		enableButtonsFor('');
	} else {
		//the workview failed to execute
		//the workview save failed, typically this is only happening if the session has expired.
		if( results['results'][0]['error'] ) {
			error = results['results'][0]['error'];
			alert(error);
		}
	}
}


