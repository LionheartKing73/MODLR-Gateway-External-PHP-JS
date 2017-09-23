var server_id = 0;


$(document).ready(function(){
	

	$( "tr.workview-entry" ).bind( "mousedown", function ( e ) {
		if( $(this).hasClass("ui-selected") ) {
			$(this).removeClass("ui-selected");
			$(this).children().removeClass("ui-selected");
			
			selectionOrder.splice(selectionOrder.indexOf(this),1);
			
		} else {
			$(this).addClass("ui-selected");
			$(this).children().addClass("ui-selected");
			selectionOrder[selectionOrder.length] = this;
		}
		return false;
	});
	
	
	$.contextMenu({
		selector: "tr.workview-entry", 
		callback: function(key, options) {
			
			if( key == "open-landscape" ) {
				if( selectionOrder.length == 2 ) {
					var tr1 = selectionOrder[0];
					var tr2 = selectionOrder[1];
					var url = '/workview/multi/?position=landscape&id=' + getDataset(tr1,"model") + '&w1=' + getDataset(tr1,"workview") + '&w2=' + getDataset(tr2,"workview");
					var win = window.open(url, '_blank');
					win.focus();
					
				}
			} else if( key == "open-portrait" ) {
				if( selectionOrder.length == 2 ) {
					var tr1 = selectionOrder[0];
					var tr2 = selectionOrder[1];
					var url = '/workview/multi/?position=portrait&id=' + getDataset(tr1,"model") + '&w1=' + getDataset(tr1,"workview") + '&w2=' + getDataset(tr2,"workview");
					var win = window.open(url, '_blank');
					win.focus();
					
				}
			} else if( key == "open-four" ) {
				if( selectionOrder.length == 4 ) {
					var tr1 = selectionOrder[0];
					var tr2 = selectionOrder[1];
					var tr3 = selectionOrder[2];
					var tr4 = selectionOrder[3];
					var url = '/workview/multi/?id=' + getDataset(tr1,"model") + '&w1=' + getDataset(tr1,"workview") + '&w2=' + getDataset(tr2,"workview") + '&w3=' + getDataset(tr3,"workview") + '&w4=' + getDataset(tr4,"workview");
					var win = window.open(url, '_blank');
					win.focus();
					
				}
			} else if( key == "open-new-window" ) {
				var sel = $("tr.ui-selected");
				for(var i=0;i<sel.length;i++) {
					var tr = sel[i];
					var url = '/workview/editor/?id=' + getDataset(tr,"model") + '&workview=' + getDataset(tr,"workview");
					
					var win = window.open(url, '_blank');
					win.focus();
				}
			}
		
		},
		items: {
			"open-four": {name: "Split Screen", icon: "open-four", "disabled": function(key, opt) { 
					return $("tr.ui-selected").length != 4; 
				}},
			"open-landscape": {name: "Landscape Panels", icon: "open-landscape", "disabled": function(key, opt) { 
					return $("tr.ui-selected").length != 2; 
				}} ,
			"open-portrait": {name: "Portrait Panels", icon: "open-portrait", "disabled": function(key, opt) { 
					return $("tr.ui-selected").length != 2; 
				}} ,
			"open-new-window": {name: "Open in new windows", icon: "open-new-window" } 
			
			
		
		
		},
		events: {
			show: function(opt) {
				
				if( $(this).hasClass("ui-selected") ) {
				
				} else {
					$(this).addClass("ui-selected");
					$(this).children().addClass("ui-selected");
					selectionOrder[selectionOrder.length] = this[0];
				}
			
			}, 
			hide: function(opt) {
			
			}
		}
	});
	
	breakFromFrame();
	

})

function breakFromFrame() {
    try {
        if(  window.self !== window.top ) {
        	window.parent.location = window.location;
        }
    } catch (e) {
        return true;
    }
}


var selectionOrder = [];

function deleteItem(type,id,name) {
	var removeButton = "Delete " + type + ": " + name;
	
	document.getElementById('confirmParagraph').innerHTML = "Are you sure you would like to permanently delete the '" + name + "' " + type.toLowerCase() + "?";
	
	$( "#dlgDeleteItem" ).dialog({
		resizable: false,
		height:140,
		width:400,
		modal: true,
		title: removeButton,
		buttons: {
			"Delete Object" : function() {
				deleteItemConfirmed(type,id,name);
				$( this ).dialog( "close" );
			},
			Cancel: function() {
				$( this ).dialog( "close" );
			}
		}
	});
}

var stationaryClick = false;
function deleteItemConfirmed(type,id,name) {
	var tasks = null;
	if( type == "Cube" ) {
		tasks = {"tasks": [
			{"task": "cube.delete", "id":model_detail.id,  "cubeid": id }
		]};
	} else if( type == "Dimension" ) {
		tasks = {"tasks": [
			{"task": "dimension.delete", "id":model_detail.id,  "dimensionid": id }
		]};
	} else if( type == "Workview" ) {
		tasks = {"tasks": [
			{"task": "workview.delete", "id":model_detail.id,  "workviewid": id }
		]};
	} else if( type == "Process" ) {
		tasks = {"tasks": [
			{"task": "process.delete", "id":model_detail.id,  "processid": id }
		]};
	} else if( type == "Variable" ) {
		tasks = {"tasks": [
			{"task": "model.variable.delete", "id":model_detail.id,  "key": id }
		]};
	} else if( type == "Schedule" ) {
		tasks = {"tasks": [
			{"task": "schedule.delete", "id":model_detail.id,  "scheduleid": id }
		]};
	}
	query("model.service",tasks,deleteItemConfirmedCallback);
}

function toggleCheckedTask(workflowid, taskid, parentObject) {
	
	var checkbox = parentObject.childNodes[0];
	var status = $(checkbox).hasClass("blank");
	
	if( status ) {
		status = "1";
		$(checkbox).removeClass("blank");
	} else {
		status = "0";
		$(checkbox).addClass("blank");
	}
	
	tasks = {"tasks": [
		{"task": "workflow.item.status.update", "id":model_detail.id,  "workflowid": workflowid,  "itemid": taskid, "status":status }
	]};
	query("model.service",tasks,toggleCheckedTaskCallback);
}
function toggleCheckedTaskCallback(data) {

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
		
		location.reload(true);
	}
}



